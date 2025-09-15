<?php
namespace SqlCommands;

class SqlCommands
{
    /**
     * Build a SELECT query.
     */
    public static function select($table, $columns = ['*'])
    {
        $cols = implode(', ', $columns);
        return "SELECT $cols FROM `$table`";
    }

    // Common & Simple
    public static function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(function($v) { return "'" . addslashes($v) . "'"; }, array_values($data)));
        return "INSERT INTO `$table` ($columns) VALUES ($values)";
    }
    public static function update($table, $data, $where)
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col='" . addslashes($val) . "'";
        }
        $setStr = implode(', ', $set);
        return "UPDATE `$table` SET $setStr WHERE $where";
    }
    public static function delete($table, $where)
    {
        return "DELETE FROM `$table` WHERE $where";
    }
    public static function createTable($table, $columns)
    {
        $cols = implode(', ', $columns);
        return "CREATE TABLE `$table` ($cols)";
    }
    public static function alterTable($table, $alterations)
    {
        $alts = implode(', ', $alterations);
        return "ALTER TABLE `$table` $alts";
    }
    public static function dropTable($table)
    {
        return "DROP TABLE `$table`";
    }
    public static function truncateTable($table)
    {
        return "TRUNCATE TABLE `$table`";
    }

    // Moderately Common
    public static function useDatabase($database)
    {
        return "USE `$database`";
    }
    public static function describeTable($table)
    {
        return "DESCRIBE `$table`";
    }
    public static function showTables()
    {
        return "SHOW TABLES";
    }

    // Joins & Set Operations
    public static function leftJoin($table1, $table2, $on)
    {
        return "SELECT * FROM `{$table1}` LEFT JOIN `{$table2}` ON {$on}";
    }
    public static function rightJoin($table1, $table2, $on)
    {
        return "SELECT * FROM `{$table1}` RIGHT JOIN `{$table2}` ON {$on}";
    }
    public static function fullOuterJoin($table1, $table2, $on)
    {
        return "SELECT * FROM `{$table1}` FULL OUTER JOIN `{$table2}` ON {$on}";
    }
    public static function union($query1, $query2, $all = false)
    {
        $type = $all ? 'UNION ALL' : 'UNION';
        return "$query1 $type $query2";
    }
    public static function intersect($query1, $query2)
    {
        return "$query1 INTERSECT $query2";
    }
    public static function except($query1, $query2)
    {
        return "$query1 EXCEPT $query2";
    }

    // String & Aggregate Functions
    public static function length($column)
    {
        return "LENGTH($column)";
    }
    public static function substring($column, $start, $length = null)
    {
        if ($length !== null) {
            return "SUBSTRING($column, $start, $length)";
        }
        return "SUBSTRING($column, $start)";
    }
    public static function count($column = '*')
    {
        return "COUNT($column)";
    }
    public static function sum($column)
    {
        return "SUM($column)";
    }
    public static function avg($column)
    {
        return "AVG($column)";
    }
    public static function min($column)
    {
        return "MIN($column)";
    }
    public static function max($column)
    {
        return "MAX($column)";
    }
    public static function groupBy($columns)
    {
        if (is_array($columns)) {
            $cols = implode(', ', $columns);
        } else {
            $cols = $columns;
        }
        return "GROUP BY $cols";
    }
    public static function having($condition)
    {
        return "HAVING $condition";
    }
    public static function between($column, $start, $end)
    {
        return "$column BETWEEN '$start' AND '$end'";
    }

    /**
     * Build a CONCAT expression.
     */
    public static function concat(...$args)
    {
        $expr = implode(", ", $args);
        return "CONCAT($expr)";
    }

    /**
     * Build an INNER JOIN clause.
     */
    public static function innerJoin($table1, $table2, $on)
    {
        return "SELECT * FROM `{$table1}` INNER JOIN `{$table2}` ON {$on}";
    }

    /**
     * Build a CROSS JOIN clause.
     */
    public static function crossJoin($table1, $table2)
    {
        return "SELECT * FROM `{$table1}` CROSS JOIN `{$table2}`";
    }
}
