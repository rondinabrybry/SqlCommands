<?php
require_once __DIR__ . '/SqlCommands/SqlCommands.php';

use SqlCommands\SqlCommands;

class SqlSimulator
{
    private $pdo;

    public function __construct($sqlitePath)
    {
        $this->pdo = new PDO('sqlite:' . $sqlitePath);
    }

    public function runCommand($sql)
    {
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return $this->pdo->errorInfo();
        }
        if (stripos($sql, 'select') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $stmt->rowCount();
    }
}

// Usage Example:
// $sim = new SqlSimulator(__DIR__ . '/database.sqlite');
// $result = $sim->runCommand('SELECT * FROM users');
// print_r($result);
