<?php
// Load environment variables from the .env file
require_once '../vendor/autoload.php'; // Assuming you're using vlucas/phpdotenv or similar

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

// Database connection settings from .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

// Initialize connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Add the navbar
include './navbar.php';

// Initialiser le tableau de résultats
$results = [];

// Vérifiez si une requête de recherche est envoyée
if (isset($_GET['query'])) {
    // Nettoyez la requête de recherche
    $query = htmlspecialchars($_GET['query'], ENT_QUOTES, 'UTF-8');
    
    // Créer la requête SQL pour chercher dans la table ENTREPRISE
    $sql = "SELECT id, nom, adresse, activite, secteurs, site_web 
            FROM ENTREPRISE 
            WHERE nom LIKE ? 
            OR activite LIKE ? 
            OR secteurs LIKE ?";
    
    // Préparez la requête SQL
    if ($stmt = $conn->prepare($sql)) {
        // Lier les paramètres (recherche dans nom, activite, et secteurs)
        $searchTerm = "%" . $query . "%";
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        
        // Exécutez la requête
        $stmt->execute();
        
        // Obtenez les résultats
        $result = $stmt->get_result();
        
        // Récupérer les résultats sous forme de tableau associatif
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        
        // Fermer la requête
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche</title>
</head>
<body>
    <h1>Résultats de recherche</h1>
    
    <!-- Formulaire de recherche -->
    <form method="GET" action="search.php">
        <input type="text" name="query" placeholder="Rechercher des entreprises..." required>
        <button type="submit">Rechercher</button>
    </form>

    <!-- Afficher les résultats de recherche -->
    <?php if (isset($_GET['query'])): ?>
        <h2>Résultats pour "<?php echo $query; ?>"</h2>
        <?php if (count($results) > 0): ?>
            <ul>
                <?php foreach ($results as $result): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($result['nom'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        Activité: <?php echo htmlspecialchars($result['activite'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Secteurs: <?php echo htmlspecialchars($result['secteurs'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Adresse: <?php echo htmlspecialchars($result['adresse'], ENT_QUOTES, 'UTF-8'); ?><br>
                        Site web: <a href="<?php echo htmlspecialchars($result['site_web'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank"><?php echo htmlspecialchars($result['site_web'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun résultat trouvé.</p>
        <?php endif; ?>
    <?php endif; ?>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
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
</body>
</html>
