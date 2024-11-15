<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\SiteConfig;

SiteConfig::init();

// Récupération des données
$mainDescription = SiteConfig::get('legal.content.main_description') ?? '';
$purposeText = SiteConfig::get('legal.content.purpose') ?? '';
$etablissement = SiteConfig::get('legal.education.etablissement') ?? '';
$formation = SiteConfig::get('legal.education.formation') ?? '';
$hostingName = SiteConfig::get('legal.hosting.name') ?? '';
$hostingAddress = SiteConfig::get('legal.hosting.address') ?? '';
$creators = SiteConfig::get('legal.creators') ?? [];
$helpers = SiteConfig::get('legal.helpers') ?? [];
$dataUsage = SiteConfig::get('legal.content.data_usage') ?? '';
$cookiesInfo = SiteConfig::get('legal.content.cookies_info') ?? '';
$intellectualProperty = SiteConfig::get('legal.content.intellectual_property') ?? '';
?>

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
                <p><?= nl2br(htmlspecialchars($mainDescription)) ?></p>
                
                <?php if (!empty($creators)) : ?>
                <p>Ce site web a été créé par :</p>
                <ul class="mentions-list">
                    <?php foreach ($creators as $creator) : ?>
                        <li>
                            <span class="emphasis">Étudiant :</span> 
                            <?= htmlspecialchars($creator['name']) ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!empty($helpers)) : ?>
                        <?php foreach ($helpers as $helper) : ?>
                            <li>
                                <span class="emphasis">Étudiant :</span> 
                                <?= htmlspecialchars($helper['name']) ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li>
                        <span class="emphasis">Formation :</span> 
                        <?= nl2br(htmlspecialchars($formation)) ?>
                    </li>
                    <li>
                        <span class="emphasis">Établissement :</span> 
                        <?= nl2br(htmlspecialchars($etablissement)) ?>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Objectif du site -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">2. Objectif du site</h2>
            <div class="mentions-content">
                <p><?= nl2br(htmlspecialchars($purposeText)) ?></p>
            </div>
        </div>

        <!-- Section Hébergement -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">3. Hébergement</h2>
            <div class="mentions-content">
                <p>Ce site est hébergé par :</p>
                <ul class="mentions-list">
                    <li><span class="emphasis">Hébergeur :</span> <?= htmlspecialchars($hostingName) ?></li>
                    <li><span class="emphasis">Adresse :</span> <?= htmlspecialchars($hostingAddress) ?></li>
                </ul>
            </div>
        </div>

        <!-- Section Utilisation des données -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">4. Utilisation des données</h2>
            <div class="mentions-content">
                <p><?= nl2br(htmlspecialchars($dataUsage)) ?></p>
            </div>
        </div>

        <!-- Section Cookies -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">5. Cookies</h2>
            <div class="mentions-content">
                <p><?= nl2br(htmlspecialchars($cookiesInfo)) ?></p>
            </div>
        </div>

        <!-- Section Propriété intellectuelle -->
        <div class="mentions-section">
            <h2 class="mentions-subtitle">6. Propriété intellectuelle</h2>
            <div class="mentions-content">
                <p><?= nl2br(htmlspecialchars($intellectualProperty)) ?></p>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
