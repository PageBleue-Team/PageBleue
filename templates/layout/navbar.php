<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;
use App\Exception\DatabaseException;
use Config\SiteConfig;

$Utils = new Utils();
$navLinks = $Utils->getNavLinks();
$currentPage = $Utils->getCurrentPage();
$activePage = array_search($currentPage, $navLinks) ?: '';

SiteConfig::init(); // Initialisation si pas déjà fait
$siteName = SiteConfig::$siteName;

// Vérifier l'état de la connexion à la base de données
$dbError = false;
$errorMessage = '';
if (!Config\Database::getInstance()->isConnected()) {
    $dbError = true;
    $errorMessage = "Erreur de connexion à la base de données";
}
?>

<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<!-- Structure de la navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" 
           href="<?php echo htmlspecialchars($navLinks['Accueil']); ?>">
            <?php echo htmlspecialchars($siteName); ?>
        </a>
        
        <button 
            class="navbar-toggler" 
            id="navbarToggler" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNav" 
            aria-controls="navbarNav" 
            aria-expanded="false" 
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 position-relative">
                <?php foreach ($navLinks as $name => $link) : ?>
                    <li class="nav-item">
                        <a class="nav-link<?php echo ($activePage == $name) ? ' active' : ''; ?>
                            <?php echo ($name == "Panel") ? ' admin-link' : ''; ?>"
                           href="<?php echo htmlspecialchars($link); ?>"
                           data-nav="<?php echo htmlspecialchars(strtolower($name)); ?>">
                            <?php echo htmlspecialchars($name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <div class="nav-slider"></div>
            </ul>

            <div class="search-container">
                <input 
                    class="form-control search-input" 
                    type="search" 
                    placeholder="Rechercher une entreprise" 
                    aria-label="Search" 
                    id="search-input"
                >
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <!-- Message d'erreur de la base de données -->
    <?php if ($dbError) : ?>
        <div class="error-banner">
            <div class="container">
                <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div><?php echo htmlspecialchars($errorMessage); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</nav>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="application/javascript" src="/public/assets/js/navbar.js"></script>
