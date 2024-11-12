<?php

namespace Config;

use App\Exception\DatabaseException;

class Database
{
    private static ?self $instance = null;
    private \PDO $pdo;
/** @var array<int, int|bool> */
    private array $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $_ENV['DB_HOST'], $_ENV['DB_NAME']);
            $this->pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $this->options);
        } catch (\PDOException $e) {
            throw DatabaseException::fromPDOException($e);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    // Empêcher le clonage
    private function __clone()
    {
    }

    // Empêcher la désérialisation
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
