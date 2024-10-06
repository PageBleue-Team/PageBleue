<?php
require_once '../config.php';

// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();
includeWidget('footer');

$pdo = getDbConnection();

// Vérifier si un ID d'entreprise est fourni
$showEnterprise = isset($_GET['show']) ? intval($_GET['show']) : null;

if ($showEnterprise) {
    // Récupérer les détails de l'entreprise spécifique
    $stmt = $pdo->prepare("SELECT e.*, l.adresse AS localisation_adresse, l.transports, l.carte_interactive 
                           FROM Entreprise e 
                           LEFT JOIN Localisation l ON e.id = l.entreprise_id 
                           WHERE e.id = ?");
    $stmt->execute([$showEnterprise]);
    $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les stages associés à cette entreprise
    $stmtStages = $pdo->prepare("SELECT s.*, e.nom AS eleve_nom, e.prenom AS eleve_prenom 
                                 FROM Stage s 
                                 JOIN Eleve e ON s.eleve_id = e.id 
                                 WHERE s.entreprise_id = ?");
    $stmtStages->execute([$showEnterprise]);
    $stages = $stmtStages->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les tuteurs associés à cette entreprise
    $stmtTuteurs = $pdo->prepare("SELECT * FROM Tuteur WHERE entreprise_id = ?");
    $stmtTuteurs->execute([$showEnterprise]);
    $tuteurs = $stmtTuteurs->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Pagination pour la liste des entreprises
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->query("SELECT COUNT(*) FROM Entreprise");
    $total_rows = $stmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    $stmt = $pdo->prepare("SELECT * FROM Entreprise LIMIT $offset, $limit");
    $stmt->execute();
    $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showEnterprise ? htmlspecialchars(nullSafe($enterprise['nom'])) : 'Liste des entreprises'; ?> - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .enterprise-logo {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        .star-rating {
            color: #ffc107;
        }
        .card {
            position: relative;
            overflow: hidden;
        }
        .lasalle-badge {
            position: absolute;
            top: 0;
            right: 0;
            width: 40px;
            height: 40px;
            background-color: gold;
            border-radius: 0 0 0 100%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            padding: 5px;
        }
        .lasalle-badge i {
            color: white;
            font-size: 20px;
        }        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    
    <div class="container mt-5" style="padding-top: 60px;">
        <?php if ($showEnterprise && $enterprise): ?>
            <h1 class="mb-4"><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></h1>
            <div class="row">
                <div class="col-md-2">
                    <img src="<?php echo htmlspecialchars(nullSafe($enterprise['logo'], '/img/default-logo.png')); ?>" alt="Logo <?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?>" class="img-fluid enterprise-logo mb-3">
                </div>
                <div class="col-md-8">
                    <h2 class="section-title">Informations générales</h2>
                    <div class="info-item"><span class="info-label">Secteur:</span> <?php echo htmlspecialchars(nullSafe($enterprise['secteur'])); ?></div>
                    <div class="info-item"><span class="info-label">Taille:</span> <?php echo htmlspecialchars(nullSafe($enterprise['taille'])); ?></div>
                    <div class="info-item"><span class="info-label">Adresse:</span> <?php echo htmlspecialchars(nullSafe($enterprise['adresse'])); ?></div>
                    <div class="info-item"><span class="info-label">Téléphone:</span> <?php echo htmlspecialchars(nullSafe($enterprise['telephone'])); ?></div>
                    <div class="info-item"><span class="info-label">Email:</span> <?php echo htmlspecialchars(nullSafe($enterprise['email'])); ?></div>
                    <div class="info-item"><span class="info-label">Site web:</span> <a href="<?php echo htmlspecialchars(nullSafe($enterprise['site_web'], '#')); ?>" target="_blank"><?php echo htmlspecialchars(nullSafe($enterprise['site_web'])); ?></a></div>
                </div>
            </div>

            <div class="mt-4">
                <h2 class="section-title">Localisation et accès</h2>
                <div class="info-item"><span class="info-label">Adresse détaillée:</span> <?php echo htmlspecialchars(nullSafe($enterprise['localisation_adresse'])); ?></div>
                <div class="info-item"><span class="info-label">Transports:</span> <?php echo htmlspecialchars(nullSafe($enterprise['transports'])); ?></div>
                <div class="info-item"><span class="info-label">Proximité de l'établissement:</span> <?php echo htmlspecialchars(nullSafe($enterprise['proximite_etablissement'])); ?></div>
                <div class="info-item"><span class="info-label">Accès:</span> <?php echo htmlspecialchars(nullSafe($enterprise['acces'])); ?></div>
                <?php if (!empty($enterprise['carte_interactive'])): ?>
                    <div class="mt-3">
                        <iframe src="<?php echo htmlspecialchars($enterprise['carte_interactive']); ?>" width="100%" height="300" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <h2 class="section-title">Informations pour les stagiaires</h2>
                <div class="info-item"><span class="info-label">Avantages pour les stagiaires:</span> <?php echo htmlspecialchars(nullSafe($enterprise['avantages_stagiaire'])); ?></div>
            </div>

            <?php if (!empty($tuteurs)): ?>
                <div class="mt-4">
                    <h2 class="section-title">Tuteurs</h2>
                    <?php foreach ($tuteurs as $tuteur): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tuteur['prenom'] . ' ' . $tuteur['nom']); ?></h5>
                                <p class="card-text"><strong>Contact:</strong> <?php echo htmlspecialchars(nullSafe($tuteur['contact'])); ?></p>
                                <p class="card-text"><strong>Expérience:</strong> <?php echo htmlspecialchars(nullSafe($tuteur['experience'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($stages)): ?>
                <div class="mt-4">
                    <h2 class="section-title">Stages précédents</h2>
                    <?php foreach ($stages as $stage): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Stage de <?php echo htmlspecialchars($stage['eleve_prenom'] . ' ' . $stage['eleve_nom']); ?></h5>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($stage['dates']); ?></p>
                                <p class="card-text"><strong>Missions:</strong> <?php echo htmlspecialchars(nullSafe($stage['missions'])); ?></p>
                                <p class="card-text"><strong>Niveau requis:</strong> <?php echo htmlspecialchars(nullSafe($stage['niveau_requis'])); ?></p>
                                <p class="card-text"><strong>Note d'expérience:</strong> <?php echo htmlspecialchars(nullSafe($stage['note_experience'], 'Non noté')); ?>/5</p>
                                <p class="card-text"><strong>Charge de travail:</strong> <?php echo htmlspecialchars(nullSafe($stage['charge_travail'])); ?></p>
                                <p class="card-text"><strong>Encadrement:</strong> <?php echo htmlspecialchars(nullSafe($stage['encadrement'])); ?></p>
                                <p class="card-text"><strong>Ambiance:</strong> <?php echo htmlspecialchars(nullSafe($stage['ambiance'])); ?></p>
                                <p class="card-text"><strong>Possibilité d'embauche:</strong> <?php echo $stage['possibilite_embauche'] ? 'Oui' : 'Non'; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="/list" class="btn btn-primary mt-3 mb-5">Retour à la liste</a>

        <?php else: ?>
            <h1>Liste des entreprises</h1>
            <?php if (!empty($enterprises)): ?>
                <div class="row">
                    <?php foreach ($enterprises as $enterprise): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <?php if ($enterprise['ancien_eleve_lasalle']): ?>
                                    <div class="lasalle-badge">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex">
                                    <img src="<?php echo htmlspecialchars(nullSafe($enterprise['logo'] . '../img/default-logo.png')); ?>" alt="Logo <?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?>" class="enterprise-logo mr-3">
                                    <div class="ml-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></h5>
                                        <p class="card-text">
                                            <!-- <?php
                                            $description = nullSafe($enterprise['description']);
                                            if ($description === "Non Renseigné") {
                                                echo "Aucune description disponible";
                                            } else {
                                                echo htmlspecialchars(mb_substr($description, 0, $descriptionLength)) . (mb_strlen($description) > $descriptionLength ? '...' : '');
                                            }
                                            ?> -->
                                        </p>
                                        <a href="/list?show=<?php echo $enterprise['id']; ?>" class="btn btn-primary">En savoir plus</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="/list?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php else: ?>
                <p>Aucune entreprise trouvée.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php renderFooter($siteName, $navLinks, $logoURL); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>