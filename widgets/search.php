<?php
// Load environment variables from the .env file
require_once __DIR__ . '/../config.php';
$siteName = $_ENV['WEBSITE'];
require_once '../widgets/navbar.php';
require_once '../widgets/footer.php';


$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion: " . $e->getMessage());
}

$results = [];
$query = '';

if (isset($_GET['query'])) {
    $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $sql = "SELECT id, nom, adresse, activite, secteurs, site_web, logo_url, personne_contact, ancien_eleve_lasalle, note_moyenne_travail 
            FROM ENTREPRISE 
            WHERE nom LIKE :search 
            OR activite LIKE :search 
            OR secteurs LIKE :search";
    
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$navLinks = getNavLinks();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            padding-top: 60px;
        }
        .search-result {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .search-result .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        .search-result .content {
            flex: 1;
        }
        .lasalle-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: gold;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .star-rating {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    
    <div class="container mt-4">
        <h1>Résultats de recherche</h1>
        
        <form method="GET" action="search.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Rechercher des entreprises..." required value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>

        <?php if (!empty($query)): ?>
            <h2>Résultats pour "<?php echo htmlspecialchars($query); ?>"</h2>
            <?php if (!empty($results)): ?>
                <div class="row">
                    <?php foreach ($results as $index => $result): ?>
                        <div class="col-md-6 <?php echo ($index % 2 == 0 && $index == count($results) - 1) ? 'offset-md-3' : ''; ?>">
                            <div class="search-result position-relative">
                                <div class="d-flex p-3">
                                    <img src="<?php echo htmlspecialchars($result['logo_url']); ?>" alt="Logo <?php echo htmlspecialchars($result['nom']); ?>" class="logo me-3">
                                    <div class="content">
                                        <h3><?php echo htmlspecialchars($result['nom']); ?></h3>
                                        <p><strong>Activité:</strong> <?php echo htmlspecialchars($result['activite']); ?></p>
                                        <p><strong>Adresse:</strong> <?php echo htmlspecialchars($result['adresse']); ?></p>
                                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($result['personne_contact']); ?> - <?php echo htmlspecialchars($result['contact_tel']); ?></p>
                                        <div class="star-rating">
                                            <?php
                                            $rating = round($result['note_moyenne']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($result['ancien_eleve_lasalle']): ?>
                                    <div class="lasalle-badge">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php renderFooter($siteName, $navLinks, $logoURL); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>