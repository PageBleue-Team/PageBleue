<?php
namespace App\Controller;

use Config\Database;
use App\Domain\Repository\EntrepriseRepository;
use App\Domain\Repository\StageRepository;
use App\Domain\Repository\TuteurRepository;

class EntrepriseController {
    private $entrepriseRepo;
    private $stageRepo;
    private $tuteurRepo;
    
    public function __construct() 
    {
        $pdo = Database::getInstance()->getConnection();
        $this->entrepriseRepo = new EntrepriseRepository($pdo);
        $this->stageRepo = new StageRepository($pdo);
        $this->tuteurRepo = new TuteurRepository($pdo);
    }
    
    public function listAction(int $page = 1, array $filters = []) 
    {
        // Logique de liste
        $result = $this->entrepriseRepo->listEntreprises($page, 10, $filters);
        return ['enterprises' => $result['data'], 'total_pages' => $result['lastPage']];
    }
    
    public function showAction(int $id) 
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