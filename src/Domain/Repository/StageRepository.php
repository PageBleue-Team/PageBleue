<?php

namespace App\Domain\Repository;

use PDO;

class StageRepository
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les stages d'une entreprise
     * @param int $entrepriseId
     * @return array<int, array{
     *    id: int,
     *    entreprise_id: int,
     *    tuteur_id: ?int,
     *    tuteur_nom: ?string,
     *    tuteur_prenom: ?string,
     *    date_debut: string,
     *    date_fin: string
     * }>
     * @throws \InvalidArgumentException Si l'ID de l'entreprise est invalide
     * @throws \RuntimeException En cas d'erreur de base de données
     */
    public function getStagesByEntreprise(int $entrepriseId): array
    {
        if ($entrepriseId <= 0) {
            throw new \InvalidArgumentException(
                "L'ID de l'entreprise doit être un nombre positif"
            );
        }

        $query = "SELECT s.*, t.nom as tuteur_nom, t.prenom as tuteur_prenom
                 FROM Stage s
                 LEFT JOIN Tuteur t ON s.tuteur_id = t.id
                 WHERE s.entreprise_id = :entrepriseId";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':entrepriseId', $entrepriseId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new \RuntimeException(
                    "Échec de l'exécution de la requête de récupération des stages"
                );
            }

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result === false) {
                throw new \RuntimeException(
                    "Erreur lors de la récupération des données des stages"
                );
            }

            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "Erreur lors de la récupération des stages : " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
