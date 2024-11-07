<?php

namespace App\Controller;

class SecurityController
{
    private const SESSION_LIFETIME = 1800; // 30 minutes en secondes
    private const ADMIN_SESSION_KEY = 'admin_logged_in';
    private const LAST_ACTIVITY_KEY = 'last_activity';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => true,
                'cookie_samesite' => 'Lax',
                'gc_maxlifetime' => self::SESSION_LIFETIME
            ]);
        }
    }

    public function home(): void
    {
        try {
            // Vérifier si le fichier existe avant de l'inclure
            $templatePath = '../templates/pages/home.php';
            if (!file_exists($templatePath)) {
                throw new \RuntimeException('Template non trouvé : ' . $templatePath);
            }
            include $templatePath;
        } catch (\Exception $e) {
            // Log l'erreur
            error_log("Erreur dans home() : " . $e->getMessage());
            // Rediriger vers une page d'erreur
            header('Location: /error');
            exit;
        }
    }

    public function isAdminLoggedIn(): bool
    {
        if ($this->isSessionExpired()) {
            return false;
        }
        return isset($_SESSION[self::ADMIN_SESSION_KEY]) && 
               $_SESSION[self::ADMIN_SESSION_KEY] === true;
    }

    public function adminLogout(): void
    {
        // Vider toutes les variables de session
        $_SESSION = array();

        // Détruire le cookie de session si présent
        $sessionName = session_name();
        if ($sessionName !== false && isset($_COOKIE[$sessionName])) {
            setcookie(
                $sessionName,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }

        // Détruire la session
        session_destroy();
        session_write_close();
    }

    public function adminLogin(string $username, string $password): bool
    {
        try {
            // Vérifier que les variables d'environnement sont définies
            if (!isset($_ENV['ADMIN_USERNAME']) || !isset($_ENV['ADMIN_PASSWORD_HASH'])) {
                throw new \RuntimeException('Configuration d\'authentification manquante');
            }

            // Protection contre les attaques par timing
            if (!hash_equals($_ENV['ADMIN_USERNAME'], $username)) {
                return false;
            }

            if (!password_verify($password, $_ENV['ADMIN_PASSWORD_HASH'])) {
                return false;
            }

            // Régénérer l'ID de session pour prévenir la fixation de session
            session_regenerate_id(true);

            $_SESSION[self::ADMIN_SESSION_KEY] = true;
            $_SESSION[self::LAST_ACTIVITY_KEY] = time();

            return true;

        } catch (\Exception $e) {
            error_log("Erreur lors de la tentative de connexion : " . $e->getMessage());
            return false;
        }
    }

    public function isSessionExpired(): bool
    {
        if (!isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return true;
        }

        $isExpired = (time() - $_SESSION[self::LAST_ACTIVITY_KEY]) > self::SESSION_LIFETIME;
        
        if ($isExpired) {
            $this->adminLogout();
            return true;
        }

        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        return false;
    }

    /**
     * Vérifie et met à jour le jeton CSRF
     * @return string
     */
    public function getCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie la validité du jeton CSRF
     * @param string $token
     * @return bool
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}