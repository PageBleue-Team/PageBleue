<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="fr">
<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<style>
        /* Reset CSS complet */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        /* Reset des éléments HTML5 */
        article, aside, details, figcaption, figure,
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        /* Reset des listes */
        ol, ul {
            list-style: none;
        }
        /* Reset des liens */
        a {
            text-decoration: none;
            color: inherit;
        }
        /* Styles spécifiques à la page 404 avec préfixe unique */
        .error-404-page {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;  /* Fond sombre */
            color: #ffffff;             /* Texte blanc */
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            line-height: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1000;
        }
       
        .error-404-container {
            text-align: center;
            padding: 2.5rem;
            max-width: 600px;
            background: #2d2d2d;      /* Fond container plus clair que le background */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3); /* Ombre plus prononcée */
            margin: auto;
            position: relative;
            z-index: 1001;
            border: 1px solid #3d3d3d; /* Bordure subtile */
        }
       
        .error-404-code {
            font-size: 8rem;
            font-weight: bold;
            color: #ff6b6b;          /* Rouge plus doux */
            margin: 0;
            line-height: 1;
            text-shadow: 0 0 10px rgba(255,107,107,0.3); /* Glow effect */
        }
       
        .error-404-title {
            font-size: 2rem;
            margin: 1rem 0;
            font-weight: bold;
            color: #e0e0e0;          /* Blanc cassé */
        }
       
        .error-404-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #b0b0b0;          /* Gris clair */
        }
       
        .error-404-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #4a9eff;  /* Bleu plus vif */
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: normal;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.1); /* Bordure subtile */
            box-shadow: 0 2px 10px rgba(74,158,255,0.2); /* Glow autour du bouton */
        }
       
        .error-404-button:hover {
            background-color: #3d8dee;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74,158,255,0.3);
        }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const homeButton = document.querySelector('.error-404-button');
        homeButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.error-404-page').style.opacity = 0;
            setTimeout(function() {
                window.location.href = '/';
            }, 300);
        });
    });
</script>
<body>
    <div class="error-404-page">
        <div class="error-404-container">
            <h1 class="error-404-code">403</h1>
            <h2 class="error-404-title">Accès Interdit</h2>
            <p class="error-404-message">
                Désolé, vous n'avez pas les autorisations nécessaires pour accéder à cette page.
            </p>
            <a href="/" class="error-404-button">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
