<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

// Configuration du site
$siteName = "Page Bleue";
$siteDescription = "Bienvenue sur Page Bleue, un projet réalisé par trois lycéens de La Salle Avignon. Notre mission est de faciliter la recherche de Périodes de Formation en Milieu Professionnel (PFMP) tout en contribuant à l'obtention de notre baccalauréat.

Page Bleue est une plateforme conçue pour mettre en relation les étudiants à la recherche de stages avec des entreprises pour le CIEL.

Nos objectifs sont :
1. Mettre en avant les entreprises qui connaissent La Salle.
2. Faciliter les connexions entre les étudiants et les entreprises.

Rejoignez-nous dans cette aventure pour façonner l'avenir de la formation professionnelle !";

$navLinks = [
    "Accueil" => "#",
    "Entreprises" => "entreprises.php",
    "À Propos" => "#about",
    "Formulaire" => "formulaire.php"
];

// Ajout du lien Admin si l'utilisateur est connecté en tant qu'admin
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    $navLinks["Admin"] = "admin.php";
}

$dbError = false;
$errorMessage = "";

// Utilisation des variables d'environnement pour la connexion à la base de données
$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbName = $_ENV['DB_NAME'];

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fonction de recherche d'entreprises
    function searchEnterprises($search) {
        global $db;
        $query = "SELECT * FROM pb_entreprises WHERE nom LIKE :search OR description LIKE :search LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute(['search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Traitement de la recherche
    $searchResults = [];
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $searchResults = searchEnterprises($search);
    }

    // Récupération des entreprises aléatoires pour la page d'accueil
    $query = "SELECT * FROM pb_entreprises ORDER BY RAND() LIMIT 12";
    $stmt = $db->query($query);
    $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dbError = true;
    $errorMessage = "Erreur de connexion à la base de données : " . $e->getMessage();
    $enterprises = [];
    $searchResults = [];
}
?>
<!DOCTYPE html>
<html lang="FR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Pour l'icone loupe -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #007bff;
            --secondary-blue: #4dabf7;
            --light-blue: #e7f5ff;
            --dark-blue: #004085;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: white;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }
        .content {
            flex: 1 0 auto;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .navbar {
            background-color: var(--primary-blue) !important;
            color: white;
        }
        .navbar-light .navbar-brand,
        .navbar-light .navbar-nav .nav-link {
            color: white !important;
        }
        .section-title {
            color: var(--primary-blue);
            text-align: left;
            margin-bottom: 2rem;
            margin-left: 5%;
        }
        .section-content {
            margin-left: 10%;
            margin-right: 10%;
        }
        footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 20px 0;
            width: 100%;
            margin-top: 20px; /* Ajoutez une marge en haut si nécessaire */
        }
        .footer-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .footer-tagline {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .error-banner {
            background-color: #dc3545;
            color: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2;
        }
        .background-animation span {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 2px solid var(--primary-blue);
            animation: move 25s infinite;
        }
        @keyframes move {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            100% {
                transform: translate(-50%, -50%) rotate(360deg) scale(0.5);
            }
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
        .la-salle-logo {
            max-height: 50px;
            margin-right: 15px;
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
    <div class="content" style="padding-top: 70px;">
        <?php if ($dbError): ?>
        <div class="container mt-3">
            <div class="error-banner">
                <strong>Erreur :</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($searchResults)): ?>
            <div class="container mt-4">
                <h2>Résultats de la recherche</h2>
                <?php foreach ($searchResults as $enterprise): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($enterprise['nom']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($enterprise['description']); ?></p>
                            <p>Adresse: <?php echo htmlspecialchars($enterprise['adresse']); ?></p>
                            <p>SIRET: <?php echo htmlspecialchars($enterprise['siret']); ?></p>
                            <p>Note moyenne: <?php echo number_format($enterprise['note_moyenne'], 1); ?>/5</p>
                            <p>Ancien élève de La Salle: <?php echo $enterprise['ancien_eleve_lasalle'] ? 'Oui' : 'Non'; ?></p>
                            <p>Site web: <a href="<?php echo htmlspecialchars($enterprise['site_web']); ?>" target="_blank"><?php echo htmlspecialchars($enterprise['site_web']); ?></a></p>
                            <p>Contact: <?php echo htmlspecialchars($enterprise['contact_nom']); ?> (<?php echo $enterprise['contact_verifie'] ? 'Vérifié' : 'Non vérifié'; ?>)</p>
                            <p>Type de travail: <?php echo htmlspecialchars($enterprise['type_travail']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="container mt-5" id="entreprises">
                <h2 class="section-title">Entreprises</h2>
                <div class="section-content">
                    <?php if (!$dbError && !empty($enterprises)): ?>
                        <div class="row">
                            <?php foreach ($enterprises as $enterprise): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="<?php echo htmlspecialchars($enterprise['logo_url']); ?>" class="card-img-top" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($enterprise['nom']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars(substr($enterprise['description'], 0, 100)) . '...'; ?></p>
                                            <a href="#" class="btn btn-primary">En savoir plus</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Nous sommes désolés, les informations sur les entreprises ne sont pas disponibles pour le moment. Veuillez réessayer ultérieurement.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container mt-5 mb-5" id="about">
                <h2 class="section-title">À Propos de Nous</h2>
                <div class="section-content">
                    <p><?php echo nl2br(htmlspecialchars($siteDescription)); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="footer-logo"><?php echo htmlspecialchars($siteName); ?></div>
                    <div class="footer-tagline">Par Florian, Samuel et Benjamin avec le ❤️</div>
                </div>
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars(getenv('LASALLE_LOGO_URL') ?: 'chemin/vers/logo-par-defaut.png'); ?>" alt="Logo La Salle Avignon" class="la-salle-logo">
                </div>
                <div class="col-md-4 text-end">
                    <ul class="list-unstyled">
                        <?php foreach ($navLinks as $name => $link): ?>
                            <li><a href="<?php echo htmlspecialchars($link); ?>" class="text-white"><?php echo htmlspecialchars($name); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <div class="background-animation">
        <?php for ($i = 0; $i < 15; $i++): ?>
            <span style="left: <?php echo rand(0, 100); ?>%; top: <?php echo rand(0, 100); ?>%; animation-delay: -<?php echo rand(0, 25); ?>s;"></span>
        <?php endfor; ?>
    </div>

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
                                window.location.href = `enterprise.php?id=${item.id}`;
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
                window.location.href = `index.php?search=${encodeURIComponent(searchTerm)}`;
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
</html>
