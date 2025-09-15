<?php

namespace SqlCommands\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use SqlCommands\SqlCommands;
use SqlCommands\SqlSimulator;
use Exception;

/**
 * SQL Practice Controller
 * 
 * Provides web interface for SQL learning and practice
 */
class SqlPracticeController extends Controller
{
    private $simulator;
    
    public function __construct()
    {
        // Initialize with practice database from config
        $practiceDbPath = config('sql-commands.practice_database_path', database_path('practice.sqlite'));
        $this->simulator = new SqlSimulator($practiceDbPath);
        
        // Auto-create sample data if enabled
        if (config('sql-commands.auto_create_samples', true)) {
            $this->initializePracticeDatabase();
        }
    }
    
    /**
     * Show the SQL practice interface
     */
    public function index()
    {
        $schema = $this->simulator->getSchema();
        return view('sql-commands::practice', compact('schema'));
    }
    
    /**
     * Execute SQL command from textarea
     */
    public function execute(Request $request)
    {
        $request->validate([
            'sql_command' => 'required|string|max:10000',
            'command_type' => 'in:raw,builder'
        ]);
        
        try {
            $sqlCommand = trim($request->sql_command);
            
            // Check for dangerous operations
            $this->validateSqlCommand($sqlCommand);
            
            // Set execution timeout
            set_time_limit(config('sql-commands.max_execution_time', 30));
            
            $result = $this->simulator->executeRawSQL($sqlCommand);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'sql' => $sqlCommand,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'sql' => $request->sql_command,
                'timestamp' => now()->toISOString()
            ], 400);
        }
    }
    
    /**
     * Get database schema for reference
     */
    public function schema()
    {
        try {
            $schema = $this->simulator->getSchema();
            return response()->json($schema);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Reset database to initial state
     */
    public function reset()
    {
        try {
            $this->simulator->resetDatabase();
            $this->initializePracticeDatabase();
            
            return response()->json([
                'success' => true,
                'message' => 'Database reset successfully with sample data'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get sample queries for learning
     */
    public function samples()
    {
        $samples = [
            'Basic SELECT' => [
                'sql' => 'SELECT * FROM users LIMIT 5',
                'description' => 'Retrieve first 5 users from the users table',
                'category' => 'basic'
            ],
            'SELECT with WHERE' => [
                'sql' => 'SELECT name, email FROM users WHERE age > 25',
                'description' => 'Get users older than 25 years',
                'category' => 'basic'
            ],
            'COUNT records' => [
                'sql' => 'SELECT COUNT(*) as total_users FROM users',
                'description' => 'Count total number of users',
                'category' => 'aggregate'
            ],
            'INSERT new record' => [
                'sql' => "INSERT INTO users (name, email, age) VALUES ('New User', 'new@example.com', 30)",
                'description' => 'Add a new user to the database',
                'category' => 'modification'
            ],
            'UPDATE existing record' => [
                'sql' => "UPDATE users SET age = 35 WHERE name = 'John Doe'",
                'description' => 'Update John Doe\'s age to 35',
                'category' => 'modification'
            ],
            'DELETE records' => [
                'sql' => "DELETE FROM orders WHERE status = 'cancelled'",
                'description' => 'Remove all cancelled orders',
                'category' => 'modification'
            ],
            'INNER JOIN' => [
                'sql' => 'SELECT u.name, o.total FROM users u INNER JOIN orders o ON u.id = o.user_id',
                'description' => 'Join users with their orders',
                'category' => 'joins'
            ],
            'LEFT JOIN with COUNT' => [
                'sql' => 'SELECT u.name, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id, u.name',
                'description' => 'Count orders per user (including users with no orders)',
                'category' => 'joins'
            ],
            'GROUP BY and HAVING' => [
                'sql' => 'SELECT status, COUNT(*) as count FROM orders GROUP BY status HAVING count > 1',
                'description' => 'Group orders by status and show only statuses with more than 1 order',
                'category' => 'aggregate'
            ],
            'Subquery' => [
                'sql' => 'SELECT name FROM users WHERE id IN (SELECT user_id FROM orders WHERE total > 100)',
                'description' => 'Find users who have orders worth more than $100',
                'category' => 'advanced'
            ],
            'Date functions' => [
                'sql' => "SELECT name, created_at FROM users WHERE created_at >= date('now', '-30 days')",
                'description' => 'Get users created in the last 30 days',
                'category' => 'advanced'
            ],
            'String functions' => [
                'sql' => "SELECT UPPER(name) as name_upper, LENGTH(email) as email_length FROM users",
                'description' => 'Demonstrate string manipulation functions',
                'category' => 'functions'
            ]
        ];
        
        return response()->json($samples);
    }
    
    /**
     * Initialize practice database (first-time setup)
     */
    public function initialize()
    {
        try {
            $results = [];
            $results['tables'] = $this->simulator->createSampleTables();
            $results['data'] = $this->simulator->insertSampleData();
            
            return response()->json([
                'success' => true,
                'message' => 'Practice database initialized successfully',
                'results' => $results
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export query results as CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'sql_command' => 'required|string|max:10000'
        ]);
        
        try {
            $sqlCommand = trim($request->sql_command);
            
            // Only allow SELECT queries for export
            if (!preg_match('/^\s*SELECT\s/i', $sqlCommand)) {
                throw new Exception('Only SELECT queries can be exported');
            }
            
            $result = $this->simulator->executeRawSQL($sqlCommand);
            
            if (!$result['success'] || !isset($result['data'])) {
                throw new Exception('No data to export');
            }
            
            $data = $result['data'];
            if (empty($data)) {
                throw new Exception('Query returned no results');
            }
            
            // Generate CSV content
            $csv = $this->generateCsv($data);
            
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="query-results-' . date('Y-m-d-H-i-s') . '.csv"');
                
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Private helper methods
     */
    private function initializePracticeDatabase()
    {
        $schema = $this->simulator->getSchema();
        
        // If no tables exist, create sample data
        if (empty($schema)) {
            $this->simulator->createSampleTables();
            $this->simulator->insertSampleData();
        }
    }
    
    private function validateSqlCommand(string $sql)
    {
        $sql = strtoupper(trim($sql));
        $allowedOperations = config('sql-commands.allowed_operations', [
            'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER'
        ]);
        
        $operation = explode(' ', $sql)[0];
        
        if (!in_array($operation, $allowedOperations)) {
            throw new Exception("Operation '{$operation}' is not allowed in practice mode");
        }
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            'DROP DATABASE',
            'DROP SCHEMA', 
            'TRUNCATE DATABASE',
            'DELETE FROM sqlite_master',
            'PRAGMA'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (strpos($sql, $pattern) !== false) {
                throw new Exception("Dangerous operation '{$pattern}' is not allowed");
            }
        }
    }
    
    private function generateCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }
        
        $output = fopen('php://temp', 'r+');
        
        // Add header row
        fputcsv($output, array_keys($data[0]));
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}