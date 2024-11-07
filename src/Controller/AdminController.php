<?php
namespace App\Controller;

use App\Repository\TableRepository;
use App\Repository\EntrepriseRepository;

class AdminController 
{
    private SecurityController $authService;
    private TableRepository $tableRepository;

    public function __construct(
        SecurityController $authService,
        TableRepository $tableRepository,
    ) {
        $this->authService = $authService;
        $this->tableRepository = $tableRepository;
    }

    /**
     * Page d'accueil du tableau de bord admin
     */
    public function dashboard(): void 
    {
        // Vérification de l'authentification
        if (!$this->authService->isAdminLoggedIn()) {
            header('Location: login');
            exit();
        }

        // Récupération des données de toutes les tables
        $tables = $this->tableRepository->getTables();
        $tableData = [];
        
        foreach ($tables as $table) {
            $tableData[$table] = $this->tableRepository->getTableData($table);
        }

        // Inclusion de la vue
        require_once ROOT_PATH . '/templates/admin/dashboard.php';
    }
}