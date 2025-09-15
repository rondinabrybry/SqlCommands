<?php

namespace SqlCommands;

use PDO;
use PDOException;
use InvalidArgumentException;

/**
 * Secure SQL Simulator for practice and learning
 * 
 * This class provides a safe environment to execute SQL queries
 * with proper error handling and security measures.
 */
class SqlSimulator
{
    private PDO $pdo;
    private string $databaseType;
    private array $allowedOperations = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER'];

    /**
     * Initialize the simulator with database connection
     */
    public function __construct(string $databasePath, array $options = [])
    {
        $this->initializeDatabase($databasePath, $options);
        $this->databaseType = 'sqlite';
        SqlCommands::setDatabaseType($this->databaseType);
    }

    /**
     * Execute a prepared SQL command safely
     */
    public function executeQuery(array $queryData): array
    {
        if (!isset($queryData['sql']) || !isset($queryData['params'])) {
            throw new InvalidArgumentException('Query data must contain sql and params keys');
        }

        $sql = $queryData['sql'];
        $params = $queryData['params'];

        // Validate the operation
        $this->validateOperation($sql);

        try {
            $statement = $this->pdo->prepare($sql);
            $success = $statement->execute($params);

            if (!$success) {
                return [
                    'success' => false,
                    'error' => $statement->errorInfo(),
                    'sql' => $sql
                ];
            }

            // Return appropriate result based on query type
            if ($this->isSelectQuery($sql)) {
                return [
                    'success' => true,
                    'data' => $statement->fetchAll(PDO::FETCH_ASSOC),
                    'rowCount' => $statement->rowCount(),
                    'sql' => $sql
                ];
            } else {
                return [
                    'success' => true,
                    'affectedRows' => $statement->rowCount(),
                    'sql' => $sql
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sql' => $sql
            ];
        }
    }

    /**
     * Execute raw SQL (for advanced users, with restrictions)
     */
    public function executeRawSQL(string $sql, array $params = []): array
    {
        return $this->executeQuery(['sql' => $sql, 'params' => $params]);
    }

    /**
     * Get database schema information
     */
    public function getSchema(): array
    {
        try {
            $tables = [];
            $tablesQuery = SqlCommands::showTables();
            $tablesResult = $this->executeQuery($tablesQuery);
            
            if ($tablesResult['success']) {
                foreach ($tablesResult['data'] as $row) {
                    $tableName = reset($row); // Get first column value
                    $tableInfo = SqlCommands::describeTable($tableName);
                    $tableInfoResult = $this->executeQuery($tableInfo);
                    
                    $tables[$tableName] = $tableInfoResult['success'] ? $tableInfoResult['data'] : [];
                }
            }
            
            return $tables;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create sample tables for practice
     */
    public function createSampleTables(): array
    {
        $results = [];
        
        // Users table
        $usersTable = SqlCommands::createTable('users', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'name' => 'TEXT NOT NULL',
            'email' => 'TEXT UNIQUE NOT NULL',
            'age' => 'INTEGER',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
        $results['users'] = $this->executeQuery($usersTable);

        // Orders table
        $ordersTable = SqlCommands::createTable('orders', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'user_id' => 'INTEGER NOT NULL',
            'total' => 'DECIMAL(10,2)',
            'status' => 'TEXT DEFAULT "pending"',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id)' => 'REFERENCES users(id)'
        ]);
        $results['orders'] = $this->executeQuery($ordersTable);

        // Products table
        $productsTable = SqlCommands::createTable('products', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'name' => 'TEXT NOT NULL',
            'price' => 'DECIMAL(10,2)',
            'category' => 'TEXT',
            'stock' => 'INTEGER DEFAULT 0'
        ]);
        $results['products'] = $this->executeQuery($productsTable);

        return $results;
    }

    /**
     * Insert sample data for practice
     */
    public function insertSampleData(): array
    {
        $results = [];

        // Sample users
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 28],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 34],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 45],
            ['name' => 'Alice Brown', 'email' => 'alice@example.com', 'age' => 29]
        ];

        foreach ($users as $user) {
            $userInsert = SqlCommands::insert('users', $user);
            $results['users'][] = $this->executeQuery($userInsert);
        }

        // Sample products
        $products = [
            ['name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics', 'stock' => 10],
            ['name' => 'Mouse', 'price' => 29.99, 'category' => 'Electronics', 'stock' => 50],
            ['name' => 'Keyboard', 'price' => 79.99, 'category' => 'Electronics', 'stock' => 30],
            ['name' => 'Book', 'price' => 19.99, 'category' => 'Books', 'stock' => 100]
        ];

        foreach ($products as $product) {
            $productInsert = SqlCommands::insert('products', $product);
            $results['products'][] = $this->executeQuery($productInsert);
        }

        // Sample orders
        $orders = [
            ['user_id' => 1, 'total' => 999.99, 'status' => 'completed'],
            ['user_id' => 2, 'total' => 109.98, 'status' => 'pending'],
            ['user_id' => 1, 'total' => 29.99, 'status' => 'shipped'],
            ['user_id' => 3, 'total' => 19.99, 'status' => 'completed']
        ];

        foreach ($orders as $order) {
            $orderInsert = SqlCommands::insert('orders', $order);
            $results['orders'][] = $this->executeQuery($orderInsert);
        }

        return $results;
    }

    /**
     * Reset database (drop all tables)
     */
    public function resetDatabase(): array
    {
        $results = [];
        $schema = $this->getSchema();
        
        foreach (array_keys($schema) as $table) {
            $dropQuery = SqlCommands::dropTable($table);
            $results[$table] = $this->executeQuery($dropQuery);
        }
        
        return $results;
    }

    /**
     * Get connection info
     */
    public function getConnectionInfo(): array
    {
        return [
            'database_type' => $this->databaseType,
            'pdo_version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'connection_status' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
        ];
    }

    /**
     * Private helper methods
     */
    private function initializeDatabase(string $databasePath, array $options): void
    {
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        try {
            $this->pdo = new PDO('sqlite:' . $databasePath, null, null, $options);
            // Enable foreign key constraints for SQLite
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            throw new InvalidArgumentException("Cannot connect to database: " . $e->getMessage());
        }
    }

    private function validateOperation(string $sql): void
    {
        $sql = trim(strtoupper($sql));
        $operation = explode(' ', $sql)[0];
        
        if (!in_array($operation, $this->allowedOperations)) {
            throw new InvalidArgumentException("Operation '{$operation}' is not allowed");
        }

        // Prevent dangerous operations
        $dangerous = ['DROP DATABASE', 'TRUNCATE', 'GRANT', 'REVOKE'];
        foreach ($dangerous as $danger) {
            if (strpos($sql, $danger) !== false) {
                throw new InvalidArgumentException("Dangerous operation '{$danger}' is not allowed");
            }
        }
    }

    private function isSelectQuery(string $sql): bool
    {
        return stripos(trim($sql), 'SELECT') === 0 || 
               stripos(trim($sql), 'PRAGMA') === 0 ||
               stripos(trim($sql), 'SHOW') === 0;
    }
}