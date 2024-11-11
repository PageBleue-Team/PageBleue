<?php

require_once __DIR__ . '/../config/init.php';
use App\Controller\AdminController;
header('Content-Type: application/json');
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new InvalidArgumentException('Méthode non autorisée');
    }

    // Validate content type for POST requests with JSON
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $_POST = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON invalide');
        }
    }

    $adminController = new AdminController();
    $adminController->handleFormSubmission();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
