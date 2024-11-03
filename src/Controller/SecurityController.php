<?php

namespace App\Controller;

class SecurityController {
    public function home() {
        // Inclure la vue de la page d'accueil
        include '../templates/pages/home.php';
    }

    /**
     * Vérifie si un admin est connecté
     * @return bool
     */
    public function isAdminLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Déconnecte l'administrateur
     * @return void
     */
    public function adminLogout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_logged_in']);
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
    }

    /**
     * Connecte l'administrateur
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function adminLogin(string $username, string $password): bool {
        if ($username === $_ENV['ADMIN_USERNAME'] && 
            password_verify($password, $_ENV['ADMIN_PASSWORD_HASH'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    /**
     * Vérifie si la session est expirée
     * @return bool
     */
    public function isSessionExpired(): bool {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        $expireTime = 30 * 60; // 30 minutes
        if (time() - $_SESSION['last_activity'] > $expireTime) {
            $this->adminLogout();
            return true;
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
}