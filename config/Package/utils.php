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
            "À Propos de nous" => "/#story"
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

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return 'Date invalide';
        }

        return date('d/m/Y', $timestamp);
    }

    /**
     * Récupère la page actuelle
     * @return string Nom de la page active
     */
    public function getCurrentPage(): string
    {
        $currentPage = basename($_SERVER['REQUEST_URI']);
        return $currentPage;
    }
}

// Fonctions de compatibilité
function safeInclude(string $filePath): mixed
{
    return Utils::safeInclude($filePath);
}

function nullSafe(mixed $value, string $default = "Non Renseigné"): string
{
    return Utils::nullSafe($value, $default);
}

function getLogoUrl(int $entrepriseId): string
{
    return Utils::getLogoUrl($entrepriseId);
}
