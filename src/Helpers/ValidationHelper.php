<?php

namespace SqlCommands\Helpers;

/**
 * Validation helper for SQL operations
 */
class ValidationHelper
{
    /**
     * Allowed table name characters
     */
    private static $tableNamePattern = '/^[a-zA-Z][a-zA-Z0-9_]*$/';
    
    /**
     * Allowed column name characters
     */
    private static $columnNamePattern = '/^[a-zA-Z][a-zA-Z0-9_]*$/';
    
    /**
     * Dangerous SQL keywords to prevent
     */
    private static $dangerousKeywords = [
        'DROP DATABASE',
        'DROP SCHEMA', 
        'TRUNCATE DATABASE',
        'DELETE FROM information_schema',
        'DELETE FROM sqlite_master',
        'GRANT',
        'REVOKE',
        'CREATE USER',
        'ALTER USER',
        'DROP USER'
    ];

    /**
     * Validate table name
     */
    public static function isValidTableName(string $tableName): bool
    {
        return preg_match(self::$tableNamePattern, $tableName) === 1;
    }

    /**
     * Validate column name
     */
    public static function isValidColumnName(string $columnName): bool
    {
        // Handle table.column notation
        if (strpos($columnName, '.') !== false) {
            $parts = explode('.', $columnName);
            return count($parts) === 2 && 
                   self::isValidTableName($parts[0]) && 
                   (self::isValidColumnName($parts[1]) || $parts[1] === '*');
        }
        
        // Allow wildcard
        if ($columnName === '*') {
            return true;
        }
        
        return preg_match(self::$columnNamePattern, $columnName) === 1;
    }

    /**
     * Check if SQL contains dangerous operations
     */
    public static function containsDangerousOperation(string $sql): bool
    {
        $sql = strtoupper($sql);
        
        foreach (self::$dangerousKeywords as $keyword) {
            if (strpos($sql, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate data array for INSERT/UPDATE
     */
    public static function validateDataArray(array $data): array
    {
        $errors = [];
        
        if (empty($data)) {
            $errors[] = 'Data array cannot be empty';
        }
        
        foreach ($data as $column => $value) {
            if (!is_string($column) || !self::isValidColumnName($column)) {
                $errors[] = "Invalid column name: {$column}";
            }
            
            // Check for excessively long values
            if (is_string($value) && strlen($value) > 65535) {
                $errors[] = "Value too long for column: {$column}";
            }
        }
        
        return $errors;
    }

    /**
     * Validate WHERE conditions array
     */
    public static function validateWhereConditions(array $conditions): array
    {
        $errors = [];
        
        foreach ($conditions as $column => $value) {
            if (!is_string($column) || !self::isValidColumnName($column)) {
                $errors[] = "Invalid column name in WHERE clause: {$column}";
            }
            
            if (is_array($value) && empty($value)) {
                $errors[] = "Empty array value for column: {$column}";
            }
        }
        
        return $errors;
    }

    /**
     * Sanitize and validate table name with strict checking
     */
    public static function sanitizeTableName(string $tableName): string
    {
        if (!self::isValidTableName($tableName)) {
            throw new \InvalidArgumentException("Invalid table name: {$tableName}");
        }
        
        return $tableName;
    }

    /**
     * Sanitize and validate column name with strict checking
     */
    public static function sanitizeColumnName(string $columnName): string
    {
        if (!self::isValidColumnName($columnName)) {
            throw new \InvalidArgumentException("Invalid column name: {$columnName}");
        }
        
        return $columnName;
    }
}

/**
 * Query building helper for common patterns
 */
class QueryHelper
{
    /**
     * Build a pagination clause
     */
    public static function buildPagination(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 1000)); // Limit max per page
        $offset = ($page - 1) * $perPage;
        
        return [
            'limit' => $perPage,
            'offset' => $offset
        ];
    }

    /**
     * Build search conditions for multiple columns
     */
    public static function buildSearchConditions(array $columns, string $searchTerm): array
    {
        $conditions = [];
        $params = [];
        
        foreach ($columns as $column) {
            ValidationHelper::sanitizeColumnName($column);
            $conditions[] = "`{$column}` LIKE ?";
            $params[] = "%{$searchTerm}%";
        }
        
        return [
            'sql' => '(' . implode(' OR ', $conditions) . ')',
            'params' => $params
        ];
    }

    /**
     * Build date range conditions
     */
    public static function buildDateRange(string $column, ?string $startDate, ?string $endDate): array
    {
        ValidationHelper::sanitizeColumnName($column);
        
        $conditions = [];
        $params = [];
        
        if ($startDate) {
            $conditions[] = "`{$column}` >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $conditions[] = "`{$column}` <= ?";
            $params[] = $endDate;
        }
        
        if (empty($conditions)) {
            return ['sql' => '1=1', 'params' => []];
        }
        
        return [
            'sql' => implode(' AND ', $conditions),
            'params' => $params
        ];
    }

    /**
     * Build IN clause with proper parameter binding
     */
    public static function buildInClause(string $column, array $values): array
    {
        ValidationHelper::sanitizeColumnName($column);
        
        if (empty($values)) {
            return ['sql' => '1=0', 'params' => []]; // Always false condition
        }
        
        $placeholders = array_fill(0, count($values), '?');
        $sql = "`{$column}` IN (" . implode(', ', $placeholders) . ")";
        
        return [
            'sql' => $sql,
            'params' => array_values($values)
        ];
    }

    /**
     * Escape LIKE pattern characters
     */
    public static function escapeLikePattern(string $pattern): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $pattern);
    }
}

