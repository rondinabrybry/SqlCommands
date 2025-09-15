<?php

namespace SqlCommands;

/**
 * Secure SQL Query Builder for Laravel Practice
 * 
 * This class provides methods to generate SQL queries safely for learning purposes.
 * All methods return parameterized queries to prevent SQL injection.
 */
class SqlCommands
{
    /**
     * Database type for compatibility checks
     */
    private static $databaseType = 'sqlite';

    /**
     * Set the database type for compatibility
     */
    public static function setDatabaseType(string $type): void
    {
        self::$databaseType = strtolower($type);
    }

    /**
     * Build a SELECT query with optional WHERE, ORDER BY, LIMIT
     */
    public static function select(string $table, array $columns = ['*'], array $options = []): array
    {
        $cols = implode(', ', array_map([self::class, 'sanitizeColumnName'], $columns));
        $sql = "SELECT {$cols} FROM " . self::sanitizeTableName($table);
        $params = [];

        if (isset($options['where'])) {
            $whereClause = self::buildWhereClause($options['where'], $params);
            $sql .= " WHERE {$whereClause}";
        }

        if (isset($options['orderBy'])) {
            $sql .= " ORDER BY " . self::sanitizeColumnName($options['orderBy']);
            if (isset($options['orderDirection']) && in_array(strtoupper($options['orderDirection']), ['ASC', 'DESC'])) {
                $sql .= " " . strtoupper($options['orderDirection']);
            }
        }

        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);
            if (isset($options['offset']) && is_numeric($options['offset'])) {
                $sql .= " OFFSET " . intval($options['offset']);
            }
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build a parameterized INSERT query
     */
    public static function insert(string $table, array $data): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty');
        }

        $columns = array_keys($data);
        $sanitizedColumns = array_map([self::class, 'sanitizeColumnName'], $columns);
        $placeholders = array_fill(0, count($data), '?');

        $sql = "INSERT INTO " . self::sanitizeTableName($table) . 
               " (" . implode(', ', $sanitizedColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        return ['sql' => $sql, 'params' => array_values($data)];
    }

    /**
     * Build a parameterized UPDATE query
     */
    public static function update(string $table, array $data, array $whereConditions): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty');
        }
        if (empty($whereConditions)) {
            throw new \InvalidArgumentException('WHERE conditions cannot be empty for UPDATE');
        }

        $setParts = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setParts[] = self::sanitizeColumnName($column) . " = ?";
            $params[] = $value;
        }

        $whereClause = self::buildWhereClause($whereConditions, $params);
        
        $sql = "UPDATE " . self::sanitizeTableName($table) . 
               " SET " . implode(', ', $setParts) . 
               " WHERE {$whereClause}";

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build a parameterized DELETE query
     */
    public static function delete(string $table, array $whereConditions): array
    {
        if (empty($whereConditions)) {
            throw new \InvalidArgumentException('WHERE conditions cannot be empty for DELETE');
        }

        $params = [];
        $whereClause = self::buildWhereClause($whereConditions, $params);

        $sql = "DELETE FROM " . self::sanitizeTableName($table) . " WHERE {$whereClause}";

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build CREATE TABLE query (database-specific)
     */
    public static function createTable(string $table, array $columns): array
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('Columns array cannot be empty');
        }

        $columnDefinitions = [];
        foreach ($columns as $column => $definition) {
            $columnDefinitions[] = self::sanitizeColumnName($column) . ' ' . $definition;
        }

        $sql = "CREATE TABLE " . self::sanitizeTableName($table) . 
               " (" . implode(', ', $columnDefinitions) . ")";

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build ALTER TABLE query
     */
    public static function alterTable(string $table, array $alterations): array
    {
        if (empty($alterations)) {
            throw new \InvalidArgumentException('Alterations array cannot be empty');
        }

        $sql = "ALTER TABLE " . self::sanitizeTableName($table) . " " . implode(', ', $alterations);

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build DROP TABLE query
     */
    public static function dropTable(string $table, bool $ifExists = true): array
    {
        $sql = "DROP TABLE ";
        if ($ifExists) {
            $sql .= "IF EXISTS ";
        }
        $sql .= self::sanitizeTableName($table);

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build TRUNCATE/DELETE ALL query (SQLite compatible)
     */
    public static function truncateTable(string $table): array
    {
        if (self::$databaseType === 'sqlite') {
            // SQLite doesn't support TRUNCATE, use DELETE
            $sql = "DELETE FROM " . self::sanitizeTableName($table);
        } else {
            $sql = "TRUNCATE TABLE " . self::sanitizeTableName($table);
        }

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Show tables query (database-specific)
     */
    public static function showTables(): array
    {
        if (self::$databaseType === 'sqlite') {
            $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
        } else {
            $sql = "SHOW TABLES";
        }

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Describe table query (database-specific)
     */
    public static function describeTable(string $table): array
    {
        if (self::$databaseType === 'sqlite') {
            $sql = "PRAGMA table_info(" . self::sanitizeTableName($table) . ")";
        } else {
            $sql = "DESCRIBE " . self::sanitizeTableName($table);
        }

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build INNER JOIN query
     */
    public static function innerJoin(string $table1, string $table2, string $onCondition, array $columns = ['*']): array
    {
        $cols = implode(', ', array_map([self::class, 'sanitizeColumnName'], $columns));
        $sql = "SELECT {$cols} FROM " . self::sanitizeTableName($table1) . 
               " INNER JOIN " . self::sanitizeTableName($table2) . 
               " ON {$onCondition}";

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build LEFT JOIN query
     */
    public static function leftJoin(string $table1, string $table2, string $onCondition, array $columns = ['*']): array
    {
        $cols = implode(', ', array_map([self::class, 'sanitizeColumnName'], $columns));
        $sql = "SELECT {$cols} FROM " . self::sanitizeTableName($table1) . 
               " LEFT JOIN " . self::sanitizeTableName($table2) . 
               " ON {$onCondition}";

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build RIGHT JOIN query (not supported in SQLite)
     */
    public static function rightJoin(string $table1, string $table2, string $onCondition, array $columns = ['*']): array
    {
        if (self::$databaseType === 'sqlite') {
            throw new \InvalidArgumentException('RIGHT JOIN is not supported in SQLite');
        }

        $cols = implode(', ', array_map([self::class, 'sanitizeColumnName'], $columns));
        $sql = "SELECT {$cols} FROM " . self::sanitizeTableName($table1) . 
               " RIGHT JOIN " . self::sanitizeTableName($table2) . 
               " ON {$onCondition}";

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build CROSS JOIN query
     */
    public static function crossJoin(string $table1, string $table2, array $columns = ['*']): array
    {
        $cols = implode(', ', array_map([self::class, 'sanitizeColumnName'], $columns));
        $sql = "SELECT {$cols} FROM " . self::sanitizeTableName($table1) . 
               " CROSS JOIN " . self::sanitizeTableName($table2);

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build UNION query
     */
    public static function union(string $query1, string $query2, bool $all = false): array
    {
        $type = $all ? 'UNION ALL' : 'UNION';
        $sql = "({$query1}) {$type} ({$query2})";

        return ['sql' => $sql, 'params' => []];
    }

    /**
     * Build aggregate functions
     */
    public static function count(string $column = '*'): string
    {
        return "COUNT(" . ($column === '*' ? '*' : self::sanitizeColumnName($column)) . ")";
    }

    public static function sum(string $column): string
    {
        return "SUM(" . self::sanitizeColumnName($column) . ")";
    }

    public static function avg(string $column): string
    {
        return "AVG(" . self::sanitizeColumnName($column) . ")";
    }

    public static function min(string $column): string
    {
        return "MIN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function max(string $column): string
    {
        return "MAX(" . self::sanitizeColumnName($column) . ")";
    }

    /**
     * Build GROUP BY clause
     */
    public static function groupBy($columns): string
    {
        if (is_array($columns)) {
            $cols = array_map([self::class, 'sanitizeColumnName'], $columns);
            return "GROUP BY " . implode(', ', $cols);
        }
        return "GROUP BY " . self::sanitizeColumnName($columns);
    }

    /**
     * Build HAVING clause
     */
    public static function having(string $condition): string
    {
        return "HAVING {$condition}";
    }

    /**
     * Build BETWEEN expression with parameters
     */
    public static function between(string $column, $start, $end): array
    {
        $sql = self::sanitizeColumnName($column) . " BETWEEN ? AND ?";
        return ['expression' => $sql, 'params' => [$start, $end]];
    }

    /**
     * Build CONCAT expression (database-specific)
     */
    public static function concat(...$args): string
    {
        if (self::$databaseType === 'sqlite') {
            return implode(' || ', $args);
        }
        return "CONCAT(" . implode(', ', $args) . ")";
    }

    /**
     * Build string functions
     */
    public static function length(string $column): string
    {
        return "LENGTH(" . self::sanitizeColumnName($column) . ")";
    }

    public static function substring(string $column, int $start, ?int $length = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($length !== null) {
            return "SUBSTR({$col}, {$start}, {$length})";
        }
        return "SUBSTR({$col}, {$start})";
    }

    /**
     * Private helper methods
     */
    private static function sanitizeTableName(string $table): string
    {
        // Remove any potentially dangerous characters and wrap in backticks
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        return "`{$table}`";
    }

    private static function sanitizeColumnName(string $column): string
    {
        // Handle special cases and wildcards
        if ($column === '*') {
            return '*';
        }
        
        // Handle table.column notation
        if (strpos($column, '.') !== false) {
            $parts = explode('.', $column);
            return self::sanitizeTableName($parts[0]) . '.' . self::sanitizeColumnName($parts[1]);
        }
        
        // Remove dangerous characters and wrap
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        return "`{$column}`";
    }

    private static function buildWhereClause(array $conditions, array &$params): string
    {
        $whereParts = [];
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle IN clause
                $placeholders = array_fill(0, count($value), '?');
                $whereParts[] = self::sanitizeColumnName($column) . " IN (" . implode(', ', $placeholders) . ")";
                $params = array_merge($params, $value);
            } else {
                $whereParts[] = self::sanitizeColumnName($column) . " = ?";
                $params[] = $value;
            }
        }
        
        return implode(' AND ', $whereParts);
    }
}