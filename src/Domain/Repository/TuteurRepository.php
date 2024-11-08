<?php
namespace App\Domain\Repository;
use PDO;

class TuteurRepository {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getTuteursByEntreprise(int $entrepriseId): array 
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Tuteur WHERE entreprise_id = ?");
        $stmt->execute([$entrepriseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 