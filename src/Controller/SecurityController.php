<?php

namespace App\Controller;

use App\Domain\Repository\UsersRepository;

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
        global $_SESSION;
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

    /**
     * @return array{success: bool, error?: string}
     */
    public function attemptLogin(string $username, string $password): array
    {
        if (empty($username) || empty($password) || strlen($username) > 50) {
            return ['success' => false, 'error' => "Nom d'utilisateur ou mot de passe invalide."];
        }

        try {
            $usersRepository = new UsersRepository();
            $user = $usersRepository->findByUsername($username);

            if (!$user) {
                return ['success' => false, 'error' => "Nom d'utilisateur ou mot de passe incorrect."];
            }

            if ($usersRepository->isAccountLocked($user)) {
                return ['success' => false, 'error' => "Compte temporairement verrouillé. Veuillez réessayer plus tard."];
            }

            if ($usersRepository->verifyPassword($password, $user)) {
                $usersRepository->resetLoginAttempts($user['id']);
                $this->createUserSession($user);
                return ['success' => true];
            }

            $usersRepository->incrementLoginAttempts($user['id']);
            return ['success' => false, 'error' => "Nom d'utilisateur ou mot de passe incorrect."];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['success' => false, 'error' => "Une erreur est survenue. Veuillez réessayer plus tard."];
        }
    }

    /**
     * @param array{id: int, username: string} $user
     */
    private function createUserSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION[self::ADMIN_SESSION_KEY] = true;
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        session_regenerate_id(true);
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

    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function refreshCsrfToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
