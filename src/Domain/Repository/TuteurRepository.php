<?php

namespace App\Domain\Repository;

use PDO;
use PDOException;

class TuteurRepository
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les tuteurs d'une entreprise
     * @param int $entrepriseId
     * @return array<int, array<string, mixed>>
     * @throws PDOException En cas d'erreur de base de données
     */
    public function getTuteursByEntreprise(int $entrepriseId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, mail, telephone 
                FROM Tuteur 
                WHERE entreprise_id = ?
            ");
            $stmt->execute([$entrepriseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException(
                "Erreur lors de la récupération des tuteurs : " . $e->getMessage()
            );
        }
    }
}
