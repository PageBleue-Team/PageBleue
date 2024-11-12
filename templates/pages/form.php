<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;

$utils = new Utils();
$navLinks = $utils->getNavLinks();

// Header
include ROOT_PATH . '/templates/layout/header.php';
?>

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
