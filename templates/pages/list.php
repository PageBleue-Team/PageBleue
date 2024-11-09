<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use App\Controller\EntrepriseController;
use Config\Utils;

$controller = new EntrepriseController();
$Utils = new Utils();

// Si un ID d'entreprise est fourni via $showEnterprise (défini dans index.php)
if (isset($showEnterprise)) {
    $data = $controller->showAction($showEnterprise);
    extract($data); // Extrait enterprise, stages, tuteurs
    require __DIR__ . '/list/detail-entreprise.php';
} else {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $filters = [
        'search' => $_GET['search'] ?? null,
        'lasallien' => isset($_GET['lasallien']) ? (bool)$_GET['lasallien'] : null
    ];
    $data = $controller->listAction($page, $filters);
    extract($data); // Extrait enterprises, total_pages
    require __DIR__ . '/list/liste-entreprises.php';
}
