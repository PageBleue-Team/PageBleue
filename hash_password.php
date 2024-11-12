<?php

require_once __DIR__ . '/config/init.php';

use Config\Database;
use App\Services\HashPassword;
$hashPassword = new HashPassword();


try {
// Utilisez votre fonction de connexion à la base de données
    $pdo = Database::getInstance()->getConnection();
// Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id, password FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Mettre à jour chaque mot de passe
    $updateStmt = $pdo->prepare("UPDATE Users SET password = :password WHERE id = :id");
    foreach ($users as $user) {
    // Vérifiez si le mot de passe n'est pas déjà haché
        if (password_get_info($user['password'])['algoName'] === 'unknown') {
            $hashedPassword = $hashPassword->hashPassword($user['password']);
            $updateStmt->execute([
                'password' => $hashedPassword,
                'id' => $user['id']
            ]);
            echo "Mot de passe mis à jour pour l'utilisateur ID: " . $user['id'] . "\n";
        } else {
            echo "Le mot de passe de l'utilisateur ID: " . $user['id'] . " est déjà haché.\n";
        }
    }

    echo "Tous les mots de passe ont été traités.\n";
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
