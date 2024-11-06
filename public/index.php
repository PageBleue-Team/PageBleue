<?php
/**
 * Point d'entrée principal de l'application
 */

// Définitions de chemins
require_once __DIR__ . '../../config/Package/paths.php';

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

// Routes de l'application
$routes = [
    '' => [
        'controller' => 'SecurityController',
        'action' => 'home'
    ],
    'login' => [
        'controller' => 'SecurityController',
        'action' => 'login'
    ],
    'admin' => [
        'controller' => 'AdminController',
        'action' => 'dashboard',
        'auth' => true
    ]
];

// Récupération de l'URL demandée
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

try {
    // Vérification de l'existence de la route
    if (!isset($routes[$uri])) {
        throw new Exception('Page non trouvée', 404);
    }

    $route = $routes[$uri];
    
    // Vérification de l'authentification
    if (!empty($route['auth']) && empty($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }

    // Construction du nom complet de la classe du contrôleur
    $controllerName = "App\\Controller\\" . $route['controller'];
    
    if (!class_exists($controllerName)) {
        throw new Exception("Contrôleur non trouvé", 500);
    }

    // Instanciation du contrôleur et appel de l'action
    $controller = new $controllerName();
    $action = $route['action'];
    
    if (!method_exists($controller, $action)) {
        throw new Exception("Action non trouvée", 500);
    }

    $controller->$action();

} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    header("HTTP/1.0 $statusCode");

    // Log de l'erreur
    error_log(sprintf(
        "[%s] %s in %s:%d",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    // Affichage de la page d'erreur
    $errorFile = ROOT_PATH . "/templates/pages/{$statusCode}.php";
    include file_exists($errorFile) ? $errorFile : ROOT_PATH . '/templates/pages/500.php';
}