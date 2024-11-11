<?php

namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;
use Config\Database;
use PDO;

class TableRepository extends EntityRepository
{
    /** @var array<int, string> */
    private array $blacklistedTables;

    public function __construct(PDO $connection)
    {
        parent::__construct($connection);
        $this->blacklistedTables = ['login_logs', 'Users', 'Entreprises_Activite'];
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
     * @return int|null
     */
    public function addRecord(string $table, array $data): ?int
    {
        $this->validateTable($table);

        $filteredData = array_filter($data, function ($key) {
            return !in_array($key, ['action', 'table', 'csrf_token']);
        }, ARRAY_FILTER_USE_KEY);

        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($filteredData)));
        $values = implode(', ', array_fill(0, count($filteredData), '?'));

        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute(array_values($filteredData))) {
            return (int)$this->pdo->lastInsertId();
        }

        return null;
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

    /**
     * Récupère la structure d'une table
     * @param string $table
     * @return array<string, array>
     */
    public function getTableStructure(string $table): array
    {
        $this->validateTable($table);
        $stmt = $this->pdo->prepare("DESCRIBE `$table`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les données pour un champ de type foreign key
     * @param string $tableName
     * @return array<int, array<string, mixed>>
     */
    public function getForeignKeyData(string $tableName): array
    {
        try {
            // Mapping des noms de tables (singulier vers réel)
            $tableMapping = [
                'Entreprise' => 'Entreprises',
                'Adresse' => 'Adresse',
                'Contact' => 'Contact',
                'Juridique' => 'Juridique',
                'Activite' => 'Activite',
                'Stage' => 'Stage',
                'Tuteur' => 'Tuteur'
            ];

            // Utiliser le nom correct de la table
            $realTableName = $tableMapping[$tableName] ?? $tableName;

            // Obtenir le prochain ID disponible
            $nextId = $this->getNextId($realTableName);

            // Récupérer les données existantes avec l'ID dans l'affichage
            $displayColumns = [
                'Entreprises' => ['id', 'nom'],
                'Adresse' => ['id', 'numero', 'rue', 'code_postal', 'commune'],
                'Contact' => ['id', 'mail'],
                'Juridique' => ['id', 'RSC'],
                'Activite' => ['id', 'nom'],
                'Tuteur' => ['id', 'nom', 'prenom'],
                'Stage' => ['id', 'date_debut']
            ];

            $columns = $displayColumns[$realTableName] ?? ['id'];

            // Construire la partie CONCAT avec l'ID en premier
            $displayColumn = "CONCAT('(ID #', id, ') ', " .
                (count($columns) > 1 ?
                    implode(", ' ', ", array_slice($columns, 1)) :
                    $columns[0]) .
                ")";

            // Récupérer les enregistrements existants
            $sql = sprintf("SELECT id, %s as display_value FROM `%s`", $displayColumn, $realTableName);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $existingData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter l'entrée pour le prochain ID
            $existingData[] = [
                'id' => $nextId,
                'display_value' => "(ID potentiel #$nextId)"
            ];

            return $existingData;
        } catch (\Exception $e) {
            error_log("Erreur dans getForeignKeyData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un champ est une clé étrangère
     * @param string $fieldName
     * @return bool
     */
    public function isForeignKey(string $fieldName): bool
    {
        return str_ends_with($fieldName, '_id');
    }

    /**
     * Obtient le prochain ID disponible pour une table
     * @param string $table
     * @return int
     */
    public function getNextId(string $table): int
    {
        $stmt = $this->pdo->prepare("SELECT MAX(id) + 1 as next_id FROM `$table`");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['next_id'] ?? 1;
    }

    /**
     * Met à jour un enregistrement
     * @param string $table
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateRecord(string $table, int $id, array $data): bool
    {
        $this->validateTable($table);

        $filteredData = array_filter($data, function ($key) {
            return !in_array($key, ['action', 'table', 'id', 'csrf_token']);
        }, ARRAY_FILTER_USE_KEY);

        $setClause = implode(', ', array_map(fn($col) => "`$col` = ?", array_keys($filteredData)));

        $sql = "UPDATE `$table` SET $setClause WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([...array_values($filteredData), $id]);
    }
}
