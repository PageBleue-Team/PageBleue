<?php

namespace App\Controller;

use App\Domain\Repository\TableRepository;
use App\Services\ImageService;
use Exception;

use function Config\app_log;

class AdminController
{
    private TableRepository $tableRepository;
    private ImageService $imageService;

    public function __construct()
    {
        $pdo = \Config\Database::getInstance()->getConnection();
        $this->tableRepository = new TableRepository($pdo);
        $this->imageService = new ImageService();
    }

    /**
     * Gère la soumission des formulaires
     */
    public function handleFormSubmission(): void
    {
        try {
            app_log("=== Début handleFormSubmission ===");

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            if (!isset($_POST['action'], $_POST['table'])) {
                throw new Exception('Paramètres manquants');
            }

            $action = $_POST['action'];
            $table = $_POST['table'];

            app_log("Action: $action, Table: $table");
            app_log("POST reçu: " . print_r($_POST, true));

            switch ($action) {
                case 'add':
                    $filteredData = array_filter($_POST, function ($key) {
                        return !in_array($key, ['action', 'table', 'csrf_token']);
                    }, ARRAY_FILTER_USE_KEY);

                    // Traitement du logo si présent
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                        $logoData = $this->imageService->processUploadedImage($_FILES['logo']);
                        if ($logoData !== null) {
                            $filteredData['logo'] = $logoData;
                            app_log("Logo traité avec succès");
                        } else {
                            app_log("Échec du traitement du logo");
                        }
                    }

                    try {
                        $result = $this->tableRepository->addRecord($table, $filteredData);
                        if ($result) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Enregistrement ajouté avec succès'
                            ]);
                        } else {
                            throw new Exception("Erreur lors de l'ajout de l'enregistrement");
                        }
                    } catch (Exception $e) {
                        throw new Exception("Erreur de validation : " . $e->getMessage());
                    }
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception("ID manquant");
                    }

                    $id = (int)$_POST['id'];
                    $result = $this->tableRepository->deleteRecord($table, $id);

                    if ($result) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Enregistrement supprimé avec succès'
                        ]);
                    } else {
                        throw new Exception("Erreur lors de la suppression de l'enregistrement");
                    }
                    break;

                default:
                    throw new Exception('Action non reconnue');
            }
        } catch (Exception $e) {
            app_log("ERREUR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère les données pour le tableau de bord
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $tables = $this->tableRepository->getAllTables();
        $tableData = [];

        foreach ($tables as $table) {
            $tableData[$table] = $this->tableRepository->getAllRecords($table);
        }

        return [
            'tables' => $tables,
            'tableData' => $tableData
        ];
    }
}
