<?php

namespace App\Domain\Entity;

use PDO;
use Config\Database;

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
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
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
}
