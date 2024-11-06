<?php
namespace Config;
Use \App\Controller\SecurityController;

class Utils {
    public static function safeInclude(string $filePath): mixed {
        $fullPath = ROOT_PATH . '/' . ltrim($filePath, '/');
        if (file_exists($fullPath)) {
            return require_once $fullPath;
        }
        throw new \Exception("File not found: $filePath");
    }

    public static function includeWidget(string $name): void {
        $filePath = ROOT_PATH . "/templates/layout/{$name}.php";
        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            error_log("Widget non trouvé : {$name}");
        }
    }

    public static function nullSafe(mixed $value, string $default = "Non Renseigné"): string {
        return $value !== null && $value !== '' ? $value : $default;
    }

    public static function getLogoUrl(int $entrepriseId): string {
        $logoPath = LOGO_DIR . '/' . $entrepriseId . '.webp';
        return file_exists($logoPath) ? $logoPath : LOGO_DIR . '/default.png';
    }

    /**
     * Récupère les liens de navigation
     * @return array Liste des liens de navigation
     */
    public static function getNavLinks(): array {
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
function safeInclude(string $filePath): mixed {
    return Utils::safeInclude($filePath);
}


// A supprimer !!!!
function includeWidget(string $name): void {
    Utils::includeWidget($name);
}

function nullSafe(mixed $value, string $default = "Non Renseigné"): string {
    return Utils::nullSafe($value, $default);
}

function getLogoUrl(int $entrepriseId): string {
    return Utils::getLogoUrl($entrepriseId);
}