<?php
// Inclusion du fichier de configuration
require_once '../config.php';

// Importation des classes nécessaires pour le traitement des images
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// Inclusion des widgets nécessaires pour l'interface utilisateur
includeWidget('navbar');
$navLinks = getNavLinks();
includeWidget('footer');

// Établissement de la connexion à la base de données
$pdo = getDbConnection();

// Vérification de l'authentification de l'administrateur
if (!isAdminLoggedIn()) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
    header('Location: login');
    exit();
}

// Gestion de la déconnexion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    adminLogout();
}

// Fonction pour obtenir la structure d'une table
function getTableStructure($pdo, $tableName) {
    $stmt = $pdo->prepare("DESCRIBE $tableName");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir la liste des tables de la base de données
function getTables($pdo) {
    $stmt = $pdo->query("SHOW TABLES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fonction pour obtenir les données d'une table
function getTableData($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Liste des tables à ne pas afficher dans l'interface d'administration
$blacklistedTables = ['login_logs', 'users'];

// Récupération de toutes les tables sauf celles dans la liste noire
$tables = array_diff(getTables($pdo), $blacklistedTables);

// Création d'une instance de ImageManager avec le driver GD pour le traitement des images
$manager = new ImageManager(new Driver());

// Fonction de conversion en WebP
function convertToWebP($sourcePath, $destinationPath) {
    $info = getimagesize($sourcePath);
    $isTransparent = false;

    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            if (imagecolortransparent($image) >= 0 || (imagecolorat($image, 0, 0) >> 24) & 0x7F) {
                $isTransparent = true;
            }
            break;
        case 'image/webp':
            // Si l'image est déjà en WebP, on la copie simplement
            return copy($sourcePath, $destinationPath);
        default:
            return false;
    }

    if ($isTransparent) {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    // Convertir et sauvegarder en WebP
    $result = imagewebp($image, $destinationPath, 90);
    imagedestroy($image);

    return $result;
}

// Fonction pour gérer l'upload et la conversion du logo
function handleLogoUpload($file) {
    $tempFile = $file['tmp_name'];
    
    // Lire le contenu du fichier
    $imageData = file_get_contents($tempFile);
    
    // Convertir l'image en WebP
    $image = imagecreatefromstring($imageData);
    if ($image === false) {
        return false;
    }
    
    // Redimensionner l'image
    $resized = imagecreatetruecolor(300, 300);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, 300, 300, imagesx($image), imagesy($image));
    
    // Capturer la sortie de imagewebp
    ob_start();
    imagewebp($resized, null, 90);
    $webpData = ob_get_contents();
    ob_end_clean();
    
    imagedestroy($image);
    imagedestroy($resized);
    
    return $webpData;
}

// Traitement des actions POST (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    if (in_array($table, $tables)) {
        $structure = getTableStructure($pdo, $table);
        $data = $_POST;
        unset($data['action'], $data['table']);
        switch ($action) {
            case 'add':
            case 'edit':
                $id = $_POST['id'] ?? null;
                $columns = [];
                $placeholders = [];
                foreach ($data as $key => $value) {
                    if ($key === 'logo' && $table === 'Entreprise') {
                        if (!empty($_FILES['logo']['name'])) {
                            $logoData = handleLogoUpload($_FILES['logo']);
                            if ($logoData !== false) {
                                $data['logo'] = $logoData;
                            } else {
                                // Gérer l'erreur d'upload
                                echo "Erreur lors de l'upload du logo.";
                                continue;
                            }
                        } elseif ($action === 'edit') {
                            // Si c'est une édition et qu'aucun nouveau logo n'est fourni, on garde l'ancien
                            unset($data['logo']);
                        } else {
                            // Si c'est un ajout et qu'aucun logo n'est fourni, on met NULL
                            $data['logo'] = null;
                        }
                    }
                    if (isset($data[$key])) {
                        $columns[] = $key;
                        $placeholders[] = ':' . $key;
                    }
                }
                $columns = implode(', ', $columns);
                $placeholders = implode(', ', $placeholders);
                if ($action === 'add') {
                    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
                } else {
                    $set = implode(', ', array_map(function ($key) {
                        return "$key = :$key";
                    }, array_keys($data)));
                    $sql = "UPDATE $table SET $set WHERE id = :id";
                    $data['id'] = $id;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                break;
            // ... (le reste du code reste inchangé)
        }
        // Redirection pour éviter les soumissions multiples
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Récupération des données pour chaque table
$tableData = [];
foreach ($tables as $table) {
    $tableData[$table] = getTableData($pdo, $table);
}
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
        /* Styles CSS pour l'interface d'administration */
        .table-responsive { margin-top: 20px; }
        .action-buttons { white-space: nowrap; }
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
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; max-width: 100%; }
        @media (max-width: 1200px) {
            .panel-content { max-width: 100%; }
        }
        .nav-tabs {
            justify-content: center;
            margin-bottom: 20px;
        }
        .tab-content { width: 100%; }
        .panel-container {
            padding-top: 80px; /* Ajustement pour la navbar fixe */
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

        <div class="panel-content">
            <!-- Onglets pour les différentes tables -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <?php foreach ($tables as $index => $table): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                                id="<?php echo $table; ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#<?php echo $table; ?>" 
                                type="button" 
                                role="tab" 
                                aria-controls="<?php echo $table; ?>" 
                                aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                            <?php echo ucfirst($table); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Contenu des onglets -->
            <div class="tab-content" id="myTabContent">
                <?php foreach ($tables as $index => $table): ?>
                    <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
                        id="<?php echo $table; ?>" 
                        role="tabpanel" 
                        aria-labelledby="<?php echo $table; ?>-tab">
                        
                        <h2>Gestion des <?php echo ucfirst($table); ?></h2>
                        <button type="button" class="btn btn-primary mb-3" 
                                data-bs-toggle="modal" 
                                data-bs-target="#add<?php echo ucfirst($table); ?>Modal">
                            Ajouter un(e) <?php echo rtrim(ucfirst($table), 's'); ?>
                        </button>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <?php foreach (getTableStructure($pdo, $table) as $column): ?>
                                            <th><?php echo ucfirst($column['Field']); ?></th>
                                        <?php endforeach; ?>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableData[$table] as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $key => $value): ?>
                                                <td>
                                                    <?php
                                                    if ($key === 'logo' && $table === 'Entreprise' && !empty($value)) {
                                                        echo "<img src='../uploads/{$value}' alt='Logo' style='max-width: 50px; max-height: 50px;'>";
                                                    } else {
                                                        echo htmlspecialchars(nullSafe($value));
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#edit<?php echo ucfirst($table); ?>Modal<?php echo $row['id']; ?>">Éditer</button>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<!-- Modals d'ajout pour chaque table -->
<?php foreach ($tables as $table): ?>
    <div class="modal fade" id="add<?php echo ucfirst($table); ?>Modal" tabindex="-1" aria-labelledby="add<?php echo ucfirst($table); ?>ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add<?php echo ucfirst($table); ?>ModalLabel">Ajouter <?php echo rtrim(ucfirst($table), 's'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" enctype="multipart/form-data" onsubmit="return validateForm(this)">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="table" value="<?php echo $table; ?>">

                        <?php foreach (getTableStructure($pdo, $table) as $column): ?>
                            <?php if ($column['Field'] !== 'id'): ?>
                                <div class="mb-3">
                                    <label for="<?php echo $column['Field']; ?>" class="form-label"><?php echo ucfirst($column['Field']); ?></label>
                                    <?php if ($column['Field'] === 'logo' && $table === 'Entreprise'): ?>
                                        <input type="file" class="form-control" id="<?php echo $column['Field']; ?>" name="<?php echo $column['Field']; ?>" accept="image/png, image/jpeg, image/webp, image/jpg">
                                        <small class="form-text text-muted">Formats acceptés : PNG, JPEG, WebP, JPG. L'image sera convertie en WebP.</small>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="<?php echo $column['Field']; ?>" name="<?php echo $column['Field']; ?>" <?php echo (isset($column['Null']) && $column['Null'] === 'NO') ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modals d'édition pour chaque table -->
<?php foreach ($tables as $table): ?>
    <?php foreach ($tableData[$table] as $row): ?>
        <div class="modal fade" id="edit<?php echo ucfirst($table); ?>Modal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="edit<?php echo ucfirst($table); ?>ModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="edit<?php echo ucfirst($table); ?>ModalLabel<?php echo $row['id']; ?>">Éditer <?php echo rtrim(ucfirst($table), 's'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm(this)">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="table" value="<?php echo $table; ?>">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <?php foreach (getTableStructure($pdo, $table) as $column): ?>
                                <?php if ($column['Field'] !== 'id'): ?>
                                    <div class="mb-3">
                                        <label for="<?php echo $column['Field'] . $row['id']; ?>" class="form-label"><?php echo ucfirst($column['Field']); ?></label>
                                        <?php if ($column['Field'] === 'logo' && $table === 'Entreprise'): ?>
                                            <?php if (!empty($row['logo'])): ?>
                                                <img src="../uploads/<?php echo $row['logo']; ?>" alt="Logo actuel" class="img-fluid mb-2" style="max-width: 100px; max-height: 100px;">
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="<?php echo $column['Field'] . $row['id']; ?>" name="<?php echo $column['Field']; ?>" accept="image/png, image/jpeg, image/webp, image/jpg">
                                            <small class="form-text text-muted">Formats acceptés : PNG, JPEG, WebP, JPG. L'image sera convertie en WebP.</small>
                                        <?php else: ?>
                                            <input type="text" class="form-control" id="<?php echo $column['Field'] . $row['id']; ?>" name="<?php echo $column['Field']; ?>" value="<?php echo htmlspecialchars($row[$column['Field']] ?? ''); ?>" <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
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
<?php endforeach; ?>

    <?php renderFooter($siteName, $navLinks, $logoURL); ?>
    
    <!-- Inclusion des scripts JavaScript nécessaires -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction de validation du formulaire
        function validateForm(form) {
            var inputs = form.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].hasAttribute('required') && inputs[i].value.trim() === '') {
                    alert('Veuillez remplir tous les champs obligatoires !');
                    return false;
                }
            }
            // Si tous les champs sont remplis, le formulaire est valide
            return true;
        }

        // Fonction pour afficher un aperçu de l'image sélectionnée
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = input.previousElementSibling;
                            if (img && img.tagName === 'IMG') {
                                img.src = e.target.result;
                            } else {
                                const newImg = document.createElement('img');
                                newImg.src = e.target.result;
                                newImg.alt = 'Aperçu';
                                newImg.className = 'img-fluid mb-2';
                                newImg.style.maxWidth = '100px';
                                newImg.style.maxHeight = '100px';
                                input.parentNode.insertBefore(newImg, input);
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });
        });
    </script>
</body>
</html>