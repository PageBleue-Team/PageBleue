<?php
// Définir le chemin racine du projet
define('ROOT_PATH', __DIR__ . '/..');
define('CACHE_DIR', ROOT_PATH . '/var/cache');
define('LOGO_DIR', ROOT_PATH . '/public/assets/images/logos');

require ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Démarrer la session
session_start();

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
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
    }
    return $pdo;
}

# Fonction de cache
function cacheQuery($key, $callback, $ttl = 3600) {
    $cacheFile = CACHE_DIR . '/' . md5($key) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        return unserialize(file_get_contents($cacheFile));
    }
    $data = $callback();
    file_put_contents($cacheFile, serialize($data));
    return $data;
}

$cacheDir = CACHE_DIR;

// Vérification si le répertoire cache existe
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

# Fonction log connexion Admin
function logLoginAttempt($username, $success) {
    global $pdo;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Récupérer l'ID de l'utilisateur s'il existe
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
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
        $stmt = $pdo->prepare("UPDATE Users SET last_login = CURRENT_TIMESTAMP, login_attempts = 0 WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
    }
}

// Autres variables globales utiles
$siteName = $_ENV['WEBSITE'];
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

/**
 * Helper function pour inclure les widgets
 * @param string $name
 * @return void
 */
function includeWidget(string $name): void {
    $filePath = ROOT_PATH . "/templates/layout/{$name}.php";
    if (file_exists($filePath)) {
        require_once $filePath;
    } else {
        error_log("Widget non trouvé : {$name}");
    }
}

# En cas d'entrée NULL ou vide dans la BDD
function nullSafe($value, $default = "Non Renseigné") {
    return $value !== null && $value !== '' ? $value : $default;
}

// Obtenir le lien d'un logo d'entreprise
function getLogoUrl($entrepriseId) {
    $logoPath = LOGO_DIR . '/' . $entrepriseId . '.webp';
    return file_exists($logoPath) ? $logoPath : LOGO_DIR . '/default.png';
}
