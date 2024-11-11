<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;
use Config\Database;
use App\Controller\AdminController;
use App\Controller\SecurityController;
use App\Domain\Repository\TableRepository;

$Utils = new Utils();
$navLinks = $Utils->getNavLinks();

// Obtenir la connexion PDO via le singleton Database
$pdo = Database::getInstance()->getConnection();

// Créer les dépendances nécessaires
$securityController = new SecurityController();
$tableRepository = new TableRepository($pdo);
$adminController = new AdminController($securityController, $tableRepository);

$error = '';

// Forcer HTTPS
// if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
//     header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
// }

// Générer ou récupérer le jeton CSRF
$csrf_token = $securityController->generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$securityController->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Erreur de validation du formulaire. Veuillez réessayer.";
    } else {
        $result = $securityController->attemptLogin(
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($result['success']) {
            header('Location: panel');
            exit;
        }

        $error = $result['error'];
    }

    $csrf_token = $securityController->refreshCsrfToken();
}
?>
<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<head>
    <meta
        name="description"
        content="Page de connexion à PageBleue.">
    <title>Connexion - <?php echo htmlspecialchars($siteName); ?></title>
    <style>
        :root {
            --primary-blue: #007bff;
            --secondary-blue: #4dabf7;
            --light-blue: #e7f5ff;
            --dark-blue: #004085;
        }

        html,
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body {
            background-color: white;
            color: #333;
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            /* Centrer verticalement */
            justify-content: center;
            /* Centrer horizontalement */
            margin: 0;
            /* Enlever les marges par défaut */
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <div class="container-fluid d-flex justify-content-center align-items-center flex-grow-1">
        <div 
            class="col-md-8 col-lg-6 col-xl-4 p-4" 
            style="
                background-color: white; 
                border-radius: 8px; 
                box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
            "
        >
            <h2 class="mb-4">Connexion</h2>
            <?php if ($error) : ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" id="username" name="username" required maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>

</html>
