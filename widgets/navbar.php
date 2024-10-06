<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../config.php';
}

function getNavLinks() {
    $navLinks = [
        "Accueil" => "/#",
        "Entreprises" => "/list",
        "Formulaire" => "/form",
        "À Propos de nous" => "/#story"
    ];

    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        $navLinks["Admin"] = "/panel";
    }

    return $navLinks;
}

function renderNavbar($siteName) {
    $navLinks = getNavLinks();

    // Détection automatique de la page active
    $currentPage = $_SERVER['REQUEST_URI'];
    $currentPage = strtok($currentPage, '?'); // Retire les paramètres d'URL
    $activePage = array_search($currentPage, $navLinks) ?: '';

    echo '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <link rel="manifest" href="/favicons/site.webmanifest">
    <link rel="icon" href="favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">

    // Styles spécifiques à la navbar
    <style>
        :root {
            --primary-blue: #007bff;
        }
        .navbar {
            background-color: var(--primary-blue) !important;
            color: white;
        }
        .navbar-light .navbar-brand,
        .navbar-light .navbar-nav .nav-link {
            color: white !important;
        }
        .nav-link {
            position: relative;
        }
        .nav-slider {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            background-color: white;
            transition: all 0.6s ease;
        }
        .search-container {
            position: relative;
            width: 100%;
            max-width: 300px;
        }
        .search-input {
            padding-right: 40px;
            width: 100%;
            height: 40px;
        }
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            pointer-events: none;
        }
        @media (max-width: 991px) {
            .navbar-nav {
                margin-bottom: 15px;
            }
            .search-container {
                margin-top: 15px;
                max-width: 100%;
            }
        }
        .mobile-device .nav-slider {
            display: none;
        }
        .mobile-device .nav-link.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: white;
        }
    </style>
    ';
    
    // Structure HTML de la navbar
    echo '<nav class="navbar navbar-expand-lg navbar-light fixed-top">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="/#">' . htmlspecialchars($siteName) . '</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">';
    echo '<span class="navbar-toggler-icon"></span>';
    echo '</button>';
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<ul class="navbar-nav me-auto mb-2 mb-lg-0 position-relative">';

    foreach ($navLinks as $name => $link) {
        $activeClass = ($activePage == $name) ? ' active' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link' . $activeClass . '" href="' . htmlspecialchars($link) . '" data-nav="' . htmlspecialchars(strtolower($name)) . '">' . htmlspecialchars($name) . '</a>';
        echo '</li>';
    }

    echo '<div class="nav-slider"></div>';
    echo '</ul>';
    echo '<div class="search-container">';
    echo '<input class="form-control search-input" type="search" placeholder="Rechercher une entreprise" aria-label="Search" id="search-input">';
    echo '<i class="fas fa-search search-icon"></i>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';

    // Scripts JavaScript pour la navbar et Bootstrap
    echo '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function isMobileDevice() {
        return (typeof window.orientation !== "undefined") 
            || (navigator.userAgent.indexOf("IEMobile") !== -1)
            || (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))
            || window.innerWidth <= 991;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const navSlider = document.querySelector(".nav-slider");
        const navLinks = document.querySelectorAll(".nav-link");
        const navContainer = document.querySelector(".navbar-nav");
        const navbarCollapse = document.querySelector(".navbar-collapse");
        const navbarToggler = document.querySelector(".navbar-toggler");

        function handleMobileChange() {
            if (isMobileDevice()) {
                document.body.classList.add("mobile-device");
                navSlider.style.display = "none";
            } else {
                document.body.classList.remove("mobile-device");
                navSlider.style.display = "block";
                resetSlider();
            }
        }

        function moveSlider(link) {
            if (!isMobileDevice()) {
                navSlider.style.width = `${link.offsetWidth}px`;
                navSlider.style.left = `${link.offsetLeft}px`;
            }
        }

        function resetSlider() {
            if (!isMobileDevice()) {
                const activeLink = document.querySelector(".nav-link.active") || navLinks[0];
                moveSlider(activeLink);
            }
        }

        navLinks.forEach(link => {
            link.addEventListener("mouseenter", () => moveSlider(link));
            link.addEventListener("click", () => {
                if (isMobileDevice()) {
                    navbarCollapse.classList.remove("show");
                    navbarToggler.setAttribute("aria-expanded", "false");
                    navLinks.forEach(l => l.classList.remove("active"));
                    link.classList.add("active");
                }
            });
        });

        navContainer.addEventListener("mouseleave", resetSlider);

        // Initial position
        handleMobileChange();

        // Handle window resize
        window.addEventListener("resize", handleMobileChange);

        // Search functionality
        const searchInput = document.getElementById("search-input");
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const searchTerm = searchInput.value;
                if (searchTerm.length > 2) {
                    window.location.href = `/?search=${encodeURIComponent(searchTerm)}`;
                }
            }
        });

        // Ensure Bootstrap collapse works for mobile menu
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener("click", function() {
                navbarCollapse.classList.toggle("show");
            });
        }
    });
    </script>
    ';
}
?>