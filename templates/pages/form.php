<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;

$Utils = new Utils();

$navLinks = $Utils->getNavLinks();
?>

<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<body>
  <!-- Navbar -->
  <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

  <div class="container mt-5" style="padding-top: 60px;">
    <h2 class="mb-4">Arrive bientôt...</h2>
  </div>

  <!-- Footer -->
  <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>

</html>
