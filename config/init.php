<?php
// Chargement des fichiers de configuration dans le bon ordre
require_once __DIR__ . '/package/paths.php';
require_once ROOT_PATH . '/vendor/autoload.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Démarrage session
session_start();

// Chargement des autres configurations
require_once __DIR__ . '/package/database.php';
require_once __DIR__ . '/package/cache.php';
require_once __DIR__ . '/package/security.php';
require_once __DIR__ . '/package/site.php';
require_once __DIR__ . '/package/utils.php';