<?php

namespace Config;

use App\Controller\SecurityController;

class Utils
{
    public static function safeInclude(string $filePath): mixed
    {
        if (!defined('ROOT_PATH')) {
            throw new \RuntimeException('ROOT_PATH constant is not defined');
        }

        $fullPath = ROOT_PATH . '/' . ltrim($filePath, '/');
        if (file_exists($fullPath)) {
            return require_once $fullPath;
        }
        throw new \Exception("File not found: $filePath");
    }

    public static function nullSafe(mixed $value, string $default = "Non Renseigné"): string
    {
        return $value !== null && $value !== '' ? $value : $default;
    }

    public static function getLogoUrl(int $entrepriseId): string
    {
        if (!defined('LOGO_PATH')) {
            throw new \RuntimeException('LOGO_PATH constant is not defined');
        }

        if ($entrepriseId <= 0) {
            throw new \InvalidArgumentException('ID entreprise invalide');
        }

        $logoPath = LOGO_PATH . '/' . $entrepriseId . '.webp';

        if (file_exists($logoPath)) {
            // Vérifier le type MIME du fichier
            $mimeType = mime_content_type($logoPath);
            if (!in_array($mimeType, ['image/webp'])) {
                error_log("Type de fichier logo invalide pour entreprise $entrepriseId: $mimeType");
                return LOGO_PATH . '/default.png';
            }
            return $logoPath;
        }

        $defaultLogo = LOGO_PATH . '/default.png';
        if (!file_exists($defaultLogo)) {
            throw new \RuntimeException('Logo par défaut non trouvé');
        }

        return $defaultLogo;
    }

    /**
     * Récupère les liens de navigation
     * @return array<string, string> Tableau associatif des liens de navigation
     */
    public static function getNavLinks(): array
    {
        $navLinks = [
            "Accueil" => "/#",
            "Entreprises" => "/list",
            "Formulaire" => "/form",
            "À Propos de nous" => "/#aboutus",
            "Notre Histoire" => "/#story"
        ];
        $SecurityController = new SecurityController();
        if ($SecurityController->isAdminLoggedIn()) {
            $navLinks["Panel"] = "/panel";
        }

        return $navLinks;
    }

    public function formatDate(?string $date): string
    {
        if (!$date) {
            return 'Non renseigné';
        }

        try {
            $dateTime = new \DateTimeImmutable($date);
            return $dateTime->format('d/m/Y');
        } catch (\Exception $e) {
            return 'Date invalide';
        }
    }

    /**
     * Récupère la page actuelle
     * @return string Nom de la page active
     */
    public static function getCurrentPage(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === false || $path === null) {
            return 'index';
        }
        $currentPage = basename($path);
        return $currentPage ?: 'index';
    }

    /**
     * Récupère le nom de la navlink correspondant à la page actuelle
     * @return string Nom de la navlink ou 'Inconnu' si non trouvé
     */
    public static function getCurrentNavLinkName(): string
    {
        // Récupérer l'URL actuelle
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        error_log("Current URL: $currentUrl");

        // Récupérer la partie avant le fragment
        $currentPage = strtok($currentUrl, '#') ?: ''; // Prendre tout avant le #
        $currentPage = rtrim($currentPage, '/'); // Enlever le slash final si présent
        error_log("Current Page: $currentPage");

        $navLinks = self::getNavLinks();

        foreach ($navLinks as $name => $url) {
            $url = rtrim($url, '/'); // Enlever le slash final pour la comparaison
            error_log("Comparing current page: $currentPage with nav link: $url");
            if ($url === $currentPage) {
                return $name; // Retourner le nom de la navlink
            }
        }

        return 'Inconnu'; // Retourner 'Inconnu' si aucune correspondance n'est trouvée
    }
}

// Fonctions de compatibilité
function safeInclude(string $filePath): mixed
{
    if (!defined('ROOT_PATH')) {
        throw new \RuntimeException('ROOT_PATH constant is not defined');
    }
    // Nettoyer et valider le chemin
    $filePath = htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8');
    $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);

    // Vérifier les caractères dangereux
    if (preg_match('/[<>:"\\|?*]/', $normalizedPath)) {
        throw new \Exception("Caractères non autorisés dans le chemin");
    }

    $fullPath = ROOT_PATH . '/' . ltrim($filePath, '/');

    // Vérifier que le chemin final est dans le répertoire autorisé
    $realPath = realpath($fullPath);
    $rootRealPath = realpath(ROOT_PATH);
    if (
        $realPath === false || $rootRealPath === false ||
        !str_starts_with($realPath, $rootRealPath)
    ) {
        throw new \Exception("Accès au chemin non autorisé");
    }

    if (file_exists($fullPath)) {
        return require_once $fullPath;
    }
    throw new \Exception("File not found: $filePath");
}

function nullSafe(mixed $value, string $default = "Non Renseigné"): string
{
    return Utils::nullSafe($value, $default);
}

function getLogoUrl(int $entrepriseId): string
{
    return Utils::getLogoUrl($entrepriseId);
}
