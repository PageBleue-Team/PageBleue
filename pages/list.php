<?php
require_once '../vendor/autoload.php';
require_once '../widgets/navbar.php';
require_once '../widgets/footer.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$siteName = $_ENV['WEBSITE'];
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$descriptionLength = isset($_ENV['DESCRIPTION_LENGTH']) ? intval($_ENV['DESCRIPTION_LENGTH']) : 250;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion: " . $e->getMessage());
}

$navLinks = getNavLinks();

// Fonction utilitaire pour gérer les valeurs NULL
function nullSafe($value, $default = "Non Renseigné") {
    return $value !== null && $value !== '' ? $value : $default;
}

// Vérifier si un ID d'entreprise est fourni
$showEnterprise = isset($_GET['show']) ? intval($_GET['show']) : null;

if ($showEnterprise) {
    // Récupérer les détails de l'entreprise spécifique
    $stmt = $pdo->prepare("SELECT * FROM ENTREPRISE WHERE id = ?");
    $stmt->execute([$showEnterprise]);
    $enterprise = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Pagination pour la liste des entreprises
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->query("SELECT COUNT(*) FROM ENTREPRISE");
    $total_rows = $stmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    $stmt = $pdo->prepare("SELECT * FROM ENTREPRISE LIMIT $offset, $limit");
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
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    
    <div class="container mt-3" style="padding-top: 60px;">
        <?php if ($showEnterprise && $enterprise): ?>
            <h1><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></h1>
            <div class="row">
                <div class="col-md-4">
                    <img src="<?php echo htmlspecialchars(nullSafe($enterprise['logo_url'], '/img/default-logo.png')); ?>" alt="Logo <?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?>" class="img-fluid enterprise-logo mb-3">
                </div>
                <div class="col-md-8">
                    <p><strong>Activité:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['activite'])); ?></p>
                    <p><strong>Secteurs:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['secteurs'])); ?></p>
                    <p><strong>Adresse:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['adresse'])); ?></p>
                    <p><strong>Site web:</strong> <a href="<?php echo htmlspecialchars(nullSafe($enterprise['site_web'], '#')); ?>" target="_blank"><?php echo htmlspecialchars(nullSafe($enterprise['site_web'], 'Non Renseigné')); ?></a></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['contact_nom'])); ?></p>
                    <p><strong>Téléphone:</strong> <?php echo htmlspecialchars(nullSafe($enterprise['contact_tel'], 'Non Renseigné')); ?></p>
                    <p><strong>Ancien élève de La Salle:</strong> <?php echo $enterprise['ancien_eleve_lasalle'] ? 'Oui' : 'Non'; ?></p>
                    <p><strong>Note moyenne:</strong> 
                        <span class="star-rating">
                            <?php
                            $rating = round(nullSafe($enterprise['note_moyenne'], 0));
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </span>
                        (<?php echo number_format(nullSafe($enterprise['note_moyenne'], 0), 1); ?>/5)
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars(nullSafe($enterprise['description'], 'Aucune description disponible.'))); ?></p>
            </div>
            <a href="/list" class="btn btn-primary mt-3">Retour à la liste</a>
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
                                    <img src="<?php echo htmlspecialchars(nullSafe($enterprise['logo_url'], '/img/default-logo.png')); ?>" alt="Logo <?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?>" class="enterprise-logo mr-3">
                                    <div class="ml-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></h5>
                                        <p class="card-text">
                                            <?php
                                            $description = nullSafe($enterprise['description']);
                                            if ($description === "Non Renseigné") {
                                                echo "Aucune description disponible";
                                            } else {
                                                echo htmlspecialchars(mb_substr($description, 0, $descriptionLength)) . (mb_strlen($description) > $descriptionLength ? '...' : '');
                                            }
                                            ?>
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