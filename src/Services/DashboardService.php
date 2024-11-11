<?php

namespace App\Services;

use App\Domain\Repository\EntrepriseRepository;
use App\Domain\Repository\TableRepository;
use PDO;

class DashboardService
{
    private TableRepository $tableRepository;
    private EntrepriseRepository $entrepriseRepository;
    private ImageService $imageService;

    public function __construct(
        TableRepository $tableRepository,
        EntrepriseRepository $entrepriseRepository,
        ImageService $imageService
    ) {
        $this->tableRepository = $tableRepository;
        $this->entrepriseRepository = $entrepriseRepository;
        $this->imageService = $imageService;
    }

    /**
     * Gère les opérations CRUD pour toutes les tables
     */
    public function handleTableOperation(string $action, string $table, array $postData, ?array $files = null): bool
    {
        try {
            switch ($action) {
                case 'add':
                    $data = $this->prepareData($table, $postData, $files);
                    return $this->tableRepository->addRecord($table, $data) !== null;

                case 'edit':
                    $id = (int)($postData['id'] ?? 0);
                    if ($id <= 0) {
                        throw new \Exception("ID invalide");
                    }
                    $data = $this->prepareData($table, $postData, $files);
                    return $this->tableRepository->updateRecord($table, $id, $data);

                case 'delete':
                    $id = (int)($postData['id'] ?? 0);
                    if ($id <= 0) {
                        throw new \Exception("ID invalide");
                    }
                    return $this->tableRepository->deleteRecord($table, $id);

                default:
                    throw new \Exception("Action non reconnue");
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function prepareData(string $table, array $postData, ?array $files = null): array
    {
        $data = array_filter($postData, function ($key) {
            return !in_array($key, ['action', 'table', 'id', 'csrf_token']);
        }, ARRAY_FILTER_USE_KEY);

        // Gestion des fichiers uploadés
        if ($files && isset($files['logo']) && $files['logo']['error'] === UPLOAD_ERR_OK) {
            $imageData = $this->imageService->processUploadedImage($files['logo']);
            if ($imageData !== null) {
                $data['logo'] = $imageData;
            }
        }

        return $data;
    }

    /**
     * Récupère les données pour l'affichage du dashboard
     */
    public function getDashboardData(): array
    {
        $tables = $this->tableRepository->getTables();
        $tableData = [];

        foreach ($tables as $table) {
            $tableData[$table] = $this->tableRepository->getTableData($table);
        }

        return [
            'tables' => $tables,
            'tableData' => $tableData
        ];
    }
}
