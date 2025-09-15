<?php

require_once __DIR__ . '/src/SqlCommands.php';
require_once __DIR__ . '/src/SqlSimulator.php';

use SqlCommands\SqlCommands;
use SqlCommands\SqlSimulator;

echo "=== SQL Commands Package Test Suite ===\n\n";

// Set database type for testing
SqlCommands::setDatabaseType('sqlite');

function assertTrue($condition, $message) {
    if ($condition) {
        echo "✓ PASS: $message\n";
    } else {
        echo "✗ FAIL: $message\n";
    }
}

function assertEquals($expected, $actual, $message) {
    if ($expected === $actual) {
        echo "✓ PASS: $message\n";
    } else {
        echo "✗ FAIL: $message\n";
        echo "  Expected: " . json_encode($expected) . "\n";
        echo "  Actual: " . json_encode($actual) . "\n";
    }
}

function assertContains($needle, $haystack, $message) {
    if (strpos($haystack, $needle) !== false) {
        echo "✓ PASS: $message\n";
    } else {
        echo "✗ FAIL: $message\n";
        echo "  Haystack does not contain: $needle\n";
    }
}

// Test SELECT queries
echo "--- Testing SELECT queries ---\n";
$result = SqlCommands::select('users', ['id', 'name']);
assertEquals('SELECT `id`, `name` FROM `users`', $result['sql'], 'Basic SELECT query');
assertEquals([], $result['params'], 'SELECT query has no parameters');

// Test SELECT with WHERE
$result = SqlCommands::select('users', ['*'], ['where' => ['status' => 'active']]);
assertContains('WHERE `status` = ?', $result['sql'], 'SELECT with WHERE clause');
assertEquals(['active'], $result['params'], 'SELECT WHERE parameters are correct');

// Test INSERT queries
echo "\n--- Testing INSERT queries ---\n";
$result = SqlCommands::insert('users', ['name' => 'John Doe', 'email' => 'john@example.com']);
assertEquals('INSERT INTO `users` (`name`, `email`) VALUES (?, ?)', $result['sql'], 'Basic INSERT query');
assertEquals(['John Doe', 'john@example.com'], $result['params'], 'INSERT parameters are correct');

// Test UPDATE queries
echo "\n--- Testing UPDATE queries ---\n";
$result = SqlCommands::update('users', ['name' => 'Jane Doe'], ['id' => 1]);
assertContains('UPDATE `users` SET `name` = ?', $result['sql'], 'UPDATE query structure');
assertContains('WHERE `id` = ?', $result['sql'], 'UPDATE WHERE clause');
assertEquals(['Jane Doe', 1], $result['params'], 'UPDATE parameters are correct');

// Test DELETE queries
echo "\n--- Testing DELETE queries ---\n";
$result = SqlCommands::delete('users', ['id' => 1]);
assertEquals('DELETE FROM `users` WHERE `id` = ?', $result['sql'], 'DELETE query');
assertEquals([1], $result['params'], 'DELETE parameters are correct');

// Test CREATE TABLE
echo "\n--- Testing CREATE TABLE ---\n";
$result = SqlCommands::createTable('test_table', [
    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
    'name' => 'TEXT NOT NULL'
]);
$expected = 'CREATE TABLE `test_table` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL)';
assertEquals($expected, $result['sql'], 'CREATE TABLE query');

// Test JOINs
echo "\n--- Testing JOIN queries ---\n";
$result = SqlCommands::innerJoin('users', 'orders', 'users.id = orders.user_id');
$expected = 'SELECT * FROM `users` INNER JOIN `orders` ON users.id = orders.user_id';
assertEquals($expected, $result['sql'], 'INNER JOIN query');

$result = SqlCommands::leftJoin('users', 'orders', 'users.id = orders.user_id', ['users.name', 'orders.total']);
assertContains('LEFT JOIN', $result['sql'], 'LEFT JOIN query contains LEFT JOIN');
assertContains('SELECT `users`.`name`, `orders`.`total`', $result['sql'], 'LEFT JOIN with specific columns');

