<?php

require_once __DIR__ . '/../config/init.php';
use App\Controller\AdminController;
header('Content-Type: application/json');
try {
    $adminController = new AdminController();
    $adminController->handleFormSubmission();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
