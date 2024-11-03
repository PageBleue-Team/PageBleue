<?php
// templates/pages/home.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle); ?></title>
    <meta name="google-site-verification" content="<?php echo htmlspecialchars($googleVerification); ?>" />
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #007bff;
            --secondary-blue: #0056b3;
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
            padding-top: 80px; /* Espace pour la navbar fixe */
        }

        /* Styles pour les sections */
        .section-title {
            color: var(--primary-blue);
            text-align: left;
            margin-bottom: 2rem;
            margin-left: 5%;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 0.5rem;
            width: fit-content;
        }

        .section-content {
            margin: 0 5%;
            line-height: 1.6;
            text-align: justify;
        }

        /* Styles pour les cartes d'entreprise */
        .enterprise-card {
            cursor: pointer;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .enterprise-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .card-img-top {
            width: 50% !important;
            height: auto;
            margin: 1rem auto !important;
            object-fit: contain;
            max-height: 150px;
        }

        .alumni-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary-blue);
            color: white;
            border-radius: 50%;
            padding: 8px;
            font-size: 1.2em;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .enterprise-card:hover .alumni-icon {
            transform: scale(1.1);
        }

        /* Animation de fond */
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
            pointer-events: none;
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
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translate(-50%, -50%) rotate(360deg) scale(0.5);
                opacity: 0;
            }
        }

        /* Styles pour les boutons */
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--secondary-blue);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue);
            transform: scale(1.05);
        }

        /* Styles pour les erreurs */
        .error-banner {
            background-color: #dc3545;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        }
    </style>
</head>

<body>
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <div class="content">
        <?php if ($dbError): ?>
            <div class="error-banner">
                <div class="container">
                    <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div><?php echo htmlspecialchars($errorMessage); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Section Entreprises -->
        <section class="container mt-4" id="entreprises">
            <h2 class="section-title">Entreprises partenaires</h2>
            <div class="section-content">
                <?php if (!$dbError && !empty($featuredEnterprises)): ?>
                    <div class="row">
                        <?php foreach ($featuredEnterprises as $enterprise): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card enterprise-card" onclick="window.location.href='/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>'">
                                    <?php if ($enterprise['lasallien']): ?>
                                        <div class="alumni-icon" title="Ancien élève de La Salle">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <img 
                                        src="<?php echo !empty($enterprise['logo']) 
                                            ? 'data:image/jpeg;base64,' . base64_encode($enterprise['logo']) 
                                            : '/assets/images/logos/default.png'; ?>" 
                                        class="card-img-top" 
                                        alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>"
                                    >
                                    
                                    <div class="card-body">
                                        <h5 class="card-title text-center"><?php echo htmlspecialchars($enterprise['nom']); ?></h5>
                                        <p class="card-text">
                                            <strong><i class="fas fa-map-marker-alt me-2"></i>Adresse:</strong><br>
                                            <?php
                                            $adresse = array_filter([
                                                $enterprise['numero'] ?? null,
                                                $enterprise['rue'] ?? null,
                                                $enterprise['complement'] ?? null,
                                                $enterprise['code_postal'] ?? null,
                                                $enterprise['ville'] ?? null
                                            ]);
                                            echo htmlspecialchars(implode(', ', $adresse));
                                            ?>
                                        </p>
                                        <div class="text-center">
                                            <a href="/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-info-circle me-2"></i>En savoir plus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="/list" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Voir toutes les entreprises
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Nous sommes désolés, les informations sur les entreprises ne sont pas disponibles pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section À propos -->
        <section class="container mt-5" id="story">
            <h2 class="section-title">À Propos de nous</h2>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($siteDescription)); ?></p>
            </div>
        </section>

        <!-- Section Histoire -->
        <section class="container mt-5" id="story2">
            <h2 class="section-title">Notre Histoire</h2>
            <div class="section-content">
                <p><?php echo nl2br(htmlspecialchars($siteHistory)); ?></p>
                
                <!-- Section équipe -->
                <div class="team-section mt-4">
                    <h3 class="h5 text-primary mb-3">Notre équipe :</h3>
                    <div class="row">
                        <?php foreach ($team as $member): ?>
                            <div class="col-md-4 text-center mb-3">
                                <div class="team-member">
                                    <i class="fas fa-user-circle fa-3x mb-2 text-primary"></i>
                                    <h4 class="h6"><?php echo htmlspecialchars($member['name']); ?></h4>
                                    <p class="small text-muted">
                                        <?php echo htmlspecialchars($member['role']); ?><br>
                                        Filière <?php echo htmlspecialchars($member['filiere']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>

    <!-- Animation de fond -->
    <div class="background-animation">
        <?php for ($i = 0; $i < 15; $i++): ?>
            <span style="
                left: <?php echo rand(0, 100); ?>%; 
                top: <?php echo rand(0, 100); ?>%; 
                animation-delay: -<?php echo rand(0, 25); ?>s;"></span>
        <?php endfor; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>