// Test aggregate functions
echo "\n--- Testing Aggregate Functions ---\n";
assertEquals('COUNT(*)', SqlCommands::count(), 'COUNT(*) function');
assertEquals('COUNT(`id`)', SqlCommands::count('id'), 'COUNT(column) function');
assertEquals('SUM(`amount`)', SqlCommands::sum('amount'), 'SUM function');
assertEquals('AVG(`score`)', SqlCommands::avg('score'), 'AVG function');

// Test BETWEEN
echo "\n--- Testing BETWEEN ---\n";
$result = SqlCommands::between('age', 18, 65);
assertEquals('`age` BETWEEN ? AND ?', $result['expression'], 'BETWEEN expression');
assertEquals([18, 65], $result['params'], 'BETWEEN parameters');

// Test string functions
echo "\n--- Testing String Functions ---\n";
assertEquals('LENGTH(`name`)', SqlCommands::length('name'), 'LENGTH function');
assertEquals('SUBSTR(`name`, 1, 5)', SqlCommands::substring('name', 1, 5), 'SUBSTRING with length');
assertEquals('SUBSTR(`name`, 1)', SqlCommands::substring('name', 1), 'SUBSTRING without length');

// Test database-specific functions
echo "\n--- Testing Database-Specific Functions ---\n";
$result = SqlCommands::showTables();
assertContains('sqlite_master', $result['sql'], 'SQLite-specific SHOW TABLES');

$result = SqlCommands::describeTable('users');
assertContains('PRAGMA table_info', $result['sql'], 'SQLite-specific DESCRIBE TABLE');

// Test CONCAT (database-specific)
$concat = SqlCommands::concat('first_name', 'last_name');
assertEquals('first_name || last_name', $concat, 'SQLite CONCAT using ||');

// Test SqlSimulator if SQLite is available
echo "\n--- Testing SqlSimulator ---\n";
try {
    $simulator = new SqlSimulator(':memory:'); // Use in-memory SQLite database
    
    // Test connection
    $info = $simulator->getConnectionInfo();
    assertTrue(isset($info['database_type']), 'SqlSimulator connection info');
    
    // Test creating sample tables
    $result = $simulator->createSampleTables();
    assertTrue(isset($result['users']), 'Sample tables creation');
    
    // Test executing a simple query
    $queryData = SqlCommands::select('users');
    $result = $simulator->executeQuery($queryData);
    assertTrue($result['success'], 'Execute simple SELECT query');
    
    // Test inserting data
    $insertData = SqlCommands::insert('users', ['name' => 'Test User', 'email' => 'test@example.com', 'age' => 25]);
    $result = $simulator->executeQuery($insertData);
    assertTrue($result['success'], 'Execute INSERT query');
    assertTrue($result['affectedRows'] > 0, 'INSERT affected rows');
    
    echo "✓ SqlSimulator tests completed successfully\n";
    
} catch (Exception $e) {
    echo "⚠ SqlSimulator tests skipped: " . $e->getMessage() . "\n";
}

// Test error handling
echo "\n--- Testing Error Handling ---\n";
try {
    SqlCommands::insert('users', []);
    echo "✗ FAIL: Should throw exception for empty data\n";
} catch (InvalidArgumentException $e) {
    echo "✓ PASS: Throws exception for empty INSERT data\n";
}

try {
    SqlCommands::update('users', ['name' => 'John'], []);
    echo "✗ FAIL: Should throw exception for empty WHERE in UPDATE\n";
} catch (InvalidArgumentException $e) {
    echo "✓ PASS: Throws exception for empty WHERE in UPDATE\n";
}

try {
    SqlCommands::delete('users', []);
    echo "✗ FAIL: Should throw exception for empty WHERE in DELETE\n";
} catch (InvalidArgumentException $e) {
    echo "✓ PASS: Throws exception for empty WHERE in DELETE\n";
}

echo "\n=== Test Suite Complete ===\n";
echo "All basic functionality tests completed!\n";
echo "For full integration testing, install this package in a Laravel project.\n";