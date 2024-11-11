<?php

namespace App\Domain\Repository;

use Exception;
use PDO;
use App\Domain\Entity\EntityRepository;

class EntrepriseRepository extends EntityRepository
{
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Récupère les entreprises mises en avant
     * @param int $limit Nombre d'entreprises à retourner
     * @return array<int, array<string, mixed>>
     */
    public function getFeaturedEntreprises(int $limit = 20): array
    {
        $sql = "SELECT e.*,
                    a.numero, a.rue, a.code_postal, a.commune,
                    c.mail, c.telephone
                FROM Entreprises e
                LEFT JOIN Adresse a ON e.adresse_id = a.id
                LEFT JOIN Contact c ON e.contact_id = c.id
                WHERE e.checked = true
                ORDER BY RAND()
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle entreprise avec ses relations
     * @param array<string, mixed> $data
     * @return int
     * @throws Exception
     */
    public function createEntreprise(array $data): int
    {
        try {
            $this->pdo->beginTransaction();
// Création de l'adresse
            $adresseId = $this->create('Adresse', [
                'numero' => $data['numero'],
                'rue' => $data['rue'],
                'code_postal' => $data['code_postal'],
                'commune' => $data['commune'],
                'lieu_dit' => $data['lieu_dit'] ?? null,
                'complement' => $data['complement'] ?? null
            ]);
// Création du contact
            $contactId = $this->create('Contact', [
                'mail' => $data['mail'],
                'telephone' => $data['telephone'],
                'site_web' => $data['site_web'] ?? null
            ]);
// Création des informations juridiques
            $juridiqueId = $this->create('Juridique', [
                'SIREN' => $data['SIREN'],
                'SIRET' => $data['SIRET'],
                'creation' => $data['creation'],
                'employés' => $data['employés']
            ]);
// Création de l'entreprise
            $entrepriseId = $this->create('Entreprises', [
                'nom' => $data['nom'],
                'adresse_id' => $adresseId,
                'contact_id' => $contactId,
                'juridique_id' => $juridiqueId,
                'lasallien' => $data['lasallien'] ?? false,
                'checked' => $data['checked'] ?? false,
                'logo' => $data['logo'] ?? null
            ]);
            $this->pdo->commit();
            return $entrepriseId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Met à jour une entreprise et ses relations
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     * @throws Exception
     */
    public function updateEntreprise(int $id, array $data): bool
    {
        try {
            $this->pdo->beginTransaction();
            $entreprise = $this->getEntrepriseWithRelations($id);
            if (!$entreprise) {
                throw new Exception("Entreprise non trouvée");
            }

            // Mise à jour de l'adresse
            $this->update('Adresse', $entreprise['adresse_id'], [
                'numero' => $data['numero'],
                'rue' => $data['rue'],
                'code_postal' => $data['code_postal'],
                'commune' => $data['commune'],
                'lieu_dit' => $data['lieu_dit'] ?? null,
                'complement' => $data['complement'] ?? null
            ]);
// Mise à jour du contact
            $this->update('Contact', $entreprise['contact_id'], [
                'mail' => $data['mail'],
                'telephone' => $data['telephone'],
                'site_web' => $data['site_web'] ?? null
            ]);
// Mise à jour des informations juridiques
            $this->update('Juridique', $entreprise['juridique_id'], [
                'SIREN' => $data['SIREN'],
                'SIRET' => $data['SIRET'],
                'creation' => $data['creation'],
                'employés' => $data['employés']
            ]);
// Mise à jour de l'entreprise
            $this->update('Entreprises', $id, [
                'nom' => $data['nom'],
                'lasallien' => $data['lasallien'] ?? false,
                'checked' => $data['checked'] ?? false,
                'logo' => $data['logo'] ?? $entreprise['logo']
            ]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Récupère une entreprise avec toutes ses relations
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function getEntrepriseWithRelations(int $id): ?array
    {
        $sql = "SELECT e.*,
                       a.numero, a.rue, a.code_postal, a.commune, a.lieu_dit, a.complement,
                       c.mail, c.telephone, c.site_web,
                       j.SIREN, j.SIRET, j.RSC, j.activite, j.activite_main, j.creation, j.employés
                FROM Entreprises e
                LEFT JOIN Adresse a ON e.adresse_id = a.id
                LEFT JOIN Contact c ON e.contact_id = c.id
                LEFT JOIN Juridique j ON e.juridique_id = j.id
                WHERE e.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Supprime une entreprise et ses relations
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteEntreprise(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();
            $entreprise = $this->getEntrepriseWithRelations($id);
            if (!$entreprise) {
                throw new Exception("Entreprise non trouvée");
            }

            // Suppression dans l'ordre inverse des dépendances
            $this->delete('Entreprises', $id);
            $this->delete('Adresse', $entreprise['adresse_id']);
            $this->delete('Contact', $entreprise['contact_id']);
            $this->delete('Juridique', $entreprise['juridique_id']);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Liste toutes les entreprises avec pagination
     * @param int $page
     * @param int $perPage
     * @param array<string, mixed> $filters
     * @return array{
     *     data: array<int, array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     perPage: int,
     *     lastPage: int
     * }
     */
    public function listEntreprises(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $whereClauses = [];
        $params = [];
        if (!empty($filters['search'])) {
            $whereClauses[] = "e.nom LIKE :search";
            $params['search'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['search']) . '%';
        }

        if (isset($filters['lasallien'])) {
            $whereClauses[] = "e.lasallien = :lasallien";
            $params['lasallien'] = $filters['lasallien'];
        }

        $whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
        $sql = "SELECT e.*,
                       a.numero, a.rue, a.code_postal, a.commune,
                       c.mail, c.telephone
                FROM Entreprises e
                LEFT JOIN Adresse a ON e.adresse_id = a.id
                LEFT JOIN Contact c ON e.contact_id = c.id
                $whereSQL
                ORDER BY e.nom ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
// Bind tous les paramètres
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Compte total pour la pagination
        $countSql = "SELECT COUNT(*) FROM Entreprises e $whereSQL";
        $stmtCount = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int)ceil($total / $perPage)
        ];
    }

    /**
     * Vérifie si un SIRET existe déj
     * @param string $siret
     * @param int|null $excludeId
     * @return bool
     */
    public function siretExists(string $siret, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM Juridique j
                JOIN Entreprises e ON e.juridique_id = j.id
                WHERE j.SIRET = :siret";
        $params = ['siret' => $siret];
        if ($excludeId) {
            $sql .= " AND e.id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }
}
