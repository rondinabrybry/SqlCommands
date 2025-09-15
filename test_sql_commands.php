<?php
require_once __DIR__ . '/SqlCommands/SqlCommands.php';

use SqlCommands\SqlCommands;

// Test SELECT
assert(SqlCommands::select('users', ['id', 'name']) === 'SELECT id, name FROM `users`');
// Test INSERT
assert(SqlCommands::insert('users', ['name' => 'John', 'email' => 'john@example.com']) === "INSERT INTO `users` (name, email) VALUES ('John', 'john@example.com')");
// Test UPDATE
assert(SqlCommands::update('users', ['name' => 'Jane'], "id=1") === "UPDATE `users` SET name='Jane' WHERE id=1");
// Test DELETE
assert(SqlCommands::delete('users', "id=1") === "DELETE FROM `users` WHERE id=1");
// Test INNER JOIN
assert(SqlCommands::innerJoin('users', 'orders', 'users.id = orders.user_id') === "SELECT * FROM `users` INNER JOIN `orders` ON users.id = orders.user_id");
// Test CONCAT
assert(SqlCommands::concat('first_name', 'last_name') === "CONCAT(first_name, last_name)");
// Test BETWEEN
assert(SqlCommands::between('age', 18, 30) === "age BETWEEN '18' AND '30'");

echo "All tests passed!\n";
