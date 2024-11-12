<?php

namespace Config;

use App\Exception\DatabaseException;

class Database
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;
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
        $this->validateEnvironmentVariables();
        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $_ENV['DB_HOST'], $_ENV['DB_NAME']);
            $this->pdo = new \PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [...$this->options, \PDO::ATTR_TIMEOUT => 5]
            );
        } catch (\PDOException $e) {
            throw DatabaseException::fromPDOException($e);
        }
    }

    private function validateEnvironmentVariables(): void
    {
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        $missing = array_filter($required, fn($var) => empty($_ENV[$var]));
        if (!empty($missing)) {
            throw new DatabaseException(
                'Variables d\'environnement manquantes : ' . implode(', ', $missing)
            );
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
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new DatabaseException('Impossible d\'établir une connexion à la base de données');
        }

        return $this->pdo;
    }

    public function isConnected(): bool
    {
        if (!isset($this->pdo)) {
            return false;
        }

        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
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
