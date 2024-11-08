<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;

$Utils = new Utils();

$navLinks = $Utils->getNavLinks();

use Config\SiteConfig;

Config\SiteConfig::init();

$SiteConfig = new SiteConfig();

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

    <!-- Entreprise Card -->
    <?php include ROOT_PATH . '/templates/components/enterprise-card.php'; ?>

    <!-- Section À propos -->
    <section class="container mt-5" id="story">
        <h2 class="section-title">À Propos de nous</h2>
        <div class="section-content">
            <p><?php echo nl2br(htmlspecialchars($mainDescription)); ?></p>
        </div>
    </section>

    <!-- Section Histoire -->
    <section class="container mt-5" id="story2">
        <h2 class="section-title">Notre Histoire</h2>
        <div class="section-content">
            <p><?php echo nl2br(htmlspecialchars($historyDescription)); ?></p>

            <!-- Section équipe -->
            <div class="team-section mt-4">
                <h3 class="h5 text-primary mb-3">Notre équipe :</h3>
                <div class="row">
                    <?php foreach ($team as $member): ?>
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
        </div>
    </section>
    </div>

    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>

    <!-- Animation de fond -->
    <div class="background-animation">
        <?php for ($i = 0; $i < 15; $i++): ?>
            <span style="
                left: <?php echo rand(0, 100); ?>%; 
                top: <?php echo rand(0, 100); ?>%; 
                animation-delay: -<?php echo rand(0, 25); ?>s;"></span>
        <?php endfor; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>