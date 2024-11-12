<?php

namespace Config;

// Définir le chemin du fichier de log
define('LOG_FILE', ROOT_PATH . '/var/logs/app.log');

/**
 * Initialise la configuration des logs
 * @return void
 */
function initLogs(): void
{
    // Créer le dossier logs s'il n'existe pas
    if (!file_exists(dirname(LOG_FILE))) {
        if (!mkdir(dirname(LOG_FILE), 0755, true)) {
            throw new \RuntimeException('Impossible de créer le répertoire de logs');
        }
    }
}

/**
 * Écrit un message dans le fichier de log uniquement en environnement de développement
 * @param string $message Le message à logger
 * @return void
 */
function app_log(string $message): void
{
    if (getenv('APP_ENV') !== 'development') {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}
