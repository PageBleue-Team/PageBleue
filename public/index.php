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
// Récupération de l'URI actuelle
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = (string)parse_url($requestUri, PHP_URL_PATH);
// Gestion des routes
if ($uri === '/list') {
    require TEMPLATES_DIR . '/pages/list.php';
} elseif (preg_match('#^/list/(\d+)$#', $uri, $matches)) {
    $showEnterprise = (int)$matches[1];
    require TEMPLATES_DIR . '/pages/list.php';
} elseif ($uri === '/' || $uri === '') {
    require TEMPLATES_DIR . '/pages/home.php';
} else {
    $file = TEMPLATES_DIR . '/pages' . $uri . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        header("HTTP/1.0 404 Not Found");
        require TEMPLATES_DIR . '/pages/error/404.php';
    }
}
