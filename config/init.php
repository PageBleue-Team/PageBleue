<?php

// Chargement des constantes depuis paths.php
require_once __DIR__ . '/Package/paths.php';
// Chargement des fichiers de configuration
require_once ROOT_PATH . '/vendor/autoload.php';
// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();
// Démarrage session
session_start();
// Chargement des autres configurations
require_once __DIR__ . '/Package/database.php';
require_once __DIR__ . '/Package/cache.php';
require_once __DIR__ . '/Package/security.php';
require_once __DIR__ . '/Package/site.php';
require_once __DIR__ . '/Package/utils.php';
require_once __DIR__ . '/Package/functions.php';
// Chargement du fichier de log
require_once ROOT_PATH . '/config/Package/logs.php';
