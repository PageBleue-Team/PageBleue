<?php

namespace App\Domain\Entity;

use PDO;
use InvalidArgumentException;
use RuntimeException;
use PDOException;

abstract class EntityRepository
{
    protected PDO $pdo;

    public function __construct(PDO $connection)
    {
        $this->pdo = $connection;
    }

    /**
     * Récupère un enregistrement par son ID
     * @param string $table
     * @param int $id
     * @return array<string, mixed>|null
     */
    protected function findById(string $table, int $id): ?array
    {
        if (!$this->isValidTableName($table)) {
            throw new InvalidArgumentException('Nom de table invalide');
        }
        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->quoteIdentifier($table) . ' WHERE id = ?');
        try {
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new RuntimeException('Erreur lors de la récupération des données', 0, $e);
        }
    }

    /**
     * Crée un nouvel enregistrement
     * @param string $table
     * @param array<string, mixed> $data
     * @return int ID de l'enregistrement créé
     */
    protected function create(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("
            INSERT INTO `$table` ($columns)
            VALUES ($values)
        ");
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un enregistrement
     * @param string $table
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    protected function update(string $table, int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $stmt = $this->pdo->prepare("
            UPDATE `$table`
            SET $sets
            WHERE id = ?
        ");
        return $stmt->execute([...array_values($data), $id]);
    }

    /**
     * Supprime un enregistrement
     * @param string $table
     * @param int $id
     * @return bool
     */
    protected function delete(string $table, int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM `$table` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Vérifie si un enregistrement existe
     * @param string $table
     * @param int $id
     * @return bool
     */
    protected function exists(string $table, int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM `$table` WHERE id = ?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetch();
    }

    protected function isValidTableName(string $table): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) === 1;
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
