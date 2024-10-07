<?php
// Définir le chemin racine du projet
define('ROOT_PATH', __DIR__);

require ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Démarrer la session
session_start();

// Fonction pour vérifier si l'utilisateur est connecté en tant qu'admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Fonction pour se connecter en tant qu'admin
function adminLogin($username, $password) {
    if ($username === $_ENV['ADMIN_USERNAME'] && password_verify($password, $_ENV['ADMIN_PASSWORD_HASH'])) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

// Fonction pour se déconnecter
function adminLogout() {
    $_SESSION = array();
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: /#');
    exit();
}

// Connexion à la base de données (singleton)
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Échec de la connexion à la base de données : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Fonction pour obtenir la connexion à la base de données
function getDbConnection() {
    return Database::getInstance()->getConnection();
}

# Fonction log connexion Admin
function logLoginAttempt($username, $success) {
    global $pdo;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Récupérer l'ID de l'utilisateur s'il existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user ? $user['id'] : null;
    
    // Insérer le log
    $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, username, ip_address, user_agent, success) VALUES (:user_id, :username, :ip_address, :user_agent, :success)");
    $stmt->execute([
        'user_id' => $user_id,
        'username' => $username,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'success' => $success ? 1 : 0
    ]);
    
    // Mettre à jour les informations de l'utilisateur si la connexion a réussi
    if ($success && $user_id) {
        $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP, login_attempts = 0 WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
    }
}

// Autres variables globales utiles
$siteName = $_ENV['WEBSITE'];
$cacheDir = ROOT_PATH . '/cache/enterprises/';
$logoURL = $_ENV['LASALLE_LOGO_URL'];
$descriptionLength = isset($_ENV['DESCRIPTION_LENGTH']) ? intval($_ENV['DESCRIPTION_LENGTH']) : 250;

// Fonction pour inclure les fichiers de manière sûre
function safeInclude($filePath) {
    $fullPath = ROOT_PATH . '/' . ltrim($filePath, '/');
    if (file_exists($fullPath)) {
        return require_once $fullPath;
    } else {
        throw new Exception("File not found: $filePath");
    }
}

// Fonction pour inclure les widgets
function includeWidget($widgetName) {
    safeInclude("widgets/$widgetName.php");
}

# En cas d'entrée NULL ou vide dans la BDD
function nullSafe($value, $default = "Non Renseigné") {
    return $value !== null && $value !== '' ? $value : $default;
}

// Assurez-vous que le répertoire de cache existe
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}