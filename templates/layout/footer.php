<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}
use Config\Utils;

$Utils = new Utils();
$navLinks = $Utils->getNavLinks();

Use Config\SiteConfig;
Config\SiteConfig::init();

$SiteConfig = new SiteConfig();
$siteName = SiteConfig::$siteName;
$logoURL = SiteConfig::$logoURL;
?>
<footer>
    <div class="footer-container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="footer-logo"><?php echo htmlspecialchars($siteName); ?></div>
                <div class="footer-tagline">Par Florian, Samuel et Benjamin avec le ❤️</div>
            </div>
            <div class="col-md-4 text-center">
                <img src="<?php echo htmlspecialchars($logoURL); ?>" alt="Logo La Salle Avignon" class="organisation-logo">
            </div>
            <div class="col-md-4 text-end">
                <ul class="list-unstyled">
                    <?php
                    // Check si NavLinks
                    if (!empty($navLinks)) {
                    foreach ($navLinks as $name => $link) {
                    echo '<li><a href="' . htmlspecialchars($link) . '" class="text-white">' . htmlspecialchars($name) . '</a></li>';
                    }
                    } else {
                    echo '<li>Aucun lien disponible.</li>'; // Message si aucun liens n'est référencé
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="/assets/js/navbar.js"></script>