<?php

namespace Config;

class Security {
    private static array $cspDirectives = [
        "default-src" => ["'self'"],
        "script-src" => [
            "'self'",
            "https://code.jquery.com",
            "https://cdn.jsdelivr.net",
            "'unsafe-inline'",
            "'unsafe-eval'"
        ],
        "style-src" => [
            "'self'",
            "https://cdn.jsdelivr.net",
            "'unsafe-inline'"
        ],
        "img-src" => ["'self'", "data:", "https:"],
        "font-src" => [
            "'self'", 
            "https://cdn.jsdelivr.net",
            "data:"
        ],
        "connect-src" => ["'self'"],
        "frame-src" => ["'none'"],
        "object-src" => ["'none'"],
        "base-uri" => ["'self'"],
        "form-action" => ["'self'"],
        "frame-ancestors" => ["'none'"],
        "require-sri-for" => ["script", "style"]
    ];

    public static function initSecurity(): void {
        self::setSecurityHeaders();
        self::setSessionSecurity();
        self::setCacheHeaders();
    }

    private static function setSecurityHeaders(): void {
        // Construction de la CSP
        $csp = [];
        foreach (self::$cspDirectives as $directive => $values) {
            $csp[] = $directive . ' ' . implode(' ', $values);
        }

        // En-têtes de sécurité
        header("Content-Security-Policy: " . implode('; ', $csp));
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        
        if ($_ENV['APP_ENV'] === 'production') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }

    private static function setSessionSecurity(): void {
        // Configuration sécurisée des sessions
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Strict');
    }

    private static function setCacheHeaders(): void {
        // En-têtes de cache
        header('Cache-Control: public, max-age=31536000'); // 1 an
        header('Expires: ' . date('D, d M Y H:i:s', time() + 31536000) . ' UTC');
    }

    public static function generateNonce(): string {
        $nonce = base64_encode(random_bytes(32));
        self::$cspDirectives['script-src'][] = "'nonce-$nonce'";
        return $nonce;
    }
}

class LoginLogger
{
    public static function log(string $username, bool $success): void
    {
        $pdo = Database::getInstance()->getConnection();
        
        // Nettoyage et validation des entrées
        $ip_address = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: '';
        $user_agent = htmlspecialchars(
            strip_tags($_SERVER['HTTP_USER_AGENT'] ?? ''), 
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        // Récupération ID utilisateur avec requête préparée
        $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        $user_id = $user ? $user['id'] : null;

        // Log tentative avec requête préparée
        $sql = "INSERT INTO login_logs "
             . "(user_id, username, ip_address, user_agent, success) "
             . "VALUES "
             . "(:user_id, :username, :ip_address, :user_agent, :success)";
             
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'username' => $username,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'success' => $success ? 1 : 0
        ]);

        // Mise à jour du compte utilisateur si connexion réussie
        if ($success && $user_id) {
            $stmt = $pdo->prepare(
                "UPDATE Users 
                 SET last_login = CURRENT_TIMESTAMP, 
                     login_attempts = 0 
                 WHERE id = :id"
            );
            $stmt->execute(['id' => $user_id]);
        }
    }
}

// Fonction de compatibilité
function logLoginAttempt(string $username, bool $success): void
{

    LoginLogger::log($username, $success);
}
