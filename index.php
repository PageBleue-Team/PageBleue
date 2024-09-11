<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta chatset="UFT-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Description site -->
        <meta name="description" content="EDIT MOI !">
        <!-- Auteurs -->
        <meta name="author" content="Florian Castaldo, Samuel François et Benjamin Bonardo">
        <!-- Nom sur Navigateur -->
        <title>Page Bleue - Accueil</title>
        <!-- Déinit le fichier CSS -->
        <link rel="stylesheet" href="css/<?php echo(pathinfo(__FILE__)['filename']); ?>.css">
        <!-- Bootstrap CSS -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->
    </head>
    <!-- Nav Bar -->
    <header class="p-2 mb-2">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                    <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap"><use xlink:href="#bootstrap"/></svg>
                </a>
                <ul class="nav nav-pills col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li class="nav-item"><a href="#" class="nav-link active">Accueil</a></li>
                    <li class="nav-item"><a href="/list" class="nav-link">Entreprises</a></li>
                    <li class="nav-item"><a href="/form" class="nav-link">Formulaire</a></li>
                </ul>
                
                <form class="form col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search">
                    <input type="search" class="form-control" placeholder="Recherche..." aria-label="Recherche">
                </form>
               
            </div>
        </div>
    </header>
    <body>
        <?php
        echo ("Page Bleue\n");
        ?>
    </body>
</html>