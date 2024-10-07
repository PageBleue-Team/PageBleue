<?php
require_once 'config.php'; // Assurez-vous que ce fichier contient vos paramètres de connexion à la base de données

// Fonction pour hacher un mot de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

try {
    $pdo = getDbConnection(); // Utilisez votre fonction de connexion à la base de données
    
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mettre à jour chaque mot de passe
    $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    
    foreach ($users as $user) {
        // Vérifiez si le mot de passe n'est pas déjà haché
        if (password_get_info($user['password'])['algoName'] === 'unknown') {
            $hashedPassword = hashPassword($user['password']);
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
?>