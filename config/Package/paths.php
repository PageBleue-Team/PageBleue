<?php

// Définition des constantes avec vérification
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

if (!defined('TEMPLATES_DIR')) {
    define('TEMPLATES_DIR', ROOT_PATH . '/templates');
}

if (!defined('CONFIG_DIR')) {
    define('CONFIG_DIR', ROOT_PATH . '/config');
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

if (!defined('LOGO_PATH')) {
    define('LOGO_PATH', PUBLIC_PATH . '/assets/images/logos');
}

// Définition des autres chemins
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// Définition des URLs (chemins web)
define('ASSETS_URL', '/assets');
define('JS_URL', ASSETS_URL . '/js');
define('CSS_URL', ASSETS_URL . '/css');
define('IMAGES_URL', ASSETS_URL . '/images');
define('LOGOS_URL', IMAGES_URL . '/logos');
