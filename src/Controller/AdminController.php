<?php

namespace App\Controller;

use App\Domain\Repository\TableRepository;
use App\Services\ImageService;

class AdminController
{
    private SecurityController $securityController;
    private TableRepository $tableRepository;
    private ImageService $imageService;

    public function __construct(
        SecurityController $securityController,
        TableRepository $tableRepository
    ) {
        $this->securityController = $securityController;
        $this->tableRepository = $tableRepository;
        $this->imageService = new ImageService();
    }

    public function dashboard(): void
    {
        if (!$this->securityController->isAdminLoggedIn()) {
            header('Location: login');
            exit();
        }

        // Récupération des tables et données via le repository
        $tables = $this->tableRepository->getTables();
        $tableData = $this->getTableDataForTables($tables);

        // Traitement des actions POST et AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
        } elseif ($this->isAjaxRequest()) {
            $this->handleAjaxRequest();
        }

        // Inclusion de la vue
        require_once ROOT_PATH . '/templates/admin/dashboard.php';
    }

    /**
     * Récupère les données pour toutes les tables
     * @param array<int, string> $tables
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function getTableDataForTables(array $tables): array
    {
        $tableData = [];
        foreach ($tables as $table) {
            $tableData[$table] = $this->tableRepository->getTableData($table);
        }
        return $tableData;
    }

    private function handlePostAction(): void
    {
        try {
            if (!$this->securityController->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception("Erreur de validation du formulaire");
            }

            $action = filter_input(INPUT_POST, 'action');
            $table = filter_input(INPUT_POST, 'table');

            switch ($action) {
                case 'add':
                    $id = $this->tableRepository->addRecord($table, $_POST);
                    if ($table === 'Entreprises' && isset($_FILES['logo'])) {
                        $this->imageService->handleLogoUpload($_FILES['logo'], $id);
                    }
                    break;

                case 'edit':
                    $this->tableRepository->editRecord($table, $_POST);
                    if ($table === 'Entreprises' && isset($_FILES['logo'])) {
                        $this->imageService->handleLogoUpload($_FILES['logo'], (int)$_POST['id']);
                    }
                    break;

                case 'delete':
                    if ($table === 'Entreprises') {
                        $this->imageService->deleteLogo((int)$_POST['id']);
                    }
                    $this->tableRepository->deleteRecord($table, $_POST['id']);
                    break;

                default:
                    throw new \Exception("Action non valide");
            }

            $_SESSION['success_message'] = "Opération effectuée avec succès";
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'opération";
        }

        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    }

    private function handleAjaxRequest(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        $action = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';

        $data = $this->tableRepository->getTableData($table);
        echo json_encode($data);
        exit;
    }

    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
