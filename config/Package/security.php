<?php

namespace Config;

class LoginLogger
{
    public static function log(string $username, bool $success): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $ip_address = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'unknown';
            $user_agent = substr(htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

            // Récupération ID utilisateur
            $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            $user_id = $user ? $user['id'] : null;

            // Log tentative
            $sql = "INSERT INTO login_logs "
                . "(user_id, username, ip_address, user_agent, success) "
                . "VALUES "
                . "(:user_id, :username, :ip_address, :user_agent, :success)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'username' => substr($username, 0, 100),
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'success' => $success ? 1 : 0
            ]);

            if ($success && $user_id) {
                $stmt = $pdo->prepare("UPDATE Users SET last_login = CURRENT_TIMESTAMP, login_attempts = 0 WHERE id = :id");
                $stmt->execute(['id' => $user_id]);
            }
        } catch (\PDOException $e) {
            error_log("Erreur de journalisation : " . $e->getMessage());
            throw new \RuntimeException("Impossible de journaliser la tentative de connexion");
        }
    }
}

// Fonction de compatibilité
function logLoginAttempt(string $username, bool $success): void
{

    LoginLogger::log($username, $success);
}
