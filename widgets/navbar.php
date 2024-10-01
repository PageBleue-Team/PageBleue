<?php
$siteName = $_ENV['WEBSITE'];
$navLinks = [
    "Accueil" => "#",
    "Entreprises" => "/list",
    "À Propos" => "#about",
    "Formulaire" => "/form"
];

// Ajout du lien Admin si l'utilisateur est connecté en tant qu'admin
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    $navLinks["Panel"] = "admin";
}
?>
<head>
    <meta charset="UTF-8">
    <!-- Pour l'icone loupe -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            content: '';
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
        .search-results {
            position: absolute;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
        }
        .search-result-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-container {
            position: relative;
            width: 300px; /* Augmentez cette valeur pour agrandir le champ */
            z-index: 1000; /* Assurez-vous que c'est au-dessus des autres éléments */
        }
        .search-input {
            padding-right: 40px;
            width: 100%;
            height: 40px; /* Ajustez la hauteur si nécessaire */
        }
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            pointer-events: none; /* Permet de cliquer à travers l'icône */
        }

    </style>

</head>
<body>
    <!-- navbar.php -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container">
                <a class="navbar-brand" href="#"><?php echo htmlspecialchars($siteName); ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 position-relative">
                        <?php foreach ($navLinks as $name => $link): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo htmlspecialchars($link); ?>" data-nav="<?php echo htmlspecialchars(strtolower($name)); ?>"><?php echo htmlspecialchars($name); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <div class="nav-slider"></div>
                    </ul>
                    <div class="search-container">
                        <input class="form-control search-input" type="search" placeholder="Rechercher une entreprise" aria-label="Search" id="search-input">
                        <i class="fas fa-search search-icon"></i>
                </div>
                </div>
            </div>
        </nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navSlider = document.querySelector('.nav-slider');
            const navLinks = document.querySelectorAll('.nav-link');

            function moveSlider(link) {
                navSlider.style.width = `${link.offsetWidth}px`;
                navSlider.style.left = `${link.offsetLeft}px`;
            }

            navLinks.forEach(link => {
                link.addEventListener('mouseenter', () => moveSlider(link));
                link.addEventListener('focus', () => moveSlider(link));
            });

            document.querySelector('.navbar-nav').addEventListener('mouseleave', () => {
                const activeLink = document.querySelector('.nav-link.active') || navLinks[0];
                moveSlider(activeLink);
            });

            const activeLink = document.querySelector('.nav-link.active') || navLinks[0];
            moveSlider(activeLink);

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    moveSlider(this);
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Recherche en temps réel
            const searchInput = document.getElementById('search-input');
            const searchResults = document.createElement('div');
            searchResults.className = 'search-results';
            searchInput.parentNode.appendChild(searchResults);

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value;
                if (searchTerm.length > 2) {
                    fetch(`search.php?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'search-result-item';
                                div.textContent = item.nom;
                                div.addEventListener('click', () => {
                                    window.location.href = `list.php?id=${item.id}`;
                                });
                                searchResults.appendChild(div);
                            });
                            searchResults.style.display = 'block';
                        });
                } else {
                    searchResults.style.display = 'none';
                }
            });

            // Fonction pour effectuer la recherche
            function performSearch() {
                const searchTerm = searchInput.value;
                if (searchTerm.length > 2) {
                    window.location.href = `?search=${encodeURIComponent(searchTerm)}`;
                }
            }

            // Gestionnaire d'événements pour la touche "Entrée"
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Gestionnaire d'événements pour le clic sur l'icône de loupe
            document.querySelector('.search-icon').addEventListener('click', performSearch);        

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        });
    </script>
</body>
