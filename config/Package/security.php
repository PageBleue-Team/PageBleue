<?php
namespace Config\Package;

class LoginLogger {
    public static function log(string $username, bool $success): void {
        $pdo = Database::getInstance()->getConnection();
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Récupération ID utilisateur
        $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        $user_id = $user ? $user['id'] : null;
        
        // Log tentative
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, username, ip_address, user_agent, success) VALUES (:user_id, :username, :ip_address, :user_agent, :success)");
        $stmt->execute([
            'user_id' => $user_id,
            'username' => $username,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'success' => $success ? 1 : 0
        ]);
        
        if ($success && $user_id) {
            $stmt = $pdo->prepare("UPDATE Users SET last_login = CURRENT_TIMESTAMP, login_attempts = 0 WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
        }
    }
}

// Fonction de compatibilité
function logLoginAttempt(string $username, bool $success): void {
    LoginLogger::log($username, $success);
}