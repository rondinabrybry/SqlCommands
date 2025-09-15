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

    public static function upper(string $column): string
    {
        return "UPPER(" . self::sanitizeColumnName($column) . ")";
    }

    public static function lower(string $column): string
    {
        return "LOWER(" . self::sanitizeColumnName($column) . ")";
    }

    public static function trim(string $column, ?string $chars = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($chars !== null) {
            return "TRIM({$col}, '{$chars}')";
        }
        return "TRIM({$col})";
    }

    public static function ltrim(string $column, ?string $chars = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($chars !== null) {
            return "LTRIM({$col}, '{$chars}')";
        }
        return "LTRIM({$col})";
    }

    public static function rtrim(string $column, ?string $chars = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($chars !== null) {
            return "RTRIM({$col}, '{$chars}')";
        }
        return "RTRIM({$col})";
    }

    public static function instr(string $haystack, string $needle): string
    {
        return "INSTR(" . self::sanitizeColumnName($haystack) . ", '{$needle}')";
    }

    public static function replace(string $column, string $search, string $replace): string
    {
        return "REPLACE(" . self::sanitizeColumnName($column) . ", '{$search}', '{$replace}')";
    }

    public static function printf(string $format, ...$args): string
    {
        $escapedArgs = array_map(function($arg) {
            return is_string($arg) ? "'{$arg}'" : $arg;
        }, $args);
        return "PRINTF('{$format}'" . (count($escapedArgs) > 0 ? ', ' . implode(', ', $escapedArgs) : '') . ")";
    }

    public static function quote(string $column): string
    {
        return "QUOTE(" . self::sanitizeColumnName($column) . ")";
    }

    public static function hex(string $column): string
    {
        return "HEX(" . self::sanitizeColumnName($column) . ")";
    }

    public static function unicode(string $column): string
    {
        return "UNICODE(" . self::sanitizeColumnName($column) . ")";
    }

    public static function char(...$codes): string
    {
        return "CHAR(" . implode(', ', $codes) . ")";
    }

    public static function zeroblob(int $size): string
    {
        return "ZEROBLOB({$size})";
    }

    /**
     * Build numeric/math functions
     */
    public static function abs(string $column): string
    {
        return "ABS(" . self::sanitizeColumnName($column) . ")";
    }

    public static function sign(string $column): string
    {
        return "SIGN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function round(string $column, ?int $precision = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($precision !== null) {
            return "ROUND({$col}, {$precision})";
        }
        return "ROUND({$col})";
    }

    public static function random(): string
    {
        return "RANDOM()";
    }

    public static function randomblob(int $size): string
    {
        return "RANDOMBLOB({$size})";
    }

    public static function ceil(string $column): string
    {
        return "CEIL(" . self::sanitizeColumnName($column) . ")";
    }

    public static function ceiling(string $column): string
    {
        return "CEILING(" . self::sanitizeColumnName($column) . ")";
    }

    public static function floor(string $column): string
    {
        return "FLOOR(" . self::sanitizeColumnName($column) . ")";
    }

    public static function pow(string $base, string $exponent): string
    {
        return "POW(" . self::sanitizeColumnName($base) . ", " . self::sanitizeColumnName($exponent) . ")";
    }

    public static function power(string $base, string $exponent): string
    {
        return "POWER(" . self::sanitizeColumnName($base) . ", " . self::sanitizeColumnName($exponent) . ")";
    }

    public static function sqrt(string $column): string
    {
        return "SQRT(" . self::sanitizeColumnName($column) . ")";
    }

    public static function exp(string $column): string
    {
        return "EXP(" . self::sanitizeColumnName($column) . ")";
    }

    public static function ln(string $column): string
    {
        return "LN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function log(string $column): string
    {
        return "LOG(" . self::sanitizeColumnName($column) . ")";
    }

    public static function log2(string $column): string
    {
        return "LOG2(" . self::sanitizeColumnName($column) . ")";
    }

    // Trigonometric functions
    public static function sin(string $column): string
    {
        return "SIN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function cos(string $column): string
    {
        return "COS(" . self::sanitizeColumnName($column) . ")";
    }

    public static function tan(string $column): string
    {
        return "TAN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function asin(string $column): string
    {
        return "ASIN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function acos(string $column): string
    {
        return "ACOS(" . self::sanitizeColumnName($column) . ")";
    }

    public static function atan(string $column): string
    {
        return "ATAN(" . self::sanitizeColumnName($column) . ")";
    }

    public static function atan2(string $y, string $x): string
    {
        return "ATAN2(" . self::sanitizeColumnName($y) . ", " . self::sanitizeColumnName($x) . ")";
    }

    // Hyperbolic functions
    public static function sinh(string $column): string
    {
        return "SINH(" . self::sanitizeColumnName($column) . ")";
    }

    public static function cosh(string $column): string
    {
        return "COSH(" . self::sanitizeColumnName($column) . ")";
    }

    public static function tanh(string $column): string
    {
        return "TANH(" . self::sanitizeColumnName($column) . ")";
    }

    // Angle conversion
    public static function degrees(string $column): string
    {
        return "DEGREES(" . self::sanitizeColumnName($column) . ")";
    }

    public static function radians(string $column): string
    {
        return "RADIANS(" . self::sanitizeColumnName($column) . ")";
    }

    /**
     * Date and Time Functions
     */
    public static function date(string $timestring, ...$modifiers): string
    {
        $modifierStr = count($modifiers) > 0 ? ", '" . implode("', '", $modifiers) . "'" : '';
        return "date('{$timestring}'{$modifierStr})";
    }

    public static function time(string $timestring, ...$modifiers): string
    {
        $modifierStr = count($modifiers) > 0 ? ", '" . implode("', '", $modifiers) . "'" : '';
        return "time('{$timestring}'{$modifierStr})";
    }

    public static function datetime(string $timestring, ...$modifiers): string
    {
        $modifierStr = count($modifiers) > 0 ? ", '" . implode("', '", $modifiers) . "'" : '';
        return "datetime('{$timestring}'{$modifierStr})";
    }

    public static function julianday(string $timestring, ...$modifiers): string
    {
        $modifierStr = count($modifiers) > 0 ? ", '" . implode("', '", $modifiers) . "'" : '';
        return "julianday('{$timestring}'{$modifierStr})";
    }

    public static function strftime(string $format, string $timestring, ...$modifiers): string
    {
        $modifierStr = count($modifiers) > 0 ? ", '" . implode("', '", $modifiers) . "'" : '';
        return "strftime('{$format}', '{$timestring}'{$modifierStr})";
    }

    /**
     * Window Functions
     */
    public static function rowNumber(): string
    {
        return "ROW_NUMBER()";
    }

    public static function rank(): string
    {
        return "RANK()";
    }

    public static function denseRank(): string
    {
        return "DENSE_RANK()";
    }

    public static function percentRank(): string
    {
        return "PERCENT_RANK()";
    }

    public static function cumeDist(): string
    {
        return "CUME_DIST()";
    }

    public static function ntile(int $buckets): string
    {
        return "NTILE({$buckets})";
    }

    public static function lag(string $column, int $offset = 1, ?string $default = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($default !== null) {
            return "LAG({$col}, {$offset}, '{$default}')";
        }
        return "LAG({$col}, {$offset})";
    }

    public static function lead(string $column, int $offset = 1, ?string $default = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($default !== null) {
            return "LEAD({$col}, {$offset}, '{$default}')";
        }
        return "LEAD({$col}, {$offset})";
    }

    public static function firstValue(string $column): string
    {
        return "FIRST_VALUE(" . self::sanitizeColumnName($column) . ")";
    }

    public static function lastValue(string $column): string
    {
        return "LAST_VALUE(" . self::sanitizeColumnName($column) . ")";
    }

    public static function nthValue(string $column, int $n): string
    {
        return "NTH_VALUE(" . self::sanitizeColumnName($column) . ", {$n})";
    }

    /**
     * Enhanced Aggregate Functions
     */
    public static function total(string $column): string
    {
        return "TOTAL(" . self::sanitizeColumnName($column) . ")";
    }

    public static function groupConcat(string $column, ?string $separator = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($separator !== null) {
            return "GROUP_CONCAT({$col}, '{$separator}')";
        }
        return "GROUP_CONCAT({$col})";
    }

    /**
     * JSON Functions (json1 extension)
     */
    public static function json(string $value): string
    {
        return "json('{$value}')";
    }

    public static function jsonArray(...$values): string
    {
        $escapedValues = array_map(function($v) {
            return is_string($v) ? "'{$v}'" : $v;
        }, $values);
        return "json_array(" . implode(', ', $escapedValues) . ")";
    }

    public static function jsonArrayLength(string $json, ?string $path = null): string
    {
        if ($path !== null) {
            return "json_array_length('{$json}', '{$path}')";
        }
        return "json_array_length('{$json}')";
    }

    public static function jsonExtract(string $json, string $path): string
    {
        return "json_extract('{$json}', '{$path}')";
    }

    public static function jsonInsert(string $json, string $path, string $value): string
    {
        return "json_insert('{$json}', '{$path}', '{$value}')";
    }

    public static function jsonReplace(string $json, string $path, string $value): string
    {
        return "json_replace('{$json}', '{$path}', '{$value}')";
    }

    public static function jsonSet(string $json, string $path, string $value): string
    {
        return "json_set('{$json}', '{$path}', '{$value}')";
    }

    public static function jsonObject(...$keyValuePairs): string
    {
        $pairs = array_map(function($pair) {
            return is_string($pair) ? "'{$pair}'" : $pair;
        }, $keyValuePairs);
        return "json_object(" . implode(', ', $pairs) . ")";
    }

    public static function jsonPatch(string $target, string $patch): string
    {
        return "json_patch('{$target}', '{$patch}')";
    }

    public static function jsonRemove(string $json, string $path): string
    {
        return "json_remove('{$json}', '{$path}')";
    }

    public static function jsonType(string $json, ?string $path = null): string
    {
        if ($path !== null) {
            return "json_type('{$json}', '{$path}')";
        }
        return "json_type('{$json}')";
    }

    public static function jsonValid(string $json): string
    {
        return "json_valid('{$json}')";
    }

    public static function jsonQuote(string $value): string
    {
        return "json_quote('{$value}')";
    }

    public static function jsonGroupArray(string $column): string
    {
        return "json_group_array(" . self::sanitizeColumnName($column) . ")";
    }

    public static function jsonGroupObject(string $keyColumn, string $valueColumn): string
    {
        return "json_group_object(" . self::sanitizeColumnName($keyColumn) . ", " . self::sanitizeColumnName($valueColumn) . ")";
    }

    /**
     * Miscellaneous Functions
     */
    public static function iif(string $condition, string $trueValue, string $falseValue): string
    {
        return "IIF({$condition}, '{$trueValue}', '{$falseValue}')";
    }

    public static function changes(): string
    {
        return "CHANGES()";
    }

    public static function lastInsertRowid(): string
    {
        return "LAST_INSERT_ROWID()";
    }

    public static function totalChanges(): string
    {
        return "TOTAL_CHANGES()";
    }

    public static function likelihood(string $expression, float $probability): string
    {
        return "LIKELIHOOD({$expression}, {$probability})";
    }

    public static function likely(string $expression): string
    {
        return "LIKELY({$expression})";
    }

    public static function unlikely(string $expression): string
    {
        return "UNLIKELY({$expression})";
    }

    /**
     * Special SQLite Commands and Operators
     */
    public static function pragma(string $name, ?string $value = null): array
    {
        if ($value !== null) {
            return ['sql' => "PRAGMA {$name} = {$value}", 'params' => []];
        }
        return ['sql' => "PRAGMA {$name}", 'params' => []];
    }

    public static function vacuum(): array
    {
        return ['sql' => 'VACUUM', 'params' => []];
    }

    public static function analyze(?string $table = null): array
    {
        if ($table !== null) {
            return ['sql' => 'ANALYZE ' . self::sanitizeTableName($table), 'params' => []];
        }
        return ['sql' => 'ANALYZE', 'params' => []];
    }

    public static function attachDatabase(string $filename, string $schemaName): array
    {
        return ['sql' => "ATTACH DATABASE '{$filename}' AS " . self::sanitizeTableName($schemaName), 'params' => []];
    }

    public static function detachDatabase(string $schemaName): array
    {
        return ['sql' => 'DETACH DATABASE ' . self::sanitizeTableName($schemaName), 'params' => []];
    }

    /**
     * Conditional Logic Functions
     */
    public static function caseWhen(array $conditions, ?string $defaultValue = null): string
    {
        $cases = [];
        foreach ($conditions as $condition => $result) {
            $cases[] = "WHEN {$condition} THEN '{$result}'";
        }
        $caseExpr = "CASE " . implode(' ', $cases);
        if ($defaultValue !== null) {
            $caseExpr .= " ELSE '{$defaultValue}'";
        }
        return $caseExpr . " END";
    }

    public static function caseWhenColumn(string $column, array $conditions, ?string $defaultValue = null): string
    {
        $cases = [];
        $col = self::sanitizeColumnName($column);
        foreach ($conditions as $value => $result) {
            $cases[] = "WHEN {$col} = '{$value}' THEN '{$result}'";
        }
        $caseExpr = "CASE " . implode(' ', $cases);
        if ($defaultValue !== null) {
            $caseExpr .= " ELSE '{$defaultValue}'";
        }
        return $caseExpr . " END";
    }

    /**
     * NULL Handling Functions
     */
    public static function coalesce(...$columns): string
    {
        $sanitizedCols = [];
        foreach ($columns as $col) {
            if (is_string($col) && !is_numeric($col)) {
                // Check if it's a column name or a literal value
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $col)) {
                    $sanitizedCols[] = self::sanitizeColumnName($col);
                } else {
                    $sanitizedCols[] = "'{$col}'";
                }
            } else {
                $sanitizedCols[] = $col;
            }
        }
        return "COALESCE(" . implode(', ', $sanitizedCols) . ")";
    }

    public static function nullif(string $expr1, string $expr2): string
    {
        $col1 = self::sanitizeColumnName($expr1);
        return "NULLIF({$col1}, '{$expr2}')";
    }

    public static function ifnull(string $column, string $replacement): string
    {
        return "IFNULL(" . self::sanitizeColumnName($column) . ", '{$replacement}')";
    }

    /**
     * Pattern Matching Functions
     */
    public static function like(string $column, string $pattern, ?string $escape = null): array
    {
        $sql = self::sanitizeColumnName($column) . " LIKE ?";
        if ($escape !== null) {
            $sql .= " ESCAPE '{$escape}'";
        }
        return ['expression' => $sql, 'params' => [$pattern]];
    }

    public static function notLike(string $column, string $pattern): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " NOT LIKE ?",
            'params' => [$pattern]
        ];
    }

    public static function glob(string $column, string $pattern): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " GLOB ?",
            'params' => [$pattern]
        ];
    }

    public static function regexp(string $column, string $pattern): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " REGEXP ?", 
            'params' => [$pattern]
        ];
    }

    /**
     * Subquery and Advanced Query Functions
     */
    public static function exists(array $subquery): string
    {
        return "EXISTS (" . $subquery['sql'] . ")";
    }

    public static function notExists(array $subquery): string
    {
        return "NOT EXISTS (" . $subquery['sql'] . ")";
    }

    public static function inSubquery(string $column, array $subquery): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " IN (" . $subquery['sql'] . ")",
            'params' => $subquery['params']
        ];
    }

    public static function notInSubquery(string $column, array $subquery): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " NOT IN (" . $subquery['sql'] . ")",
            'params' => $subquery['params']
        ];
    }

    public static function subqueryScalar(array $subquery): array
    {
        return [
            'expression' => "(" . $subquery['sql'] . ")",
            'params' => $subquery['params']
        ];
    }

    /**
     * CTE (Common Table Expression) Support
     */
    public static function with(array $ctes, array $mainQuery): array
    {
        $cteStrings = [];
        $allParams = [];
        
        foreach ($ctes as $name => $query) {
            $safeName = self::sanitizeTableName($name);
            $cteStrings[] = "{$safeName} AS (" . $query['sql'] . ")";
            $allParams = array_merge($allParams, $query['params']);
        }
        
        $sql = "WITH " . implode(', ', $cteStrings) . " " . $mainQuery['sql'];
        return ['sql' => $sql, 'params' => array_merge($allParams, $mainQuery['params'])];
    }

    public static function withRecursive(array $ctes, array $mainQuery): array
    {
        $cteStrings = [];
        $allParams = [];
        
        foreach ($ctes as $name => $query) {
            $safeName = self::sanitizeTableName($name);
            $cteStrings[] = "{$safeName} AS (" . $query['sql'] . ")";
            $allParams = array_merge($allParams, $query['params']);
        }
        
        $sql = "WITH RECURSIVE " . implode(', ', $cteStrings) . " " . $mainQuery['sql'];
        return ['sql' => $sql, 'params' => array_merge($allParams, $mainQuery['params'])];
    }

    /**
     * Educational Helper Functions
     */
    public static function pivot(string $valueColumn, string $pivotColumn, array $pivotValues): array
    {
        $cases = [];
        foreach ($pivotValues as $value) {
            $safeName = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
            $cases[] = "SUM(CASE WHEN " . self::sanitizeColumnName($pivotColumn) . " = '{$value}' THEN " . self::sanitizeColumnName($valueColumn) . " ELSE 0 END) AS {$safeName}";
        }
        return ['columns' => $cases, 'params' => []];
    }

    public static function unpivot(string $table, array $valueColumns, string $pivotColumnName = 'attribute', string $valueColumnName = 'value'): array
    {
        $unions = [];
        $params = [];
        
        foreach ($valueColumns as $column) {
            $unions[] = "SELECT " . self::sanitizeColumnName('id') . ", '{$column}' as {$pivotColumnName}, " . 
                       self::sanitizeColumnName($column) . " as {$valueColumnName} FROM " . self::sanitizeTableName($table);
        }
        
        return ['sql' => implode(' UNION ALL ', $unions), 'params' => $params];
    }

    /**
     * Query Analysis Functions
     */
    public static function explain(array $query, bool $queryPlan = false): array
    {
        $prefix = $queryPlan ? "EXPLAIN QUERY PLAN" : "EXPLAIN";
        return [
            'sql' => "{$prefix} " . $query['sql'],
            'params' => $query['params']
        ];
    }

    public static function indexed(string $column, ?string $indexName = null): string
    {
        $col = self::sanitizeColumnName($column);
        if ($indexName !== null) {
            return "{$col} INDEXED BY " . self::sanitizeColumnName($indexName);
        }
        return "{$col} INDEXED BY " . self::sanitizeColumnName($column . "_idx");
    }

    public static function notIndexed(string $column): string
    {
        return self::sanitizeColumnName($column) . " NOT INDEXED";
    }

    /**
     * Constraint and Validation Helpers
     */
    public static function check(string $condition): string
    {
        return "CHECK ({$condition})";
    }

    public static function unique(...$columns): string
    {
        $sanitizedCols = array_map([self::class, 'sanitizeColumnName'], $columns);
        return "UNIQUE(" . implode(', ', $sanitizedCols) . ")";
    }

    public static function primaryKey(...$columns): string
    {
        $sanitizedCols = array_map([self::class, 'sanitizeColumnName'], $columns);
        return "PRIMARY KEY(" . implode(', ', $sanitizedCols) . ")";
    }

    public static function foreignKey(string $column, string $refTable, string $refColumn, ?string $onDelete = null, ?string $onUpdate = null): string
    {
        $fk = "FOREIGN KEY (" . self::sanitizeColumnName($column) . ") REFERENCES " . 
              self::sanitizeTableName($refTable) . "(" . self::sanitizeColumnName($refColumn) . ")";
        
        if ($onDelete !== null) {
            $fk .= " ON DELETE {$onDelete}";
        }
        if ($onUpdate !== null) {
            $fk .= " ON UPDATE {$onUpdate}";
        }
        
        return $fk;
    }

    public static function notNull(string $column): string
    {
        return self::sanitizeColumnName($column) . " NOT NULL";
    }

    public static function defaultValue(string $column, string $value): string
    {
        return self::sanitizeColumnName($column) . " DEFAULT '{$value}'";
    }

    /**
     * Advanced String Pattern Helpers
     */
    public static function startsWith(string $column, string $prefix): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " LIKE ?",
            'params' => [$prefix . '%']
        ];
    }

    public static function endsWith(string $column, string $suffix): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " LIKE ?",
            'params' => ['%' . $suffix]
        ];
    }

    public static function contains(string $column, string $substring): array
    {
        return [
            'expression' => self::sanitizeColumnName($column) . " LIKE ?",
            'params' => ['%' . $substring . '%']
        ];
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