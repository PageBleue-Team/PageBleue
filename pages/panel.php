<?php
require_once '../config.php';

// Inclure les widgets nécessaires
includeWidget('navbar');
$navLinks = getNavLinks();
includeWidget('footer');

$pdo = getDbConnection();

// Vérification de l'authentification
if (!isAdminLoggedIn()) {
    // Rediriger vers la page de connexion
    header('Location: login');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    adminLogout();
}

$pdo = getDbConnection();

// Fonctions de gestion du cache
function invalidateEnterpriseCache($enterpriseId) {
    global $cacheDir;
    $cacheFile = $cacheDir . 'enterprise_' . $enterpriseId . '.cache';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

// Traitement des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';

    switch ($action) {
        case 'add':
        case 'edit':
            $id = $_POST['id'] ?? null;
            $data = $_POST;
            unset($data['action'], $data['table'], $data['id']);

            if ($action === 'add') {
                $columns = implode(', ', array_keys($data));
                $values = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO $table ($columns) VALUES ($values)";
            } else {
                $set = implode(', ', array_map(function ($key) {
                    return "$key = :$key";
                }, array_keys($data)));
                $sql = "UPDATE $table SET $set WHERE id = :id";
                $data['id'] = $id;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);

            if ($table === 'Entreprise') {
                invalidateEnterpriseCache($id ?? $pdo->lastInsertId());
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $sql = "DELETE FROM $table WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);

                if ($table === 'Entreprise') {
                    invalidateEnterpriseCache($id);
                }
            }
            break;
    }

    // Redirection pour éviter les soumissions multiples
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fonction pour récupérer les données d'une table
function getTableData($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$enterprises = getTableData($pdo, 'Entreprise');
$tuteurs = getTableData($pdo, 'Tuteur');
$eleves = getTableData($pdo, 'Eleve');
$stages = getTableData($pdo, 'Stage');
$activites = getTableData($pdo, 'Activite');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel d'administration - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .container-centered {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .panel-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            max-width: 100%;
        }

        @media (max-width: 1200px) {
            .panel-content {
                max-width: 100%;
            }
        }

        /* Styles pour les onglets */
        .nav-tabs {
            justify-content: center;
            margin-bottom: 20px;
        }

        /* Ajustement pour le contenu des onglets */
        .tab-content {
            width: 100%;
        }

        /* Style pour le conteneur principal du panel */
        .panel-container {
            padding-top: 80px; /* Ajustez cette valeur selon la hauteur de votre navbar */
        }
    </style>
</head>
<body>
    <?php renderNavbar($siteName); ?>

    <div class="container mt-5" style="padding-top: 60px;">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="mb-4">Panel d'administration</h1>
            <form method="POST" action="">
                <button type="submit" name="logout" class="btn btn-danger btn-lg">Déconnexion</button>
            </form>
            </div>
        </div>

        <div class="panel-content">
            <!-- Onglets pour les différentes tables -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="entreprises-tab" data-bs-toggle="tab" data-bs-target="#entreprises" type="button" role="tab" aria-controls="entreprises" aria-selected="true">Entreprises</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tuteurs-tab" data-bs-toggle="tab" data-bs-target="#tuteurs" type="button" role="tab" aria-controls="tuteurs" aria-selected="false">Tuteurs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="eleves-tab" data-bs-toggle="tab" data-bs-target="#eleves" type="button" role="tab" aria-controls="eleves" aria-selected="false">Élèves</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="stages-tab" data-bs-toggle="tab" data-bs-target="#stages" type="button" role="tab" aria-controls="stages" aria-selected="false">Stages</button>
                </li>
            </ul>

            <!-- Contenu des onglets -->
            <div class="tab-content" id="myTabContent">
                <!-- Onglet Entreprises -->
                <div class="tab-pane fade show active" id="entreprises" role="tabpanel" aria-labelledby="entreprises-tab">
                    <h2>Gestion des Entreprises</h2>
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEnterpriseModal">
                        Ajouter une entreprise
                    </button>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Secteur</th>
                                    <th>Addresse</th>
                                    <th>Telephone</th>
                                    <th>Email</th>
                                    <th>Site Web</th>
                                    <th>Ancien LaSallien</th>
                                    <th>Numéro Immat</th>
                                    <th>Taille</th>
                                    <th>Acces</th>
                                    <th>avantages</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enterprises as $enterprise): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['id'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['adresse'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['secteur'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['telephone'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['email'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['site_web'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['ancien_eleve_lasalle'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['numero_immatriculation'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['taille'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['acces'])); ?></td>
                                    <td><?php echo htmlspecialchars(nullSafe($enterprise['avantages_stagiaire'])); ?></td>

                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editEnterpriseModal<?php echo $enterprise['id']; ?>">Éditer</button>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="table" value="Entreprise">
                                            <input type="hidden" name="id" value="<?php echo $enterprise['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(`Êtes-vous sûr de vouloir supprimer l'entreprise <?php echo htmlspecialchars(nullSafe($enterprise['nom'])); ?> ?`);">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Onglets pour Tuteurs, Élèves et Stages (structure similaire à Entreprises) -->
                <!-- ... -->

            </div>
        </div>
    </div>

    <!-- Modals pour l'ajout et l'édition des entreprises -->
    <div class="modal fade" id="addEnterpriseModal" tabindex="-1" aria-labelledby="addEnterpriseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEnterpriseModalLabel">Ajouter une entreprise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="table" value="Entreprise">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="secteur" class="form-label">Secteur</label>
                            <input type="text" class="form-control" id="secteur" name="secteur">
                        </div>

                        <!-- Ajoutez d'autres champs ici -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach ($enterprises as $enterprise): ?>
<div class="modal fade" id="editEnterpriseModal<?php echo $enterprise['id']; ?>" tabindex="-1" aria-labelledby="editEnterpriseModalLabel<?php echo $enterprise['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEnterpriseModalLabel<?php echo $enterprise['id']; ?>">Éditer l'entreprise</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" onsubmit="return validateForm()">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="table" value="Entreprise">
                    <input type="hidden" name="id" value="<?php echo $enterprise['id']; ?>">
                    <div class="mb-3">
                        <label for="nom<?php echo $enterprise['id']; ?>" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom<?php echo $enterprise['id']; ?>" name="nom" value="<?php echo htmlspecialchars($enterprise['nom']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="secteur<?php echo $enterprise['id']; ?>" class="form-label">Secteur</label>
                        <select class="form-select" id="secteur<?php echo $enterprise['id']; ?>" name="secteur" required>
                            <option value="">Sélectionnez un secteur</option>
                            <?php foreach ($secteurs as $secteur): ?>
                                <option value="<?php echo htmlspecialchars($secteur); ?>" <?php echo ($activites['nom'] === $secteur) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($secteur); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="NULL">Non renseigné</option>
                        </select>
                    </div>
                    <!-- Ajoutez d'autres champs ici -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

    <?php renderFooter($siteName, $navLinks, $logoURL); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateForm() {
        // Obtenir les valeurs des champs
        var nom = document.getElementById("nom").value;
        var secteur = document.getElementById("secteur").value;

        // Vérifie si le champ nom est vide
        if (nom.trim() === "") {
            alert("Le nom est obligatoire !");
            return false; // Empêche l'envoi du formulaire
        }

        // Vérifie si le secteur est sélectionné
        if (secteur === "") {
            alert("Veuillez sélectionner un secteur !");
            return false; // Empêche l'envoi du formulaire
        }

        // Si tout est bon
        return true; // Permet l'envoi du formulaire
    }
    </script>
</body>
</html>