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

// Initialisation du routeur
$router = require ROOT_PATH . '/config/Package/routes.php';

// Récupération de la requête
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Recherche de la route
    $route = $router->match($uri, $method);

    if (!$route) {
        throw new Exception('Page non trouvée', 404);
    }

    // Vérification authentification
    if ($route['options']['auth'] && empty($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }

    // Instanciation du contrôleur
    $controllerName = "App\\Controller\\" . $route['controller'];
    if (!class_exists($controllerName)) {
        throw new Exception("Contrôleur non trouvé", 500);
    }

    $controller = new $controllerName();
    $action = $route['action'];

    if (!method_exists($controller, $action)) {
        throw new Exception("Action non trouvée", 500);
    }

    // Exécution de l'action avec les paramètres
    $controller->$action($route['params'] ?? []);

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