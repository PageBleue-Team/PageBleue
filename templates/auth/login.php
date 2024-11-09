<?php
if (!function_exists('safeInclude')) {
    require_once './../config/init.php';
}

use Config\Utils;

$Utils = new Utils();

$navLinks = $Utils->getNavLinks();

// Activer le mode débogage (à désactiver en production)
define('DEBUG_MODE', false);

// Fonction pour enregistrer les erreurs détaillées
function logDetailedError($message)
{
    $logFile = __DIR__ . '/../../var/logs/login_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    if (!file_put_contents($logFile, $logMessage, FILE_APPEND)) {
        error_log("Impossible d'écrire dans le fichier de log: $logFile");
    }
}

// Fonction pour afficher les erreurs de manière sécurisée
function displayError($publicMessage, $detailedMessage)
{
    global $error;
    $error = $publicMessage;
    if (DEBUG_MODE) {
        $error .= " (Debug: $detailedMessage)";
    }
    logDetailedError($detailedMessage);
}

// Forcer HTTPS
// if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
//     header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
// }

$error = '';
$pdo = getDbConnection();

// Générer ou récupérer le jeton CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        displayError("Erreur de validation du formulaire. Veuillez réessayer.", "CSRF token mismatch");
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validation des entrées
        if (empty($username) || empty($password) || strlen($username) > 50) {
            displayError("Nom d'utilisateur ou mot de passe invalide.", "Empty input or username too long");
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password, login_attempts, last_attempt_time FROM Users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Vérification du verrouillage de compte
                $lockout_time = 15 * 60; // 15 minutes
                $max_attempts = 5;

                if ($user && $user['login_attempts'] >= $max_attempts && time() - strtotime($user['last_attempt_time']) < $lockout_time) {
                    displayError("Compte temporairement verrouillé. Veuillez réessayer plus tard.", "Account locked due to excessive attempts");
                    logLoginAttempt($username, false);
                } elseif ($user) {
                    // Débogage : afficher le hash stocké
                    if (DEBUG_MODE) {
                        error_log("Stored hash: " . $user['password']);
                        error_log("Entered password: " . $password);
                    }

                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];

                        // Réinitialiser les tentatives de connexion
                        $stmt = $pdo->prepare("UPDATE Users SET login_attempts = 0 WHERE id = :id");
                        $stmt->execute(['id' => $user['id']]);

                        logLoginAttempt($username, true);
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];

                        // Régénérer l'ID de session pour prévenir la fixation de session
                        session_regenerate_id(true);

                        header('Location: panel');
                        exit;
                    } else {
                        // Incrémenter les tentatives de connexion
                        $stmt = $pdo->prepare("UPDATE Users SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE id = :id");
                        $stmt->execute(['id' => $user['id']]);
                        displayError("Nom d'utilisateur ou mot de passe incorrect.", "Password mismatch for user: $username");
                        logLoginAttempt($username, false);
                    }
                } else {
                    displayError("Nom d'utilisateur ou mot de passe incorrect.", "User not found: $username");
                    logLoginAttempt($username, false);
                }
            } catch (PDOException $e) {
                displayError("Une erreur est survenue. Veuillez réessayer plus tard.", "Database error: " . $e->getMessage());
            }
        }
    }

    // Régénérer le jeton CSRF après chaque tentative
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
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
        <div class="col-md-8 col-lg-6 col-xl-4 p-4" style="background-color: white; border-radius: 8px; box-shadow: 0px 0px 15px rgba(0,0,0,0.1);">
            <h2 class="mb-4">Connexion</h2>
            <?php if ($error): ?>
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
