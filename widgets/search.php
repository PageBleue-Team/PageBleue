<?php
// Load environment variables from the .env file
require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

// Database connection settings from .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

// Initialize connection to the database using PDO for better security
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion: " . $e->getMessage());
}

// Add the navbar
require_once 'navbar.php';
$navLinks = getNavLinks();
require_once 'footer.php';


// Initialize the results array
$results = [];
$query = '';

// Check if a search query is sent
if (isset($_GET['query'])) {
    // Clean the search query
    $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Create the SQL query to search in the ENTREPRISE table
    $sql = "SELECT id, nom, adresse, activite, secteurs, site_web 
            FROM ENTREPRISE 
            WHERE nom LIKE :search 
            OR activite LIKE :search 
            OR secteurs LIKE :search";
    
    // Prepare the SQL query
    $stmt = $pdo->prepare($sql);
    
    // Bind the parameter
    $searchTerm = "%$query%";
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results as an associative array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Render the navbar
$siteName = $_ENV['WEBSITE'] ?? 'Default Site Name';
renderNavbar($siteName);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - <?php echo htmlspecialchars($siteName); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 80px auto 20px;
            padding: 20px;
        }
        h1, h2 {
            color: #007bff;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 70%;
            padding: 10px;
            margin-right: 10px;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Résultats de recherche</h1>
        
        <!-- Search form -->
        <form method="GET" action="search.php">
            <input type="text" name="query" placeholder="Rechercher des entreprises..." required value="<?php echo htmlspecialchars($query); ?>">
            <button type="submit">Rechercher</button>
        </form>

        <!-- Display search results -->
        <?php if (!empty($query)): ?>
            <h2>Résultats pour "<?php echo htmlspecialchars($query); ?>"</h2>
            <?php if (!empty($results)): ?>
                <ul>
                    <?php foreach ($results as $result): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($result['nom']); ?></strong><br>
                            Activité: <?php echo htmlspecialchars($result['activite']); ?><br>
                            Secteurs: <?php echo htmlspecialchars($result['secteurs']); ?><br>
                            Adresse: <?php echo htmlspecialchars($result['adresse']); ?><br>
                            Site web: <a href="<?php echo htmlspecialchars($result['site_web']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($result['site_web']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun résultat trouvé.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php renderFooter($siteName, $navLinks); ?>
</body>
</html>