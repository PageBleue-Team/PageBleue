<?php
require_once '../config.php';

// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();
includeWidget('footer');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Formulaire</title>
    <meta
      name="description"
      content="PageBleue, page de formulaire pour l'ajout d'entreprises à la liste de PageBleue.">
    <style>
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>
    <div class="container mt-5" style="padding-top: 60px;">
      <h2 class="mb-4">Arrive bientôt...</h2>
    </div>
    <?php renderFooter($siteName, $navLinks, $logoURL); ?>
</body>
</html>
