<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;

try {
    $utils = new Utils();
} catch (Exception $e) {
    // Gérer l'erreur ou la logger
    die('Erreur de configuration');
}

$navLinks = $utils->getNavLinks();
?>

<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<body>
  <!-- Navbar -->
  <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

  <div class="container" style="margin: auto; vertical-align: middle; text-align: center;">
    <h2>Arrive bientôt...</h2>
  </div>

  <!-- Footer -->
  <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>

</html>
