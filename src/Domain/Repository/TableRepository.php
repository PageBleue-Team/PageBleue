<?php

namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;

class TableRepository extends EntityRepository
{
    /** @var array<int, string> */
    private array $blacklistedTables;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
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
}
