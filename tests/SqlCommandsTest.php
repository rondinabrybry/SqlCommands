<?php

namespace SqlCommands\Tests;

use PHPUnit\Framework\TestCase;
use SqlCommands\SqlCommands;

class SqlCommandsTest extends TestCase
{
    public function setUp(): void
    {
        SqlCommands::setDatabaseType('sqlite');
    }

    public function testSelectQuery()
    {
        $result = SqlCommands::select('users', ['id', 'name']);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertEquals('SELECT `id`, `name` FROM `users`', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function testSelectWithOptions()
    {
        $result = SqlCommands::select('users', ['*'], [
            'where' => ['status' => 'active'],
            'orderBy' => 'name',
            'limit' => 10
        ]);
        
        $this->assertStringContainsString('WHERE `status` = ?', $result['sql']);
        $this->assertStringContainsString('ORDER BY `name`', $result['sql']);
        $this->assertStringContainsString('LIMIT 10', $result['sql']);
        $this->assertEquals(['active'], $result['params']);
    }

    public function testInsertQuery()
    {
        $data = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $result = SqlCommands::insert('users', $data);
        
        $this->assertIsArray($result);
        $this->assertEquals('INSERT INTO `users` (`name`, `email`) VALUES (?, ?)', $result['sql']);
        $this->assertEquals(['John Doe', 'john@example.com'], $result['params']);
    }

    public function testInsertWithEmptyData()
    {
        $this->expectException(\InvalidArgumentException::class);
        SqlCommands::insert('users', []);
    }

    public function testUpdateQuery()
    {
        $data = ['name' => 'Jane Doe'];
        $where = ['id' => 1];
        $result = SqlCommands::update('users', $data, $where);
        
        $this->assertStringContainsString('UPDATE `users` SET `name` = ?', $result['sql']);
        $this->assertStringContainsString('WHERE `id` = ?', $result['sql']);
        $this->assertEquals(['Jane Doe', 1], $result['params']);
    }

    public function testUpdateWithEmptyWhere()
    {
        $this->expectException(\InvalidArgumentException::class);
        SqlCommands::update('users', ['name' => 'John'], []);
    }

    public function testDeleteQuery()
    {
        $where = ['id' => 1];
        $result = SqlCommands::delete('users', $where);
        
        $this->assertEquals('DELETE FROM `users` WHERE `id` = ?', $result['sql']);
        $this->assertEquals([1], $result['params']);
    }

    public function testDeleteWithEmptyWhere()
    {
        $this->expectException(\InvalidArgumentException::class);
        SqlCommands::delete('users', []);
    }

    public function testCreateTable()
    {
        $columns = [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'name' => 'TEXT NOT NULL'
        ];
        $result = SqlCommands::createTable('test_table', $columns);
        
        $expectedSql = 'CREATE TABLE `test_table` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL)';
        $this->assertEquals($expectedSql, $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function testDropTable()
    {
        $result = SqlCommands::dropTable('test_table');
        
        $this->assertEquals('DROP TABLE IF EXISTS `test_table`', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function testShowTables()
    {
        $result = SqlCommands::showTables();
        
        // Should use SQLite-specific query
        $this->assertStringContainsString('sqlite_master', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function testInnerJoin()
    {
        $result = SqlCommands::innerJoin('users', 'orders', 'users.id = orders.user_id');
        
        $expectedSql = 'SELECT * FROM `users` INNER JOIN `orders` ON users.id = orders.user_id';
        $this->assertEquals($expectedSql, $result['sql']);
    }

    public function testLeftJoin()
    {
        $result = SqlCommands::leftJoin('users', 'orders', 'users.id = orders.user_id', ['users.name', 'orders.total']);
        
        $this->assertStringContainsString('SELECT `users`.`name`, `orders`.`total`', $result['sql']);
        $this->assertStringContainsString('LEFT JOIN', $result['sql']);
    }

    public function testRightJoinThrowsExceptionForSQLite()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('RIGHT JOIN is not supported in SQLite');
        
        SqlCommands::rightJoin('users', 'orders', 'users.id = orders.user_id');
    }

    public function testAggregateFunctions()
    {
        $this->assertEquals('COUNT(*)', SqlCommands::count());
        $this->assertEquals('COUNT(`id`)', SqlCommands::count('id'));
        $this->assertEquals('SUM(`amount`)', SqlCommands::sum('amount'));
        $this->assertEquals('AVG(`score`)', SqlCommands::avg('score'));
        $this->assertEquals('MIN(`date`)', SqlCommands::min('date'));
        $this->assertEquals('MAX(`date`)', SqlCommands::max('date'));
    }

    public function testGroupBy()
    {
        $this->assertEquals('GROUP BY `category`', SqlCommands::groupBy('category'));
        $this->assertEquals('GROUP BY `category`, `status`', SqlCommands::groupBy(['category', 'status']));
    }

    public function testBetween()
    {
        $result = SqlCommands::between('age', 18, 65);
        
        $this->assertEquals('`age` BETWEEN ? AND ?', $result['expression']);
        $this->assertEquals([18, 65], $result['params']);
    }

    public function testConcat()
    {
        SqlCommands::setDatabaseType('sqlite');
        $result = SqlCommands::concat('first_name', 'last_name');
        $this->assertEquals('first_name || last_name', $result);

        SqlCommands::setDatabaseType('mysql');
        $result = SqlCommands::concat('first_name', 'last_name');
        $this->assertEquals('CONCAT(first_name, last_name)', $result);
    }

    public function testStringFunctions()
    {
        $this->assertEquals('LENGTH(`name`)', SqlCommands::length('name'));
        $this->assertEquals('SUBSTR(`name`, 1, 5)', SqlCommands::substring('name', 1, 5));
        $this->assertEquals('SUBSTR(`name`, 1)', SqlCommands::substring('name', 1));
    }

    public function testSanitizeTableName()
    {
        $reflection = new \ReflectionClass(SqlCommands::class);
        $method = $reflection->getMethod('sanitizeTableName');
        $method->setAccessible(true);

        // Test normal table name
        $result = $method->invoke(null, 'users');
        $this->assertEquals('`users`', $result);

        // Test with dangerous characters (should be stripped)
        $result = $method->invoke(null, 'users; DROP TABLE test;');
        $this->assertEquals('`usersDROPTABLEtest`', $result);
    }

    public function testSanitizeColumnName()
    {
        $reflection = new \ReflectionClass(SqlCommands::class);
        $method = $reflection->getMethod('sanitizeColumnName');
        $method->setAccessible(true);

        // Test wildcard
        $result = $method->invoke(null, '*');
        $this->assertEquals('*', $result);

        // Test normal column
        $result = $method->invoke(null, 'name');
        $this->assertEquals('`name`', $result);

        // Test table.column notation
        $result = $method->invoke(null, 'users.name');
        $this->assertEquals('`users`.`name`', $result);
    }
}