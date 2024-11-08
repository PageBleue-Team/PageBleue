<?php
namespace App\Domain\Repository;
use PDO;

class StageRepository {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getStagesByEntreprise(int $entrepriseId): array 
    {
        $stmt = $this->pdo->prepare("SELECT s.*, t.nom as tuteur_nom, t.prenom as tuteur_prenom 
                                    FROM Stage s 
                                    LEFT JOIN Tuteur t ON s.tuteur_id = t.id
                                    WHERE s.entreprise_id = ?");
        $stmt->execute([$entrepriseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 