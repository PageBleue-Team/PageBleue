<?php

/**
 * Point d'entrée principal de l'application
 */

// Chargement des constantes depuis paths.php
require_once __DIR__ . '/../config/Package/paths.php';
// Gestion des erreurs
$debug = getenv('APP_ENV') === 'development';
if ($debug) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Chargement des dépendances
require_once ROOT_PATH . '/vendor/autoload.php';
// Chargement des variables d'environnement
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (Exception $e) {
    die('Erreur : fichier .env manquant');
}

// Chargement de la configuration
require_once ROOT_PATH . '/config/init.php';

use App\Controller\AdminController;
use App\Controller\SecurityController;

// Récupération de l'URI actuelle
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = (string)parse_url($requestUri, PHP_URL_PATH);

// Initialiser le contrôleur de sécurité
$securityController = new SecurityController();

// Vérifier si c'est une requête AJAX
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
          (!empty($_SERVER['HTTP_ACCEPT']) &&
          strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Route pour les requêtes AJAX du panel admin
if ($uri === '/panel/ajax' && $isAjax) {
    if (!$securityController->isAdminLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Non autorisé'
        ]);
        exit;
    }

    header('Content-Type: application/json');
    try {
        $adminController = new AdminController();
        $adminController->handleFormSubmission();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Gestion des routes
if ($uri === '/') {
    require TEMPLATES_DIR . '/pages/home.php';
} elseif ($uri === '/panel') {
    if (!$securityController->isAdminLoggedIn()) {
        header('Location: /login');
        exit;
    }
    require TEMPLATES_DIR . '/admin/dashboard.php';
} elseif ($uri === '/legal') {
    require TEMPLATES_DIR . '/pages/legal.php';
} elseif ($uri === '/list') {
    require TEMPLATES_DIR . '/pages/list.php';
} elseif (preg_match('#^/list/(\d+)$#', $uri, $matches)) {
    $showEnterprise = (int)$matches[1];
    require TEMPLATES_DIR . '/pages/list.php';
} elseif ($uri === '/login') {
    require TEMPLATES_DIR . '/pages/login.php';
} else {
    http_response_code(404);
    require TEMPLATES_DIR . '/pages/error/404.php';
}
