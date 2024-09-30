<?php
$siteName = "Page Bleue";
$siteDescription = "Bienvenue sur Page Bleue, un projet réalisé par trois lycéens de La Salle Avignon. Notre mission est de faciliter la recherche de Périodes de Formation en Milieu Professionnel (PFMP) tout en contribuant à l'obtention de notre baccalauréat.

Page Bleue est une plateforme conçue pour mettre en relation les étudiants à la recherche de stages avec des entreprises pour le CIEL.

Nos objectifs sont :
1. Mettre en avant les entreprises qui connaissent La Salle.
2. Faciliter les connexions entre les étudiants et les entreprises.

Rejoignez-nous dans cette aventure pour façonner l'avenir de la formation professionnelle !";

$navLinks = [
    "Accueil" => "#",
    "Entreprises" => "#entreprises",
    "À Propos" => "#about"
];

$dbError = false;
$errorMessage = "";

// Utilisation des secrets GitHub pour la connexion à la base de données
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'username';
$dbPass = getenv('DB_PASS') ?: 'password';
$dbName = getenv('DB_NAME') ?: 'database_name';

try {
    $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($db->connect_error) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    $query = "SELECT id, nom, description, url_logo FROM entreprises ORDER BY RAND() LIMIT 9";
    $result = $db->query($query);
    if (!$result) {
        throw new Exception("Erreur lors de la récupération des données");
    }
    $enterprises = $result->fetch_all(MYSQLI_ASSOC);

    $db->close();
} catch (Exception $e) {
    $dbError = true;
    $errorMessage = $e->getMessage();
    $enterprises = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .navbar, footer {
            background-color: var(--primary-blue) !important;
            color: white;
        }
        .navbar-light .navbar-brand,
        .navbar-light .navbar-nav .nav-link,
        footer a {
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
            flex-shrink: 0;
            padding: 20px 0;
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
    </style>
</head>
<body>
    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="#"><?php echo htmlspecialchars($siteName); ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav position-relative">
                        <?php foreach ($navLinks as $name => $link): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo htmlspecialchars($link); ?>" data-nav="<?php echo htmlspecialchars(strtolower($name)); ?>"><?php echo htmlspecialchars($name); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <div class="nav-slider"></div>
                    </ul>
                </div>
            </div>
        </nav>

        <?php if ($dbError): ?>
        <div class="container mt-3">
            <div class="error-banner">
                <strong>Erreur :</strong> Le site rencontre actuellement des difficultés techniques. <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mt-5" id="entreprises">
            <h2 class="section-title">Entreprises</h2>
            <div class="section-content">
                <?php if (!$dbError && !empty($enterprises)): ?>
                    <!-- Le code du carousel reste inchangé -->
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
    </div>

    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="footer-logo"><?php echo htmlspecialchars($siteName); ?></div>
                    <div class="footer-tagline">Par Florian, Samuel et Benjamin avec le ❤️</div>
                </div>
                <div class="col-md-4 text-center">
                    <img src="chemin/vers/logo-la-salle-avignon.png" alt="Logo La Salle Avignon" class="la-salle-logo">
                </div>
                <div class="col-md-4 text-end">
                    <ul class="list-unstyled">
                        <?php foreach ($navLinks as $name => $link): ?>
                            <li><a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($name); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <div class="background-animation">
        <?php for ($i = 0; $i < 15; $i++): ?>
            <span style="left: <?php echo rand(0, 100); ?>%; top: <?php echo rand(0, 100); ?>%; animation-delay: -<?php echo rand(0, 25); ?>s; border-color: <?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>;"></span>
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
    });
    </script>
</body>
</html>