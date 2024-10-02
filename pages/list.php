<?php
// Initialize a variable for error message
$error_message = '';
require_once '../widgets/navbar.php';
$navLinks = getNavLinks();
require_once '../widgets/footer.php';

try {
    // Load environment variables and database connection
    require_once '../vendor/autoload.php';
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    $siteName = $_ENV['WEBSITE'];

    // Info connection à la DB
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    // Connexion à la DB
    $conn = new mysqli($host, $username, $password, $dbname);

    // Vérif si conneixon OK
    if ($conn->connect_error) {
        throw new Exception("Échec de la connexion: " . $conn->connect_error);
    }

    // Pagination logic (you can include the previous logic here)
    $limit = 10; // Nb d'entreprises/pages 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Nb total entreprises
    $sql_count = "SELECT COUNT(*) AS total FROM ENTREPRISE";
    $result_count = $conn->query($sql_count);
    $total_rows = $result_count->fetch_assoc()['total'];

    // Nb Total de pages
    $total_pages = ceil($total_rows / $limit);

    // Fetch companies for current page
    $sql = "SELECT id, nom, adresse, activite, secteurs, site_web 
            FROM ENTREPRISE 
            LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

} catch (Exception $e) {
    // Capture any exception and set the error message
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des entreprises</title>
    <style>
        /* Red-bordered error message box */
        .error-box {
            border: 2px solid red;
            background-color: #fdd;
            color: #900;
            padding: 10px;
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    <h1>Liste des entreprises</h1>

    <?php if (!empty($error_message)): ?>
        <!-- Display the error message in a red-bordered box -->
        <div class="error-box">
            <p>Une erreur s'est produite: <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php else: ?>
        <!-- Display the companies -->
        <?php if ($result->num_rows > 0): ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        Activité: <?php echo htmlspecialchars($row['activite'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Secteurs: <?php echo htmlspecialchars($row['secteurs'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Adresse: <?php echo htmlspecialchars($row['adresse'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Site web: <a href="<?php echo htmlspecialchars($row['site_web'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank"><?php echo htmlspecialchars($row['site_web'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Aucune entreprise trouvée.</p>
        <?php endif; ?>
        
        <!-- Pagination links -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Page précédente</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Page suivante &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php renderFooter($siteName, $navLinks); ?>
</body>
</html>

<?php
// Close the connection if it's established
if (isset($conn)) {
    $conn->close();
}
?>
