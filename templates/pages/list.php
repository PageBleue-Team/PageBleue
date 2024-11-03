<?php
require_once __DIR__ . '/../../config/config.php';

// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();
includeWidget('footer');

$pdo = getDbConnection();

// Vérifier si un ID d'entreprise est fourni
$showEnterprise = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($showEnterprise) {
    // Récupérer les détails de l'entreprise
    $stmt = $pdo->prepare("SELECT e.* , e.nom, e.adresse_id, e.contact_id, e.juridique_id, e.lasallien, e.checked
                           FROM Entreprises e
                           WHERE e.id = ?");
    $stmt->execute([$showEnterprise]);
    $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les détails de l'adresse
    $stmt = $pdo->prepare("SELECT a.* , a.rue, a.numero, a.complement, a.code_postal, a.commune, a.pays
                           FROM Adresse a
                           WHERE a.id = ?");
    $stmt->execute([$showEnterprise]);
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les détails Juridique
    $stmt = $pdo->prepare("SELECT j.* , j.SIREN, j.SIRET, j.creation, j.employés
                           FROM Juridique j
                           WHERE j.id = ?");
    $stmt->execute([$showEnterprise]);
    $juridique = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les détails de Contact
    $stmt = $pdo->prepare("SELECT c.* , c.mail, c.telephone, c.site_web
                           FROM Contact c
                           WHERE c.id = ?");
    $stmt->execute([$showEnterprise]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);



} else {
    // Pagination pour la liste des entreprises
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->query("SELECT COUNT(*) FROM Entreprises");
    $total_rows = $stmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    $stmt = $pdo->prepare("SELECT * FROM Entreprises LIMIT $offset, $limit");
    $stmt->execute();
    $enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta
      name="description"
      content="PageBleue, page de liste des entreprises référencées pour la recherche de Période de Formation.">
    <title><?php echo $showEnterprise ? htmlspecialchars(nullSafe($enterprise['nom'])) : 'Liste des entreprises'; ?> - <?php echo htmlspecialchars($siteName); ?></title>
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
        }
        .section-title {
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
        .card-link {
            color: inherit;
            text-decoration: none;
        }
        .card-link:hover {
            text-decoration: none;
        }
        .enterprise-logo-container {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
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
                    <img src="<?php echo !empty($enterprise['logo']) ? 'data:image/jpeg;base64,' . base64_encode($enterprise['logo']) : './images/logos/default.png'; ?>" class="img-fluid enterprise-logo mb-3" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
                </div>
                <div class="col-md-8">
                    <h2 class="section-title">Informations générales</h2>
                    <div class="info-item"><span class="info-label">Téléphone :</span> <?php echo htmlspecialchars(nullSafe($contact['telephone'])); ?></div>
                    <div class="info-item"><span class="info-label">Email :</span> <?php echo htmlspecialchars(nullSafe($contact['mail'])); ?></div>
                    <div class="info-item"><span class="info-label">Ville :</span> <?php echo htmlspecialchars(nullSafe($adresse['commune'])); ?></div>
                    <!-- Site Web check si renseigner -->
                    <div class="info-item">
                        <span class="info-label">Site web:</span>
                        <?php if (!empty($contact['site_web']) && $contact['site_web'] !== 'Non Renseigné'): ?>
                            <a href="<?php echo htmlspecialchars($contact['site_web']); ?>" target="_blank"><?php echo htmlspecialchars($contact['site_web']); ?></a>
                        <?php else: ?>
                            Non renseigné
                        <?php endif; ?>
                    </div>
                    <div class="info-item"><span class="info-label">Nb. Stagiaires pris :</span> <?php echo htmlspecialchars(nullSafe($contact['telephone'])); ?></div>
                </div>
            </div>
            
            <div class="mt-4">
                <h2 class="section-title">Localisation</h2>
                <div class="info-item"><span class="info-label">Adresse:</span> <?php echo htmlspecialchars(nullSafe($adresse['rue'])); ?></div>
                <!-- <div class="info-item"><span class="info-label">Proximité de l'établissement:</span></div> -->
                <?php if (!empty($enterprise['carte_interactive'])): ?>
                    <div class="mt-3">
                        <iframe src="<?php echo htmlspecialchars($enterprise['carte_interactive']); ?>" width="100%" height="300" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <h2 class="section-title">Juridique</h2>
                <div class="info-item"><span class="info-label">Date de création :</span> <?php echo htmlspecialchars(nullSafe($juridique['creation'])); ?></div>
                <div class="info-item"><span class="info-label">Forme juridique :</span> <?php echo htmlspecialchars(nullSafe($juridique['forme'])); ?></div>
                <div class="info-item"><span class="info-label">Activité (code NAF/APE) :</span> <?php echo htmlspecialchars(nullSafe($juridique['activite'])); ?></div>
                <div class="info-item"><span class="info-label">Activité principale :</span> <?php echo htmlspecialchars(nullSafe($juridique['activite_main'])); ?></div>

                <div class="info-item"><span class="info-label">Numéro SIREN :</span> <?php echo htmlspecialchars(nullSafe($juridique['SIREN'])); ?></div>
                <div class="info-item"><span class="info-label">Numéro SIRET :</span> <?php echo htmlspecialchars(nullSafe($juridique['SIRET'])); ?></div>
                <div class="info-item"><span class="info-label">Numéro RSC :</span> <?php echo htmlspecialchars(nullSafe($juridique['RSC'])); ?></div>
                <div class="info-item"><span class="info-label">Nombre d'employé(s) :</span> <?php echo htmlspecialchars(nullSafe($juridique['employés'])); ?></div>
            </div>

            <?php if (!empty($tuteurs)): ?>
                <div class="mt-4">
                    <h2 class="section-title">Tuteur(s)</h2>
                    <?php foreach ($tuteurs as $tuteur): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tuteur['nom'] . ' ' . $tuteur['prenom']); ?></h5>
                                <p class="card-text"><strong>Téléphone :</strong> <?php echo htmlspecialchars(nullSafe($tuteur['telephone'])); ?></p>
                                <p class="card-text"><strong>Mail :</strong> <?php echo htmlspecialchars(nullSafe($tuteur['mail'])); ?></p>
                                <p class="card-text"><strong>Poste :</strong> <?php echo htmlspecialchars(nullSafe($tuteur['poste'])); ?></p>
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
                            <a href="/list?id=<?php echo $enterprise['id']; ?>" class="card-link">
                                <div class="card">
                                    <?php if ($enterprise['lasallien']): ?>
                                        <div class="lasalle-badge">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex">
                                        <div class="enterprise-logo-container">
                                            <img src="<?php echo !empty($enterprise['logo']) ? 'data:image/jpeg;base64,' . base64_encode($enterprise['logo']) : '/images/logos/default.png'; ?>" class="enterprise-logo" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
                                        </div>
                                        <div>
                                            <h5 class="card-title"><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></h5>
                                            <p class="card-text">
                                                <?php
                                                    // Vérifiez si la clé "description" existe
                                                    $description = isset($enterprise['description']) ? htmlspecialchars(nullSafe($enterprise['description'])) : 'Non renseigné';
                                                    if ($description === "Non renseigné") {
                                                        echo "Aucune description disponible";
                                                    } else {
                                                        echo htmlspecialchars(mb_substr($description, 0, 150)) . (mb_strlen($description) > 150 ? '...' : '');
                                                    }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
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
</body>
</html>
