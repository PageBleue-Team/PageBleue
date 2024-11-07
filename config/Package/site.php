<?php
namespace Config;

class SiteConfig {
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

    public static function init(): void {
        // Configuration de base
        self::$siteName = $_ENV['WEBSITE'];
        self::$logoURL = $_ENV['ORGANIZATION_LOGO_PATH'];
        self::$descriptionLength = isset($_ENV['DESCRIPTION_LENGTH']) 
            ? intval($_ENV['DESCRIPTION_LENGTH']) 
            : 250;

        // Meta données
        self::$metaDescription = 'PageBleue, lieu de référencement d\'entreprises pour des recherches de Formations en Milieu Professionnel sur et autour d\'Avignon.';
        self::$googleVerification = $_ENV['GOOGLE_CONSOLE_KEY'];

        // Description principale
        self::$mainDescription = "Bienvenue sur Page Bleue, un site pensé pour faciliter la recherche de PFMP (période de formation en milieu professionnel) pour les élèves professionnels des filières CIEL (Cybersécurité, Informatique, Électronique) et MELEC (Métiers de l'Électricité et de ses Environnements Connectés) du lycée ST JEAN BAPTISTE Lasalle Avginon.

Ce projet a été créé et pensé par des étudiants, pour les étudiants, afin de centraliser les informations sur les entreprises où nos camarades ont effectué leur stage.
Ici, vous pourrez accéder à une base de données constamment enrichie et actualisée, filtrer les entreprises selon différents critères, et consulter les évaluations laissées par d'autres élèves en période de formation comme vous.

L'objectif est simple : vous aider à trouver une entreprise adaptée à vos besoins, de la recherche au contact, tout en vous donnant une vue d'ensemble sur les conditions de stage (accueil, tâches confiées, nombre d'élèves accueillis, etc.). Ce site est un véritable outil d'entraide pour améliorer et faciliter vos démarches de recherche de PFMP.";

        // Description historique
        self::$historyDescription = "Nous sommes trois étudiants, Samuel FRANCOIS, Florian CASTALDO et Benjamin BONARDO, issus de la filière CIEL au lycée La Salle Avignon.

L'idée de Page Bleue nous est venue pendant nos propres recherches de PFMP, lorsque nous avons constaté combien il était difficile de trouver des entreprises et d'obtenir des informations fiables sur celles-ci.
Notre objectif est donc de faciliter cette démarche pour les futurs élèves, en leur offrant un accès simple et rapide aux entreprises prêtes à accueillir des stagiaires, avec des avis et des évaluations basées sur des expériences réelles.

À plus long terme, nous aimerions élargir Page Bleue à d'autres filières et lycées, afin de rendre ce service qui nous tient à cœur accessible à un maximum d'élèves.";

        // Configuration de l'équipe
        self::$team = [
            [
                'name' => 'Samuel FRANCOIS',
                'role' => 'Rédacteur',
                'filiere' => 'CIEL'
            ],
            [
                'name' => 'Florian CASTALDO',
                'role' => 'Développeur',
                'filiere' => 'CIEL'
            ],
            [
                'name' => 'Benjamin BONARDO',
                'role' => 'Gestionnaire',
                'filiere' => 'CIEL'
            ]
        ];
    }
}