<?php

// Définir le chemin du fichier de log
define('LOG_FILE', ROOT_PATH . '/var/logs/app.log');

// Créer le dossier logs s'il n'existe pas
if (!file_exists(dirname(LOG_FILE))) {
    mkdir(dirname(LOG_FILE), 0777, true);
}

// Fonction de log personnalisée
function app_log($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}
