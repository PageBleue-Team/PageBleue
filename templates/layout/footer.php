<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}
use Config\Utils;

$Utils = new Utils();
$navLinks = $Utils->getNavLinks();

use Config\SiteConfig;
Config\SiteConfig::init();

$siteName = SiteConfig::get('global.name');
$footerLove = SiteConfig::get('footer.love');

$logoURL = $_ENV['ORGANIZATION_LOGO_PATH'];
?>
<footer>
    <div class="footer-container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="footer-logo">
                    <?php echo htmlspecialchars($siteName); ?>
                </div>
                <div class="footer-tagline">
                    <?php echo htmlspecialchars($footerLove); ?>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <img 
                    src="<?php echo htmlspecialchars($logoURL); ?>" 
                    alt="Logo La Salle Avignon" 
                    class="organisation-logo"
                    width="200"
                    height="100"
                >
            </div>
            <div class="col-md-4 text-end">
                <ul class="list-unstyled">
                    <?php if (!empty($navLinks)) : ?>
                        <?php foreach ($navLinks as $name => $link) : ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($link); ?>" 
                                   class="text-white">
                                    <?php echo htmlspecialchars($name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li>Aucun lien disponible.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="/assets/js/navbar.js"></script>
