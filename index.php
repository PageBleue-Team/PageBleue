<?php
require_once 'config.php';

// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();  
includeWidget('footer');

// Le reste de votre code pour la page d'accueil
$pdo = getDbConnection();

// Exemple : Récupérer quelques entreprises pour la page d'accueil
$stmt = $pdo->query("SELECT * FROM Entreprise LIMIT 5");
$featuredEnterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configuration du site
$siteDescription = "Bienvenue sur Page Bleue, un projet réalisé par trois lycéens de La Salle Avignon. Notre mission est de faciliter la recherche de Périodes de Formation en Milieu Professionnel (PFMP) tout en contribuant à l'obtention de notre baccalauréat.

Nos objectifs sont :
1. Mettre en avant les entreprises qui connaissent La Salle.
2. Faciliter les connexions entre les étudiants et les entreprises.

Rejoignez-nous dans cette aventure pour façonner l'avenir de la formation professionnelle !";

$dbError = false;
$errorMessage = "";

try {
    // Fonction de recherche d'entreprises
    function searchEnterprises($search) {
        global $pdo;
        $query = "SELECT * FROM Entreprise WHERE nom LIKE :search OR description LIKE :search LIMIT 5";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['search' => "%$search%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        // Récupération des entreprises aléatoires pour la page d'accueil
        $stmt = $pdo->query("SELECT * FROM Entreprise LIMIT 5");
        $featuredEnterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
    <meta name="google-site-verification" content="zVGKj6huDXuEi5cwo2_ARItegfAwWpGP_IYLEbZltXA" />
    <meta
      name="description"
      content="PageBleue, lieu de référencement d'entreprises pour des recherches de Formations en Milieu Professionnel sur et autour d'Avignon.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .enterprise-card {
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }
        .enterprise-card:hover {
            transform: scale(1.05);
        }
        .alumni-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    <div class="content" style="padding-top: 60px;">
        <?php if ($dbError): ?>
            <div class="container mt-3">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </symbol>
                    </svg>
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                    <div><?php echo htmlspecialchars($errorMessage); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="container mt-2" id="entreprises">
            <h2 class="section-title">Entreprises</h2>
            <div class="section-content">
                <?php if (!$dbError && !empty($featuredEnterprises)): ?>
                    <div class="row">
                        <?php foreach ($featuredEnterprises as $enterprise): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card enterprise-card" onclick="window.location.href='/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>'">
                                    <?php if ($enterprise['ancien_eleve_lasalle']): ?>
                                        <div class="alumni-icon" title="Ancien élève de La Salle">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    <?php endif; ?>
                                    <img src="<?php echo !empty($enterprise['logo']) ? 'data:image/jpeg;base64,' . base64_encode($enterprise['logo']) : '/img/default-logo.png'; ?>" class="card-img-top" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($enterprise['nom']); ?></h5>
                                        <p class="card-text">
                                            <strong>Secteur:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['secteur'])); ?><br>
                                            <strong>Adresse:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['adresse'])); ?>
                                        </p>
                                        <a href="/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>" class="btn btn-primary">En savoir plus</a>
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

        <div class="container mt-3" id="story">
            <h2 class="section-title">Notre Histoire</h2>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($siteDescription)); ?></p>
            </div>
        </div>
    </div>

    <?php renderFooter($siteName, $navLinks, $logoURL); ?>

    <div class="background-animation">
        <?php for ($i = 0; $i < 15; $i++): ?>
            <span style="left: <?php echo rand(0, 100); ?>%; top: <?php echo rand(0, 100); ?>%; animation-delay: -<?php echo rand(0, 25); ?>s;"></span>
        <?php endfor; ?>
    </div>
</body>
</html>
