<?php

namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;

class TableRepository extends EntityRepository
{
    /** @var array<int, string> */
    private array $blacklistedTables;

    public function __construct()
    {
        $this->blacklistedTables = ['login_logs', 'users', 'Entreprises_Activite'];
    }

    /**
     * Récupère la liste des tables non blacklistées
     * @return array<int, string>
     */
    public function getTables(): array
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        $allTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return array_diff($allTables, $this->blacklistedTables);
    }

    /**
     * Récupère les données d'une table avec pagination
     * @param string $table
     * @param int $page
     * @param int $perPage
     * @return array<int, array<string, mixed>>
     */
    public function getTableData(string $table, int $page = 1, int $perPage = 20): array
    {
        $this->validateTable($table);
        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare("
            SELECT * FROM `$table`
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function validateTable(string $table): void
    {
        if (in_array($table, $this->blacklistedTables)) {
            throw new Exception("Table non autorisée");
        }
    }

    /**
     * Ajoute un enregistrement dans une table
     * @param string $table
     * @param array<string, mixed> $data
     * @return int ID de l'enregistrement créé
     * @throws Exception
     */
    public function addRecord(string $table, array $data): int
    {
        $this->validateTable($table);

        $filteredData = $this->filterPostData($data);
        $columns = array_keys($filteredData);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filteredData);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Filtre les données POST pour ne garder que les champs pertinents
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterPostData(array $data): array
    {
        return array_filter($data, function ($key) {
            return !in_array($key, ['action', 'table', 'csrf_token'], true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Modifie un enregistrement dans une table
     * @param string $table
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public function editRecord(string $table, array $data): void
    {
        $this->validateTable($table);

        if (!isset($data['id'])) {
            throw new Exception("L'ID est requis pour la modification");
        }

        $id = $data['id'];
        $filteredData = $this->filterPostData($data);

        // Création des paires colonne=:colonne pour la requête UPDATE
        $setPairs = array_map(
            fn($column) => "`$column` = :$column",
            array_keys($filteredData)
        );

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE id = :id",
            $table,
            implode(', ', $setPairs)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([...$filteredData, 'id' => $id]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucun enregistrement n'a été modifié");
        }
    }

    /**
     * Supprime un enregistrement d'une table
     * @param string $table
     * @param int|string $id
     * @throws Exception
     */
    public function deleteRecord(string $table, int|string $id): void
    {
        $this->validateTable($table);

        $sql = sprintf("DELETE FROM `%s` WHERE id = :id", $table);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucun enregistrement n'a été supprimé");
        }
    }
}