/**
 * Schema helper for database introspection
 */
class SchemaHelper
{
    /**
     * Get common SQLite column types
     */
    public static function getSQLiteColumnTypes(): array
    {
        return [
            'INTEGER' => 'Integer numbers',
            'TEXT' => 'Text strings',
            'REAL' => 'Floating point numbers',
            'BLOB' => 'Binary data',
            'NUMERIC' => 'Numbers (integer or float)',
            'BOOLEAN' => 'True/false values',
            'DATE' => 'Date values (stored as TEXT)',
            'DATETIME' => 'Date and time values (stored as TEXT)',
            'TIMESTAMP' => 'Timestamp values (stored as TEXT)'
        ];
    }

    /**
     * Get common constraints and modifiers
     */
    public static function getCommonConstraints(): array
    {
        return [
            'PRIMARY KEY' => 'Primary key constraint',
            'UNIQUE' => 'Unique value constraint',
            'NOT NULL' => 'Cannot be null',
            'DEFAULT' => 'Default value',
            'CHECK' => 'Check constraint',
            'FOREIGN KEY' => 'Foreign key constraint',
            'AUTOINCREMENT' => 'Auto-incrementing value (SQLite)'
        ];
    }

    /**
     * Generate sample table definitions for learning
     */
    public static function getSampleTableDefinitions(): array
    {
        return [
            'users' => [
                'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                'username' => 'TEXT UNIQUE NOT NULL',
                'email' => 'TEXT UNIQUE NOT NULL',
                'first_name' => 'TEXT NOT NULL',
                'last_name' => 'TEXT NOT NULL',
                'age' => 'INTEGER CHECK (age >= 0 AND age <= 150)',
                'status' => 'TEXT DEFAULT "active" CHECK (status IN ("active", "inactive", "suspended"))',
                'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
            ],
            'posts' => [
                'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                'user_id' => 'INTEGER NOT NULL',
                'title' => 'TEXT NOT NULL',
                'content' => 'TEXT',
                'status' => 'TEXT DEFAULT "draft" CHECK (status IN ("draft", "published", "archived"))',
                'view_count' => 'INTEGER DEFAULT 0',
                'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                'FOREIGN KEY (user_id)' => 'REFERENCES users(id) ON DELETE CASCADE'
            ],
            'comments' => [
                'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                'post_id' => 'INTEGER NOT NULL',
                'user_id' => 'INTEGER NOT NULL',
                'content' => 'TEXT NOT NULL',
                'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                'FOREIGN KEY (post_id)' => 'REFERENCES posts(id) ON DELETE CASCADE',
                'FOREIGN KEY (user_id)' => 'REFERENCES users(id) ON DELETE CASCADE'
            ]
        ];
    }
}