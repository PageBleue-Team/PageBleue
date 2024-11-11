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

// Fonction helper pour sécuriser l'affichage
function securePrint(?string $text): string
{
    return nl2br(htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8'));
}

// Vérification de la présence des données requises
$requiredSections = ['site', 'creators', 'education', 'hosting', 'content'];
foreach ($requiredSections as $section) {
    if (!isset($config[$section])) {
        die("Configuration invalide : section '$section' manquante");
    }
}
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
                <p><?= securePrint($config['content']['main_description']) ?></p>
                
                <?php if (!empty($config['creators'])) : ?>
                <p>Ce site web a été créé par :</p>
                <ul class="mentions-list">
                    <?php foreach ($config['creators'] as $creator) : ?>
                        <li><span class="emphasis">Étudiant :</span> <?= securePrint($creator['name']) ?></li>
                    <?php endforeach; ?>
                    <?php foreach ($config['helpers'] as $helper) : ?>
                        <li><span class="emphasis">Étudiant :</span> <?= securePrint($helper['name']) ?></li>
                    <?php endforeach; ?>
                    <li><span class="emphasis">Formation :</span> <?= securePrint($config['education']['formation']) ?></li>
                    <li><span class="emphasis">Établissement :</span> <?= securePrint($config['education']['etablissement']) ?></li>
                    <li><span class="emphasis">Contact :</span> <?= securePrint($config['site']['contact_email']) ?></li>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Objectif du site -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">2. Objectif du site</h2>
            <div class="mentions-content">
                <p><?= securePrint($config['content']['purpose']) ?></p>
            </div>
        </div>

        <!-- Section Hébergement -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">3. Hébergement</h2>
            <div class="mentions-content">
                <p>Ce site est hébergé par :</p>
                <ul class="mentions-list">
                    <li><span class="emphasis">Hébergeur :</span> <?= securePrint($config['hosting']['name']) ?></li>
                    <li><span class="emphasis">Adresse :</span> <?= securePrint($config['hosting']['address']) ?></li>
                </ul>
            </div>
        </div>

        <!-- Section Utilisation des données -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">4. Utilisation des données</h2>
            <div class="mentions-content">
                <p><?= securePrint($config['content']['data_usage']) ?></p>
            </div>
        </div>

        <!-- Section Cookies -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">5. Cookies</h2>
            <div class="mentions-content">
                <p><?= securePrint($config['content']['cookies_info']) ?></p>
            </div>
        </div>

        <!-- Section Propriété intellectuelle -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">6. Propriété intellectuelle</h2>
            <div class="mentions-content">
                <p><?= securePrint($config['content']['intellectual_property']) ?></p>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
