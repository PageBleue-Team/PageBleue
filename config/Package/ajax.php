<?php

require_once __DIR__ . '/../config/init.php';
use App\Controller\AdminController;

// Prevent caching of AJAX responses
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Verify CSRF token
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}

header('Content-Type: application/json');
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new InvalidArgumentException('Méthode non autorisée');
    }

    // Validate content type for POST requests with JSON
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $input = file_get_contents('php://input');
        if ($input === false) {
            throw new InvalidArgumentException('Impossible de lire les données d\'entrée');
        }
        $_POST = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON invalide');
        }
    }

    $adminController = new AdminController();
    $adminController->handleFormSubmission();
} catch (Exception $e) {
    http_response_code(500);
    $message = 'Une erreur est survenue';

    // Log the actual error for debugging
    error_log($e->getMessage());

    // Only show detailed errors in development environment or if debug is enabled
    if ($_ENV['APP_DEBUG'] === 'true' || $_ENV['APP_ENV'] === 'development') {
        $message = $e->getMessage();
    }

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
