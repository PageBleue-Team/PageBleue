<?php

if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use App\Controller\EntrepriseController;
use App\Domain\Repository\EntrepriseRepository;
use App\Domain\Repository\StageRepository;
use App\Domain\Repository\TuteurRepository;
use Config\Database;
use Config\Utils;
use App\Services\ImageService;

$pdo = Database::getInstance()->getConnection();
$entrepriseRepo = new EntrepriseRepository($pdo);
$stageRepo = new StageRepository($pdo);
$tuteurRepo = new TuteurRepository($pdo);
$controller = new EntrepriseController($entrepriseRepo, $stageRepo, $tuteurRepo);
$Utils = new Utils();
$imageService = new ImageService();

// Si un ID est présent dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $data = $controller->showAction((int)$_GET['id']);
        if (!empty($data['enterprise'])) {
            extract($data);
            require __DIR__ . '/list/detail-entreprise.php';
            exit;
        } else {
            header('Location: /list');
            exit;
        }
    } catch (Exception $e) {
        header('Location: /list');
        exit;
    }
}

// Si pas d'ID, afficher la liste
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$filters = [
    'search' => $_GET['search'] ?? null,
    'lasallien' => isset($_GET['lasallien']) ? (bool)$_GET['lasallien'] : null
];
$data = $controller->listAction($page, $filters);
extract($data);
// Extrait enterprises, total_pages
require __DIR__ . '/list/liste-entreprises.php';
