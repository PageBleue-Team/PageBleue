<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

Use Config\SiteConfig;
$SiteConfig = new SiteConfig();
$SiteConfig->init();

$siteName = SiteConfig::$siteName;
$metaDescription = SiteConfig::$metaDescription;
$googleVerification = SiteConfig::$googleVerification;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?></title>
    <meta name="google-site-verification" content="<?php echo htmlspecialchars($googleVerification); ?>" />
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">

    <!-- Styles Externes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- CSS Root (Styles principaux) -->
    <link rel="stylesheet" href="/public/assets/css/main.css">
    <!-- Styles Navbar + Footer -->
    <link rel="stylesheet" href="/public/assets/css/footer.css">
    <link rel="stylesheet" href="/public/assets/css/navbar.css">

    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <link rel="manifest" href="/public/assets/images/favicons/site.webmanifest">
    <link rel="icon" href="/public/assets/images/favicons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/images/favicons/apple-touch-icon.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/public/assets/images/favicons/favicon.png">
</head>
