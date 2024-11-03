<?php
if (!function_exists('safeInclude')) {
    require_once './../config/init.php';
}

$navLinks = getNavLinks();
function renderFooter($siteName, $navLinks, $logoURL) {
    echo '
    <head>
    <link rel="stylesheet" href="/assets/css/footer.css">
    </head>
    <footer>
        <div class="footer-container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="footer-logo">' . htmlspecialchars($siteName) . '</div>
                    <div class="footer-tagline">Par Florian, Samuel et Benjamin avec le ❤️</div>
                </div>
                <div class="col-md-4 text-center">
                    <img src="' . htmlspecialchars($logoURL) . '" alt="Logo La Salle Avignon" class="la-salle-logo">
                </div>
                <div class="col-md-4 text-end">
                    <ul class="list-unstyled">
    ';
    
    // Check si NavLinks
    if (!empty($navLinks)) {
        foreach ($navLinks as $name => $link) {
            echo '<li><a href="' . htmlspecialchars($link) . '" class="text-white">' . htmlspecialchars($name) . '</a></li>';
        }
    } else {
        echo '<li>Aucun lien disponible.</li>'; // Display a message if no links are available
    }

    echo '
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    ';
}
?>