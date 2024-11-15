<?php

namespace Config;

class Security
{
    /**
     * Directives de la Content Security Policy
     * @var array<string, string[]>
     */
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

    public static function initSecurity(): void
    {
        self::setSecurityHeaders();
        self::setSessionSecurity();
        self::setCacheHeaders();
    }

    private static function setSecurityHeaders(): void
    {
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

    private static function setSessionSecurity(): void
    {
        // Configuration sécurisée des sessions
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Strict');
    }

    private static function setCacheHeaders(): void
    {
        // En-têtes de cache
        header('Cache-Control: public, max-age=31536000'); // 1 an
        header('Expires: ' . date('D, d M Y H:i:s', time() + 31536000) . ' UTC');
    }

    public static function generateNonce(): string
    {
        $nonce = base64_encode(random_bytes(32));
        self::$cspDirectives['script-src'][] = "'nonce-$nonce'";
        return $nonce;
    }
}
