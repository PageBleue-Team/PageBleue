<?php

// Définition des chemins absolus (système de fichiers)
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('TEMPLATES_DIR', ROOT_PATH . '/templates');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('LOGO_PATH', ASSETS_PATH . '/images/logos');
define('VAR_PATH', ROOT_PATH . '/var');
define('CACHE_PATH', VAR_PATH . '/cache');
define('LOGS_PATH', VAR_PATH . '/logs');

// Définition des URLs (chemins web)
define('ASSETS_URL', '/assets');
define('JS_URL', ASSETS_URL . '/js');
define('CSS_URL', ASSETS_URL . '/css');
define('IMAGES_URL', ASSETS_URL . '/images');
define('LOGOS_URL', IMAGES_URL . '/logos');