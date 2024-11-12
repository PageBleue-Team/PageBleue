<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;

use Config\{Utils, SiteConfig};

// Initialisation des configurations
SiteConfig::init();

// Récupération des données de navigation
$navLinks = Utils::getNavLinks();

// Récupération des données du site
$metaDescription = SiteConfig::$metaDescription;
$mainDescription = SiteConfig::$mainDescription;
$historyDescription = SiteConfig::$historyDescription;
$team = SiteConfig::$team;
?>

<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<body>
    <!-- Navbar -->
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <!-- Section Entreprises -->
    <section class="container">
        <h2 class="section-title">Entreprises</h2>
        <div class="enterprises-container">
            <?php include ROOT_PATH . '/templates/components/enterprise-card.php'; ?>
        </div>
    </section>

    <!-- Section À propos -->
    <section class="container" id="aboutus">
        <h2 class="section-title" style="padding-top: 10px;">À Propos de nous</h2>
        <div class="section-content">
            <p><?php echo nl2br(htmlspecialchars($mainDescription)); ?></p>
        </div>
    </section>

    <!-- Section Histoire -->
    <section class="container" id="story">
        <h2 class="section-title">Notre Histoire</h2>
        <div class="section-content">
            <p><?php echo nl2br(htmlspecialchars($historyDescription)); ?></p>
        </div>
    </section>

    <!-- Section équipe -->
    <section class="container" id="team">
        <div class="team-section mt-4">
            <h3 class="h5 text-primary mb-3">Notre équipe :</h3>
            <div class="row">
                <?php foreach ($team as $member) : ?>
                    <div class="col-md-4 text-center mb-3">
                        <div class="team-member">
                            <i class="fas fa-user-circle fa-3x mb-2 text-primary"></i>
                            <h4 class="h6"><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="small text-muted">
                                            <?php echo htmlspecialchars($member['role']); ?><br>
                                Filière <?php echo htmlspecialchars($member['filiere']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>

    <!-- Scripts -->
    <script type="application/javascript" src="/public/assets/js/home.js"></script>
</body>

</html>