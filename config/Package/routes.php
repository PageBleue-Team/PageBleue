<?php
use App\Router\Router;

$router = new Router();

// Routes publiques
$router->add('', 'SecurityController', 'home');
$router->add('login', 'SecurityController', 'login', ['methods' => ['GET', 'POST']]);

// Routes protégées
$router->add('admin', 'AdminController', 'dashboard', [
    'auth' => true,
    'methods' => ['GET']
]);

$router->add('admin/entreprises', 'AdminController', 'manageEnterprises', [
    'auth' => true,
    'methods' => ['GET', 'POST']
]);

return $router;
