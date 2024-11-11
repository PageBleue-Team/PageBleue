<?php

namespace App\Controller;

use App\Services\DashboardService;
use App\Services\ImageService;
use App\Domain\Repository\TableRepository;
use App\Domain\Repository\EntrepriseRepository;
use Config\Database;

class AdminController
{
    private SecurityController $securityController;
    private DashboardService $dashboardService;

    public function __construct()
    {
        $pdo = Database::getInstance()->getConnection();

        // Initialisation des repositories
        $tableRepository = new TableRepository($pdo);
        $entrepriseRepository = new EntrepriseRepository($pdo);
        $imageService = new ImageService();

        // Initialisation des services
        $this->securityController = new SecurityController();
        $this->dashboardService = new DashboardService(
            $tableRepository,
            $entrepriseRepository,
            $imageService
        );
    }

    public function getDashboardData(): array
    {
        return $this->dashboardService->getDashboardData();
    }

    public function dashboard(): void
    {
        if (!$this->securityController->isAdminLoggedIn()) {
            header('Location: login');
            exit();
        }

        // Traitement des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
        }

        // Récupération des données
        $dashboardData = $this->dashboardService->getDashboardData();

        // S'assurer que les variables sont disponibles pour le template
        $tables = $dashboardData['tables'];
        $tableData = $dashboardData['tableData'];

        require_once ROOT_PATH . '/templates/admin/dashboard.php';
    }

    private function handlePostAction(): void
    {
        try {
            if (!$this->securityController->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception("Erreur de validation du formulaire");
            }

            $action = filter_input(INPUT_POST, 'action');
            $table = filter_input(INPUT_POST, 'table');

            $success = $this->dashboardService->handleTableOperation(
                $action,
                $table,
                $_POST,
                $_FILES ?? null
            );

            $_SESSION['success_message'] = $success
                ? "Opération effectuée avec succès"
                : "Une erreur est survenue lors de l'opération";
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'opération";
        }

        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    }
}
