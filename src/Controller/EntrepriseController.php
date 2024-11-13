<?php

namespace App\Controller;

use App\Domain\Repository\EntrepriseRepository;
use App\Domain\Repository\StageRepository;
use App\Domain\Repository\TuteurRepository;

class EntrepriseController
{
    /** @var EntrepriseRepository */
    private EntrepriseRepository $entrepriseRepo;
    /** @var StageRepository */
    private StageRepository $stageRepo;
    /** @var TuteurRepository */
    private TuteurRepository $tuteurRepo;
    public function __construct(
        EntrepriseRepository $entrepriseRepo,
        StageRepository $stageRepo,
        TuteurRepository $tuteurRepo
    ) {
        $this->entrepriseRepo = $entrepriseRepo;
        $this->stageRepo = $stageRepo;
        $this->tuteurRepo = $tuteurRepo;
    }

    /**
     * @param int $page
     * @param array<string, mixed> $filters
     * @return array{
     *     enterprises: array<int, array<string, mixed>>,
     *     total_pages: int
     * }
     */
    public function listAction(int $page = 1, array $filters = []): array
    {
        // Logique de liste
        $result = $this->entrepriseRepo->listEntreprises($page, 10, $filters);
        return ['enterprises' => $result['data'], 'total_pages' => $result['lastPage']];
    }

    /**
     * @param int $id
     * @return array{
     *     enterprise: array<string, mixed>,
     *     stages: array<int, array<string, mixed>>,
     *     tuteurs: array<int, array<string, mixed>>
     * }
     */
    public function showAction(int $id): array
    {
        $enterprise = $this->entrepriseRepo->getEntrepriseWithRelations($id);
        if (!$enterprise) {
            header('Location: /list');
            exit;
        }

        $stages = $this->stageRepo->getStagesByEntreprise($id);
        $tuteurs = $this->tuteurRepo->getTuteursByEntreprise($id);
        return [
            'enterprise' => $enterprise,
            'stages' => $stages,
            'tuteurs' => $tuteurs
        ];
    }
}
