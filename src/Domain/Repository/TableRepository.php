<?php
namespace App\Domain\Repository;

use Exception;
use App\Domain\Entity\EntityRepository;

class TableRepository extends EntityRepository {
    private array $blacklistedTables = ['login_logs', 'users', 'Entreprises_Activite'];

    public function getTables(): array {
        $stmt = $this->pdo->query("SHOW TABLES");
        $allTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return array_diff($allTables, $this->blacklistedTables);
    }

    public function getTableData(string $table, int $page = 1, int $perPage = 20): array {
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

    private function validateTable(string $table): void {
        if (in_array($table, $this->blacklistedTables)) {
            throw new Exception("Table non autorisée");
        }
    }
}
?>