<?php
/**
 * Point d'entrée principal de l'application
 *
 * Ce fichier initialise l'environnement de base et gère le routage
 */

// Définir l'environnement
define('ENV', getenv('APP_ENV') ?: 'production');

// Activer l'affichage des erreurs en développement
if (ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Charger l'autoloader de Composer
require_once __DIR__ . '../../vendor/autoload.php';

// Charger les variables d'environnement
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    die('Erreur de configuration : fichier .env manquant');
}

// Initialiser la configuration
require_once __DIR__ . '/../config/init.php';

// Vérifier que $pdo est bien initialisé dans init.php
// if (!isset($pdo)) {
//     die('Erreur : La connexion PDO n\'est pas initialisée');
// }

// Initialize repositories
use App\Repository\EntrepriseRepository;
use App\Controller\PageController;
use App\Controller\SecurityController;
use App\Controller\AdminController;

try {
    $entrepriseRepository = new EntrepriseRepository($pdo);
} catch (Exception $e) {
    die('Erreur : Impossible d\'initialiser EntrepriseRepository - ' . $e->getMessage());
}

// Initialize controllers
try {
    $pageController = new PageController($entrepriseRepository, $siteConfig);
} catch (Exception $e) {
    die('Erreur : Impossible d\'initialiser PageController - ' . $e->getMessage());
}

// Récupérer l'URI demandée et nettoyer le chemin
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$path = trim(str_replace($basePath, '', $requestUri), '/');

// Router simple avec gestion des routes
try {
    // Définition des routes et de leurs contrôleurs
    $routes = [
        '' => ['controller' => SecurityController::class, 'action' => 'home'],
        'login' => ['controller' => SecurityController::class, 'action' => 'login'],
        'logout' => ['controller' => SecurityController::class, 'action' => 'logout'],
        'admin' => [
            'controller' => AdminController::class,
            'action' => 'dashboard',
            'auth' => true
        ]
    ];

    // Vérifier si la route existe
    if (!isset($routes[$path])) {
        throw new \Exception('Page non trouvée', 404);
    }

    $route = $routes[$path];
    
    // Vérifier l'authentification si nécessaire
    if (isset($route['auth']) && $route['auth'] && !isset($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }

    // Instancier le contrôleur et appeler l'action
    $controllerClass = $route['controller'];
    
    if (!class_exists($controllerClass)) {
        throw new \Exception("Contrôleur '$controllerClass' non trouvé", 500);
    }

    // Créer une nouvelle instance du contrôleur avec les dépendances nécessaires
    $controller = match ($controllerClass) {
        SecurityController::class => new SecurityController($entrepriseRepository, $pdo, $siteConfig),
        AdminController::class => new AdminController($entrepriseRepository, $pdo, $siteConfig),
        default => throw new \Exception("Configuration du contrôleur '$controllerClass' manquante", 500)
    };

    $action = $route['action'];
    if (!method_exists($controller, $action)) {
        throw new \Exception("Action '$action' non trouvée dans '$controllerClass'", 500);
    }

    // Exécuter l'action du contrôleur
    $controller->$action();

} catch (\Exception $e) {    // Gérer les différents types d'erreurs
    $statusCode = $e->getCode() ?: 500;
    header("HTTP/1.0 $statusCode");
    
    // Logger l'erreur
    error_log(sprintf(
        "[%s] %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    // Afficher la page d'erreur appropriée
    $errorPage = ROOT_PATH . "/templates/pages/{$statusCode}.php";
    if (file_exists($errorPage)) {
        include $errorPage;
    } else {
        include ROOT_PATH . '/templates/pages/500.php';
    }
}