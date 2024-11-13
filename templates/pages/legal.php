<?php

if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

try {
    $config = Yaml::parseFile(ROOT_PATH . '/public/texts/legal.yaml');
} catch (ParseException $e) {
    die('Erreur de configuration : ' . $e->getMessage());
}

// Vérification de la présence des données requises
$requiredSections = ['site', 'creators', 'education', 'hosting', 'content'];
foreach ($requiredSections as $section) {
    if (!isset($config[$section])) {
        die("Configuration invalide : section '$section' manquante");
    }
}

// Traitement des données pour l'affichage
$mainDescription = htmlspecialchars(
    $config['content']['main_description'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);
$mainDescription = nl2br($mainDescription);

$purposeText = htmlspecialchars(
    $config['content']['purpose'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);
$purposeText = nl2br($purposeText);

$etablissement = htmlspecialchars($config['education']['etablissement'] ?? '', ENT_QUOTES, 'UTF-8');
$etablissement = nl2br($etablissement);

$formation = htmlspecialchars($config['education']['formation'] ?? '', ENT_QUOTES, 'UTF-8');
$formation = nl2br($formation);

$contactEmail = htmlspecialchars($config['site']['contact_email'] ?? '', ENT_QUOTES, 'UTF-8');
$contactEmail = nl2br($contactEmail);

$hostingName = htmlspecialchars($config['hosting']['name'] ?? '', ENT_QUOTES, 'UTF-8');
$hostingAddress = htmlspecialchars($config['hosting']['address'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="fr">
<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<body>
    <!-- Navbar -->
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <div class="mentions-legales">
        <h1 class="mentions-title">Mentions légales</h1>

        <!-- Section Informations générales -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">1. Informations générales</h2>
            <div class="mentions-content">
                <p><?= $mainDescription ?></p>
                
                <?php if (!empty($config['creators'])) : ?>
                <p>Ce site web a été créé par :</p>
                <ul class="mentions-list">
                    <?php foreach ($config['creators'] as $creator) : ?>
                        <li>
                            <span class="emphasis">Étudiant :</span> 
                            <?= htmlspecialchars($creator['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                    <?php foreach ($config['helpers'] as $helper) :
                        $helperName = htmlspecialchars($helper['name'] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                        <li>
                            <span class="emphasis">Étudiant :</span> 
                            <?= $helperName ?>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <span class="emphasis">Formation :</span> 
                        <?= $formation ?>
                    </li>
                    <li>
                        <span class="emphasis">Établissement :</span> 
                        <?= $etablissement ?>
                    </li>
                    <li><span class="emphasis">Contact :</span> <?= $contactEmail ?></li>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Objectif du site -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">2. Objectif du site</h2>
            <div class="mentions-content">
                <p><?= $purposeText ?></p>
            </div>
        </div>

        <!-- Section Hébergement -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">3. Hébergement</h2>
            <div class="mentions-content">
                <p>Ce site est hébergé par :</p>
                <ul class="mentions-list">
                    <li><span class="emphasis">Hébergeur :</span> <?= $hostingName ?></li>
                    <li><span class="emphasis">Adresse :</span> <?= $hostingAddress ?></li>
                </ul>
            </div>
        </div>

        <!-- Section Utilisation des données -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">4. Utilisation des données</h2>
            <div class="mentions-content">
                <p><?= htmlspecialchars($config['content']['data_usage'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <!-- Section Cookies -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">5. Cookies</h2>
            <div class="mentions-content">
                <p><?= htmlspecialchars($config['content']['cookies_info'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($config['content']['cookies_info'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <!-- Section Propriété intellectuelle -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">6. Propriété intellectuelle</h2>
            <div class="mentions-content">
                <p><?= htmlspecialchars($config['content']['intellectual_property'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
