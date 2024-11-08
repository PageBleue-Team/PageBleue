<?php
namespace App\Domain\Repository;
use PDO;

class TuteurRepository {
    /** @var PDO */
    private PDO $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les tuteurs d'une entreprise
     * @param int $entrepriseId
     * @return array<int, array<string, mixed>>
     */
    public function getTuteursByEntreprise(int $entrepriseId): array 
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Tuteur WHERE entreprise_id = ?");
        $stmt->execute([$entrepriseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 