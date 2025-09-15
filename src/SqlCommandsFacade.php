<?php

namespace SqlCommands;

/**
 * Facade for easy access to SQL Commands functionality
 */
class SqlCommandsFacade
{
    /**
     * Get the SqlCommands instance
     */
    public static function commands(): SqlCommands
    {
        static $instance = null;
        
        if ($instance === null) {
            $instance = new SqlCommands();
        }
        
        return $instance;
    }

    /**
     * Get the SqlSimulator instance
     */
    public static function simulator(string $databasePath = null): SqlSimulator
    {
        static $instances = [];
        
        $path = $databasePath ?: ':memory:';
        
        if (!isset($instances[$path])) {
            $instances[$path] = new SqlSimulator($path);
        }
        
        return $instances[$path];
    }

    /**
     * Quick SELECT query
     */
    public static function select(string $table, array $columns = ['*'], array $options = []): array
    {
        return self::commands()::select($table, $columns, $options);
    }

    /**
     * Quick INSERT query
     */
    public static function insert(string $table, array $data): array
    {
        return self::commands()::insert($table, $data);
    }

    /**
     * Quick UPDATE query
     */
    public static function update(string $table, array $data, array $where): array
    {
        return self::commands()::update($table, $data, $where);
    }

    /**
     * Quick DELETE query
     */
    public static function delete(string $table, array $where): array
    {
        return self::commands()::delete($table, $where);
    }

    /**
     * Execute a query using the default simulator
     */
    public static function execute(array $queryData, string $databasePath = null): array
    {
        return self::simulator($databasePath)->executeQuery($queryData);
    }

    /**
     * Create a practice environment with sample data
     */
    public static function createPracticeEnvironment(string $databasePath): array
    {
        $simulator = self::simulator($databasePath);
        
        $results = [];
        $results['tables'] = $simulator->createSampleTables();
        $results['data'] = $simulator->insertSampleData();
        $results['schema'] = $simulator->getSchema();
        
        return $results;
    }
}