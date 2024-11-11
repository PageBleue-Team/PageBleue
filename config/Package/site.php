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

    public static function init(): void
    {
        // Configuration de base
        self::$siteName = $_ENV['WEBSITE'];
        self::$logoURL = $_ENV['ORGANIZATION_LOGO_PATH'];
        self::$descriptionLength = isset($_ENV['DESCRIPTION_LENGTH'])
            ? intval($_ENV['DESCRIPTION_LENGTH'])
            : 250;

        // Chargement des textes depuis le fichier YAML
        $texts = Yaml::parseFile(dirname(__DIR__, 2) . '/public/texts/site.yaml')['site'];

        // Meta données
        self::$metaDescription = $texts['meta_description'];
        self::$googleVerification = $_ENV['GOOGLE_CONSOLE_KEY'];

        // Descriptions
        self::$mainDescription = $texts['main_description'];
        self::$historyDescription = $texts['history_description'];

        private static function validateTeamMember(array $member): void
        {
            $required = ['name', 'role', 'filiere'];
            foreach ($required as $field) {
                if (!isset($member[$field]) || !is_string($member[$field])) {
                    throw new \RuntimeException("Champ '$field' invalide pour un membre de l'équipe");
                }
            }
        }

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
