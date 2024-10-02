<?php
require_once __DIR__ . '/../config.php';
$siteName = $_ENV['WEBSITE'];
$LogoURL = $_ENV['LASALLE_LOGO_URL'];

require_once 'navbar.php';
function renderFooter($siteName, $navLinks) {
    global $navLinks;
    
    echo '
    <style>
        footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 20px 0;
            width: 100%;
            margin-top: 20px;
        }
        .footer-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .footer-tagline {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .la-salle-logo {
            max-height: 50px;
            margin-right: 15px;
        }
    </style>
    
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="footer-logo">' . htmlspecialchars($siteName) . '</div>
                    <div class="footer-tagline">Par Florian, Samuel et Benjamin avec le ❤️</div>
                </div>
                <div class="col-md-4 text-center">
                    <img src="' . htmlspecialchars($LogoURL) . '" alt="Logo La Salle Avignon" class="la-salle-logo">
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