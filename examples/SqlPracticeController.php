<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SqlCommands\SqlCommands;
use SqlCommands\SqlSimulator;
use Exception;

class SqlPracticeController extends Controller
{
    private $simulator;
    
    public function __construct()
    {
        // Initialize with practice database
        $practiceDbPath = database_path('practice.sqlite');
        $this->simulator = new SqlSimulator($practiceDbPath);
        
        // Create sample data if database is empty
        $this->initializePracticeDatabase();
    }
    
    /**
     * Show the SQL practice interface
     */
    public function index()
    {
        $schema = $this->simulator->getSchema();
        return view('sql-practice.index', compact('schema'));
    }
    
    /**
     * Execute SQL command from textarea
     */
    public function execute(Request $request)
    {
        $request->validate([
            'sql_command' => 'required|string|max:10000',
            'command_type' => 'required|in:raw,builder'
        ]);
        
        try {
            $sqlCommand = trim($request->sql_command);
            $result = [];
            
            if ($request->command_type === 'raw') {
                // Execute raw SQL
                $result = $this->simulator->executeRawSQL($sqlCommand);
            } else {
                // Try to parse and use query builder (for advanced users)
                $result = $this->parseAndExecuteBuilderCommand($sqlCommand);
            }
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'sql' => $sqlCommand
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'sql' => $request->sql_command
            ], 400);
        }
    }
    
    /**
     * Get database schema for reference
     */
    public function schema()
    {
        $schema = $this->simulator->getSchema();
        return response()->json($schema);
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
                'message' => 'Database reset successfully'
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
                'description' => 'Retrieve first 5 users'
            ],
            'SELECT with WHERE' => [
                'sql' => 'SELECT name, email FROM users WHERE age > 25',
                'description' => 'Get users older than 25'
            ],
            'INSERT new record' => [
                'sql' => "INSERT INTO users (name, email, age) VALUES ('New User', 'new@example.com', 30)",
                'description' => 'Add a new user'
            ],
            'UPDATE existing record' => [
                'sql' => "UPDATE users SET age = 35 WHERE name = 'John Doe'",
                'description' => 'Update John Doe\'s age'
            ],
            'JOIN tables' => [
                'sql' => 'SELECT u.name, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id, u.name',
                'description' => 'Count orders per user'
            ],
            'DELETE records' => [
                'sql' => "DELETE FROM orders WHERE status = 'cancelled'",
                'description' => 'Remove cancelled orders'
            ],
            'Aggregate functions' => [
                'sql' => 'SELECT AVG(age) as average_age, MIN(age) as youngest, MAX(age) as oldest FROM users',
                'description' => 'User age statistics'
            ],
            'Complex JOIN' => [
                'sql' => 'SELECT u.name, p.name as product_name, o.total FROM users u JOIN orders o ON u.id = o.user_id JOIN products p ON p.id = o.id',
                'description' => 'Users with their orders and products'
            ]
        ];
        
        return response()->json($samples);
    }
    
    /**
     * Initialize practice database with sample data
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
    
    /**
     * Parse builder-style commands (advanced feature)
     */
    private function parseAndExecuteBuilderCommand($command)
    {
        // This is a simple parser - you can extend this
        if (strpos(strtoupper($command), 'SELECT') === 0) {
            return $this->simulator->executeRawSQL($command);
        }
        
        // Add more parsing logic as needed
        return $this->simulator->executeRawSQL($command);
    }
}