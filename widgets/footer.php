<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../config.php';
}

$navLinks = getNavLinks();
function renderFooter($siteName, $navLinks, $logoURL) {
    echo '
    <head>
    <style>
        html, body {
            height: 100%; /* Ensure full height of the page */
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure minimum height of the viewport */
        }
        .footer-content {
            flex-grow: 1; /* Ensure content grows and pushes the footer down */
        }
        footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 20px 0;
            width: 100%;
            margin-top: auto; /* Ensures footer is at the bottom */
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
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
            margin-right: 15px;
            width: 100%;
            max-height: 100px;
            object-fit: contain;
        }
    </style>
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