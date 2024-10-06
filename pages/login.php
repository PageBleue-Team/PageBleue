<?php
require_once '../config.php';
// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();  
includeWidget('footer');

// Forcer HTTPS
 if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
     header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
     exit();
 }

$error = '';
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Fonction pour enregistrer les tentatives de connexion

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
     if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
         die("CSRF token validation failed");
     }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation des entrées
    if (empty($username) || empty($password) || strlen($username) > 50) {
        $error = "Nom d'utilisateur ou mot de passe invalide.";
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, password, login_attempts, last_attempt_time FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du verrouillage de compte
        $lockout_time = 15 * 60; // 15 minutes
        $max_attempts = 5;

        if ($user && $user['login_attempts'] >= $max_attempts && time() - strtotime($user['last_attempt_time']) < $lockout_time) {
            $error = "Compte temporairement verrouillé. Veuillez réessayer plus tard.";
            logLoginAttempt($username, false);
        } elseif ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Réinitialiser les tentatives de connexion
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE id = :id");
            $stmt->execute(['id' => $user['id']]);
            
            logLoginAttempt($username, true);
            header('Location: panel.php');
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
            if ($user) {
                // Incrémenter les tentatives de connexion
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);
            }
            logLoginAttempt($username, false);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007bff;
            --secondary-blue: #4dabf7;
            --light-blue: #e7f5ff;
            --dark-blue: #004085;
        }
        html, body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
    </style>
</head>

<body>
    <?php renderNavbar($siteName); ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mb-4">Connexion</h2>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
    </div>

    <?php renderFooter($siteName, $navLinks, $logoURL); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>