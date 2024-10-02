<?php

$siteName = $_ENV['WEBSITE'] ?? 'Default Site Name';

function getNavLinks() {
    $navLinks = [
        "Accueil" => "/#",
        "Entreprises" => "/list",
        "À Propos" => "/#about",
        "Formulaire" => "/form"
    ];

    // Ajout du lien Admin si l'utilisateur est connecté en tant qu'admin
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

    // Inclusion de Bootstrap et Font Awesome
    echo '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    ';

    // Styles spécifiques à la navbar
    echo '
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
            overflow: hidden;
        }
        .nav-link::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .nav-slider {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            background-color: white;
            transition: all 0.3s ease;
        }
        .search-container {
            position: relative;
            width: 300px;
            z-index: 1000;
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const navSlider = document.querySelector(".nav-slider");
        const navLinks = document.querySelectorAll(".nav-link");

        function moveSlider(link) {
            navSlider.style.width = `${link.offsetWidth}px`;
            navSlider.style.left = `${link.offsetLeft}px`;
        }

        navLinks.forEach(link => {
            link.addEventListener("mouseenter", () => moveSlider(link));
            link.addEventListener("focus", () => moveSlider(link));
        });

        document.querySelector(".navbar-nav").addEventListener("mouseleave", () => {
            const activeLink = document.querySelector(".nav-link.active") || navLinks[0];
            moveSlider(activeLink);
        });

        const activeLink = document.querySelector(".nav-link.active") || navLinks[0];
        moveSlider(activeLink);

        // Gestionnaire de recherche
        const searchInput = document.getElementById("search-input");
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                performSearch();
            }
        });

        function performSearch() {
            const searchTerm = searchInput.value;
            if (searchTerm.length > 2) {
                window.location.href = `/?search=${encodeURIComponent(searchTerm)}`;
            }
        }
    });
    </script>
    ';
}
?>