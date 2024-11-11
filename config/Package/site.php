<?php

namespace Config;

use Symfony\Component\Yaml\Yaml;

class SiteConfig
{
    // Propriétés de base du site
    public static string $siteName;
    public static string $logoURL;
    public static int $descriptionLength;
// Meta données
    public static string $metaDescription;
    public static string $googleVerification;
// Descriptions longues
    public static string $mainDescription;
    public static string $historyDescription;
// Informations de l'équipe
    /** @var array<int, array{name: string, role: string, filiere: string}> */
    public static array $team;

// Déplacer la méthode getEnvOrFail ici
    private static function getEnvOrFail(string $key): string
    {
        if (!isset($_ENV[$key])) {
            throw new \RuntimeException("La variable d'environnement '$key' est requise");
        }
        return $_ENV[$key];
    }

    public static function init(): void
    {
        // Configuration de base
        self::$siteName = self::getEnvOrFail('WEBSITE');
        self::$logoURL = self::getEnvOrFail('ORGANIZATION_LOGO_PATH');
        self::$descriptionLength = isset($_ENV['DESCRIPTION_LENGTH'])
            ? intval($_ENV['DESCRIPTION_LENGTH'])
            : 250;

    // Chargement des textes depuis le fichier YAML
        $yamlPath = dirname(__DIR__, 2) . '/public/texts/site.yaml';
        if (!file_exists($yamlPath)) {
                throw new \RuntimeException("Le fichier de configuration YAML est manquant");
        }
        try {
            $yaml = Yaml::parseFile($yamlPath);
            if (!isset($yaml['site'])) {
                throw new \RuntimeException("La section 'site' est manquante dans le fichier YAML");
            }
            $texts = $yaml['site'];
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors du chargement du fichier YAML: " . $e->getMessage());
        }
            // Meta données
                self::$metaDescription = $texts['meta_description'];
                self::$googleVerification = $_ENV['GOOGLE_CONSOLE_KEY'];

            // Descriptions
                self::$mainDescription = $texts['main_description'];
                self::$historyDescription = $texts['history_description'];

        // Configuration de l'équipe
            $team = $texts['team'];
        if (!is_array($team)) {
            throw new \RuntimeException("La configuration de l'équipe doit être un tableau");
        }
        foreach ($team as $member) {
            self::validateTeamMember($member);
        }
            self::$team = $team;
    }

// Déplacer la méthode validateTeamMember à l'extérieur de la méthode init
/**
 * @param array{name: string, role: string, filiere: string} $member
 */
    private static function validateTeamMember(array $member): void
    {
        /** @var array{name: string, role: string, filiere: string} $member */
        $required = ['name', 'role', 'filiere'];

        // Vérifier que tous les champs requis existent
        foreach ($required as $field) {
            if (!array_key_exists($field, $member)) {
                throw new \RuntimeException("Le champ '$field' est requis pour un membre de l'équipe");
            }
        }
    }
}
