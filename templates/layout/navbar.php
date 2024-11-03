<?php
if (!function_exists('safeInclude')) {
    require_once './../config/init.php';
}
use \App\Controller\SecurityController;

function getNavLinks() {
    $navLinks = [
        "Accueil" => "/#",
        "Entreprises" => "/list",
        "Formulaire" => "/form",
        "À Propos de nous" => "/#story"
    ];

    $SecurityController = new SecurityController();
    // Vérification de l'authentification
    if ($SecurityController->isAdminLoggedIn()) {
        $navLinks["Panel"] = "/panel";
    }

    return $navLinks;
}

function renderNavbar($siteName) {
    $navLinks = getNavLinks();
    $currentPage = $_SERVER['REQUEST_URI'];
    $currentPage = strtok($currentPage, '?');
    $activePage = array_search($currentPage, $navLinks) ?: '';

    echo '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <link rel="manifest" href="./public/assets/images/favicons/site.webmanifest">
    <link rel="icon" href="./public/assets/images/favicons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="./public/assets/images/favicons/apple-touch-icon.png">

    <link rel="stylesheet" href="/assets/css/navbar.css">
    ';

    // Structure HTML de la navbar
    echo '<nav class="navbar navbar-expand-lg navbar-light fixed-top">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="/#">' . htmlspecialchars($siteName) . '</a>';
    echo '<button class="navbar-toggler" type="button" id="navbarToggler" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">';
    echo '<span class="navbar-toggler-icon"></span>';
    echo '</button>';
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<li class="navbar-nav me-auto mb-2 mb-lg-0 position-relative">';

    foreach ($navLinks as $name => $link) {
        $activeClass = ($activePage == $name) ? ' active' : '';
        $adminClass = ($name == "Panel") ? 'admin-link' : ''; // Classe pour l'onglet Admin
        echo '<div class="nav-item">';
        echo '<a class="nav-link ' . $adminClass . $activeClass . '" href="' . htmlspecialchars($link) . '" data-nav="' . htmlspecialchars(strtolower($name)) . '">' . htmlspecialchars($name) . '</a>';
        echo '</div>';
    }

    echo '<div class="nav-slider"></div>';
    echo '</li>';
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
    addEventListener("DOMContentLoaded", function() {
        const navSlider = document.querySelector(".nav-slider");
        const navLinks = document.querySelectorAll(".nav-link");
        const navContainer = document.querySelector(".navbar-nav");
        const navbarCollapse = document.querySelector(".navbar-collapse");
        const navbarToggler = document.getElementById("navbarToggler");
        
        // Vérifier si nous sommes sur la page du panel
        const isPanel = window.location.pathname.includes("/panel");
        
        // Fonction pour détecter si l\'écran est en mode mobile
        function isMobile() {
            return window.innerWidth <= 991;
        }

        // Fonction pour déplacer le slider sous le lien actif
        function moveSlider(link) {
            if (!isMobile() && navSlider) {
                navSlider.style.width = `${link.offsetWidth}px`;
                navSlider.style.left = `${link.offsetLeft}px`;
            }
        }

        // Fonction pour réinitialiser la position du slider
        function resetSlider() {
            if (!isMobile() && navSlider) {
                const activeLink = document.querySelector(".nav-link.active") || navLinks[0];
                moveSlider(activeLink);
            }
        }

        // Ajouter des écouteurs d\'événements pour chaque lien de navigation
        navLinks.forEach(link => {
            link.addEventListener("mouseenter", () => {
                if (!isPanel) {
                    moveSlider(link);
                }
            });
            link.addEventListener("click", (e) => {
                if (isMobile()) {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove("active"));
                    link.classList.add("active");
                    toggleNavbar(false);
                    setTimeout(() => {
                        window.location.href = link.href;
                    }, 300);
                }
            });
        });

        // Réinitialiser le slider lorsque la souris quitte la barre de navigation
        navContainer.addEventListener("mouseleave", () => {
            if (!isPanel) {
                resetSlider();
            }
        });

        // Réinitialiser le slider lors du chargement de la page et du redimensionnement de la fenêtre
        window.addEventListener("resize", () => {
            if (!isPanel) {
                resetSlider();
            }
        });

        // Initialiser la position du slider
        if (!isPanel) {
            resetSlider();
        } else {
            // Si on est sur la page du panel, positionner le slider sous l\'onglet "Panel" et le figer
            const panelLink = document.querySelector(`.nav-link[href="/panel"]`);
            if (panelLink) {
                moveSlider(panelLink);
            }
        }

        // Gestion de la recherche
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

        // Fonction pour basculer l\'état de la navbar (ouverte/fermée)
        function toggleNavbar(show) {
            if (show === undefined) {
                show = !navbarCollapse.classList.contains("show");
            }
            
            navbarCollapse.style.transition = "transform 0.3s ease-in-out";
            
            if (show) {
                navbarCollapse.classList.add("show");
                setTimeout(() => {
                    navbarCollapse.style.transform = "translateY(0)";
                }, 10);
            } else {
                navbarCollapse.style.transform = "translateY(-100%)";
                navbarCollapse.addEventListener("transitionend", function handler() {
                    navbarCollapse.classList.remove("show");
                    navbarCollapse.removeEventListener("transitionend", handler);
                });
            }
            
            navbarToggler.setAttribute("aria-expanded", show);
        }

        // Gestion du bouton de bascule de la navbar sur mobile
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation(); // Empêche la propagation de l\'événement
                toggleNavbar();
            });
        }

        // Fermeture du menu mobile en cliquant à l\'extérieur
        document.addEventListener("click", function(e) {
            if (isMobile() && navbarCollapse.classList.contains("show") && 
                !navbarCollapse.contains(e.target) && 
                !navbarToggler.contains(e.target)) {
                toggleNavbar(false);
            }
        });

        // Configuration de l\'animation pour mobile
        function setupMobileAnimation() {
            if (isMobile()) {
                navbarCollapse.style.transition = "transform 0.3s ease-in-out";
                navbarCollapse.style.transform = "translateY(-100%)";
            } else {
                navbarCollapse.style.transition = "";
                navbarCollapse.style.transform = "";
                navbarCollapse.classList.remove("show");
            }
        }

        // Initialisation de l\'animation mobile et gestion du redimensionnement
        setupMobileAnimation();
        window.addEventListener("resize", setupMobileAnimation);
    });
    </script>
    ';
}
?>
