<?php

namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;
use PDO;

use function Config\app_log;

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

    /**
     * Valide le nom d'une table
     * @param string $table
     * @throws Exception si le nom de la table est invalide
     */
    private function validateTable(string $table): void
    {
        // Liste des tables autorisées
        $allowedTables = [
            'Activite',
            'Adresse',
            'Contact',
            'Entreprises',
            'Entreprises_Activite',  // Ajout de la table de liaison
            'Juridique',
            'Stage',
            'Tuteur',
            'Users',
            'login_logs'
        ];

        if (!in_array($table, $allowedTables)) {
            throw new Exception("Table non autorisée: $table");
        }

        // Vérification supplémentaire avec la base de données
        $stmt = $this->pdo->prepare("
            SELECT 1 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);

        if (!$stmt->fetch()) {
            throw new Exception("Table inexistante: $table");
        }
    }

    /**
     * Ajoute un enregistrement dans une table
     * @param string $table
     * @param array<string, mixed> $data
     * @return bool
     */
    public function addRecord(string $table, array $data): bool
    {
        try {
            app_log("=== Début addRecord pour la table $table ===");
            app_log("Données reçues : " . print_r($data, true));

            $this->validateTable($table);

            // Validation spécifique pour la table Adresse
            if ($table === 'Adresse') {
                $this->validateAdresse($data);
            }

            // Vérification des clés étrangères pour la table Entreprises
            if ($table === 'Entreprises') {
                $foreignKeys = [
                    'adresse_id' => 'Adresse',
                    'contact_id' => 'Contact',
                    'juridique_id' => 'Juridique'
                ];

                foreach ($foreignKeys as $key => $refTable) {
                    if (isset($data[$key])) {
                        $exists = $this->checkForeignKeyExists($refTable, $data[$key]);
                        if (!$exists) {
                            app_log("Erreur: L'ID {$data[$key]} n'existe pas dans la table $refTable");
                            throw new \Exception("L'ID référencé dans $key n'existe pas dans la table $refTable");
                        }
                    }
                }
            }

            if (empty($data)) {
                app_log("Erreur: Aucune donnée à insérer");
                return false;
            }

            $columns = array_keys($data);
            $values = array_fill(0, count($data), '?');

            $sql = sprintf(
                "INSERT INTO `%s` (`%s`) VALUES (%s)",
                $table,
                implode('`, `', $columns),
                implode(', ', $values)
            );

            app_log("SQL préparé: $sql");
            app_log("Valeurs à insérer:");
            app_log(print_r(array_values($data), true));

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(array_values($data));

            if (!$result) {
                app_log("Erreur PDO: " . print_r($stmt->errorInfo(), true));
            }

            app_log("Résultat de l'insertion: " . ($result ? "succès" : "échec"));
            if ($result) {
                app_log("ID inséré: " . $this->pdo->lastInsertId());
            }
            app_log("=== Fin addRecord ===");

            return $result;
        } catch (Exception $e) {
            app_log("ERREUR dans addRecord: " . $e->getMessage());
            app_log("Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Valide les données d'une adresse
     * @param array<string, string|null> $data
     * @throws Exception si la validation échoue
     */
    private function validateAdresse(array $data): void
    {
        $hasNumero = !empty($data['numero']);
        $hasRue = !empty($data['rue']);
        $hasLieuDit = !empty($data['lieu_dit']);

        // Vérifier si on a soit (numéro + rue) soit lieu-dit
        $hasNumeroRue = $hasNumero && $hasRue;

        if (!$hasNumeroRue && !$hasLieuDit) {
            throw new Exception('Une adresse doit avoir soit un numéro et une rue, soit un lieu-dit');
        }

        if ($hasLieuDit && ($hasNumero || $hasRue)) {
            throw new Exception('Une adresse ne peut pas avoir à la fois un lieu-dit et un numéro/rue');
        }
    }

    /**
     * Vérifie l'existence d'un ID dans une table
     * @param string $table
     * @param int $id
     */
    private function checkForeignKeyExists(string $table, int $id): bool
    {
        try {
            $sql = "SELECT 1 FROM `$table` WHERE id = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetchColumn() !== false;
        } catch (\Exception $e) {
            app_log("Erreur lors de la vérification de la clé étrangère: " . $e->getMessage());
            return false;
        }
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
     * @param int $id
     * @throws Exception
     */
    public function deleteRecord(string $table, int $id): bool
    {
        try {
            app_log("=== Début deleteRecord pour la table $table ===");
            app_log("ID à supprimer : $id");

            $this->validateTable($table);

            // Vérifier si l'enregistrement existe
            $checkSql = "SELECT 1 FROM `$table` WHERE id = ?";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$id]);

            if ($checkStmt->fetchColumn() === false) {
                app_log("Enregistrement non trouvé");
                return false;
            }

            // Supprimer l'enregistrement
            $sql = "DELETE FROM `$table` WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id]);

            app_log($result ? "Suppression réussie" : "Échec de la suppression");
            return $result;
        } catch (Exception $e) {
            app_log("ERREUR dans deleteRecord : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère la structure d'une table
     * @param string $table
     * @return array<string, array<string, string|null>>
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

    /**
     * Récupère toutes les tables de la base de données pour l'affichage dans le dashboard
     * @return array<int, string>
     */
    public function getAllTables(): array
    {
        try {
            // Liste des tables à exclure du dashboard
            $excludedTables = [
                'login_logs',
                'Users',
                'Entreprises_Activite'  // On exclut la table de liaison de l'affichage
            ];

            // Récupérer le nom de la base de données depuis PDO
            $dbName = $this->pdo->query('SELECT DATABASE()')->fetchColumn();

            // Requête pour obtenir toutes les tables
            $stmt = $this->pdo->query("
                SELECT TABLE_NAME 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = '$dbName' 
                AND TABLE_NAME NOT IN ('" . implode("','", $excludedTables) . "')
                ORDER BY TABLE_NAME
            ");

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Erreur dans getAllTables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère tous les enregistrements d'une table
     * @param string $table
     * @return array<int, array<string, mixed>>
     */
    public function getAllRecords(string $table): array
    {
        try {
            $this->validateTable($table);

            $stmt = $this->pdo->prepare("SELECT * FROM `$table`");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getAllRecords pour la table $table: " . $e->getMessage());
            return [];
        }
    }
}
