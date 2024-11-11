<?php
// Inclusion de configurations
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Paths;
use Config\Database;
$pdo = Database::getInstance()->getConnection();

// Importation des classes nécessaires pour le traitement des images
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Controller\SecurityController;

// Vérification de l'authentification de l'administrateur
$SecurityController = new SecurityController();
if (!$SecurityController->isAdminLoggedIn()) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
    header('Location: login');
    exit();
}

// Gestion de la déconnexion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    $authService->adminLogout();
}

// Fonction pour obtenir la structure d'une table
function getTableStructure($pdo, $tableName)
{
    $stmt = $pdo->prepare("DESCRIBE " . $pdo->quote($tableName));
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir la liste des tables de la base de données
function getTables($pdo)
{
    $stmt = $pdo->query("SHOW TABLES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fonction pour obtenir les données d'une table
function getTableData($pdo, $table, $page = 1, $perPage = 20)
{
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT * FROM `$table` LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Liste des tables à ne pas afficher dans l'interface d'administration
$blacklistedTables = ['login_logs', 'users', 'Entreprises_Activite'];

// Récupération de toutes les tables sauf celles dans la liste noire
$tables = array_diff(getTables($pdo), $blacklistedTables);

// Création d'une instance de ImageManager avec le driver GD pour le traitement des images
$manager = new ImageManager(new Driver());

// Fonction de conversion en WebP
function convertToWebP($sourcePath, $destinationPath)
{
    $info = getimagesize($sourcePath);
    $isTransparent = false;
    $image = null;

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
            return copy($sourcePath, $destinationPath);
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    if ($isTransparent) {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    try {
        $result = imagewebp($image, $destinationPath, 90);
        return $result;
    } finally {
        imagedestroy($image);
    }
}

// Fonction pour gérer l'upload et la conversion du logo
function handleLogoUpload($file, $entrepriseId)
{
    try {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Aucun fichier n'a été uploadé");
        }

        $tempFile = $file['tmp_name'];
        $imageInfo = getimagesize($tempFile);

        if ($imageInfo === false) {
            throw new Exception("Format d'image invalide");
        }

        // Création de l'image source
        $source = match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($tempFile),
            'image/png' => imagecreatefrompng($tempFile),
            'image/webp' => imagecreatefromwebp($tempFile),
            default => throw new Exception("Format d'image non supporté")
        };

        if (!$source) {
            throw new Exception("Erreur lors de la création de l'image source");
        }

        // Dimensions originales
        $width = imagesx($source);
        $height = imagesy($source);

        // Calcul des nouvelles dimensions (max 300x300)
        $maxSize = 300;
        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Création de l'image redimensionnée
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Gestion de la transparence
        if ($imageInfo['mime'] === 'image/png') {
            imagepalettetotruecolor($resized);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        // Redimensionnement
        imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        // Chemin du fichier de destination
        $destinationPath = LOGOS_URL . '/' . $entrepriseId . '.webp';

        // Conversion et sauvegarde en WebP
        $result = imagewebp($resized, $destinationPath, 90);

        // Clean up
        imagedestroy($source);
        imagedestroy($resized);

        return $result;  // Return true if logo was saved successfully, false otherwise
    } catch (Exception $e) {
        error_log("Erreur lors du traitement de l'image: " . $e->getMessage());
        return false;
    }
}

// Ajout de la fonction getLogoUrl manquante
function getLogoUrl(int $enterpriseId): string
{
    $logoPath = PUBLIC_PATH . '/assets/images/logos/' . $enterpriseId . '.webp';
    if (file_exists($logoPath)) {
        return '/assets/images/logos/' . $enterpriseId . '.webp';
    }
    return '/assets/images/default-logo.webp'; // Image par défaut
}

// Traitement des actions POST (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        error_log("Tentative CSRF détectée");
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']) . '?error=invalid_token');
        exit();
    }

    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validation de la table
    if (!in_array($table, $tables, true)) {
        error_log("Table non autorisée: " . htmlspecialchars($table));
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    }

    try {
        $pdo->beginTransaction();

        switch ($action) {
            case 'add':
                switch ($table) {
                    case 'Entreprises':
                        // Validation des données
                        $numero = filter_input(INPUT_POST, 'numero');
                        $rue = filter_input(INPUT_POST, 'rue');
                        $codePostal = filter_input(INPUT_POST, 'code_postal');
                        $commune = filter_input(INPUT_POST, 'commune');
                        $lieuDit = filter_input(INPUT_POST, 'lieu_dit');
                        $complement = filter_input(INPUT_POST, 'complement');

                        // Validation des emails et téléphones
                        $mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
                        $telephone = filter_input(INPUT_POST, 'telephone');
                        $siteWeb = filter_input(INPUT_POST, 'site_web', FILTER_VALIDATE_URL);

                        if (!$mail) {
                            throw new Exception("Format d'email invalide");
                        }

                        // Création de l'adresse avec prepared statement
                        $addrStmt = $pdo->prepare("INSERT INTO Adresse (numero, rue, code_postal, commune, lieu_dit, complement) VALUES (:numero, :rue, :code_postal, :commune, :lieu_dit, :complement)");
                        $addrStmt->execute([
                            ':numero' => $numero,
                            ':rue' => $rue,
                            ':code_postal' => $codePostal,
                            ':commune' => $commune,
                            ':lieu_dit' => $lieuDit,
                            ':complement' => $complement
                        ]);
                        $adresseId = $pdo->lastInsertId();

                        // Création du contact
                        $contactStmt = $pdo->prepare("INSERT INTO Contact (mail, telephone, site_web) VALUES (:mail, :telephone, :site_web)");
                        $contactStmt->execute([
                            ':mail' => $mail,
                            ':telephone' => $telephone,
                            ':site_web' => $siteWeb
                        ]);
                        $contactId = $pdo->lastInsertId();

                        // Validation SIREN/SIRET
                        $siren = filter_input(INPUT_POST, 'SIREN');
                        $siret = filter_input(INPUT_POST, 'SIRET');
                        if (!preg_match('/^[0-9]{9}$/', $siren) || !preg_match('/^[0-9]{14}$/', $siret)) {
                            throw new Exception("Format SIREN/SIRET invalide");
                        }

                        // Création des informations juridiques
                        $jurStmt = $pdo->prepare("INSERT INTO Juridique (SIREN, SIRET, creation, employés) VALUES (:siren, :siret, :creation, :employes)");
                        $jurStmt->execute([
                            ':siren' => $siren,
                            ':siret' => $siret,
                            ':creation' => filter_input(INPUT_POST, 'creation'),
                            ':employes' => filter_input(INPUT_POST, 'employés', FILTER_VALIDATE_INT)
                        ]);
                        $juridiqueId = $pdo->lastInsertId();

                        // Création de l'entreprise
                        $entStmt = $pdo->prepare("INSERT INTO Entreprises (nom, adresse_id, contact_id, juridique_id, lasallien, checked) VALUES (:nom, :adresse_id, :contact_id, :juridique_id, :lasallien, :checked)");
                        $entStmt->execute([
                            ':nom' => filter_input(INPUT_POST, 'nom'),
                            ':adresse_id' => $adresseId,
                            ':contact_id' => $contactId,
                            ':juridique_id' => $juridiqueId,
                            ':lasallien' => filter_input(INPUT_POST, 'lasallien', FILTER_VALIDATE_BOOLEAN) ?? false,
                            ':checked' => filter_input(INPUT_POST, 'checked', FILTER_VALIDATE_BOOLEAN) ?? false
                        ]);
                        $entrepriseId = $pdo->lastInsertId();

                        // Gestion sécurisée du logo
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            // Validation du type de fichier
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mimeType = $finfo->file($_FILES['logo']['tmp_name']);
                            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                            if (!in_array($mimeType, $allowedTypes, true)) {
                                throw new Exception("Type de fichier non autorisé");
                            }

                            // Limite de taille (5MB)
                            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                                throw new Exception("Fichier trop volumineux");
                            }

                            $logoUploaded = handleLogoUpload($_FILES['logo'], $entrepriseId);
                            if (!$logoUploaded) {
                                throw new Exception("Erreur lors de l'upload du logo");
                            }
                        }
                        break;

                    case 'Stage':
                        // Validation des dates
                        $dateDebut = DateTime::createFromFormat('Y-m-d', $_POST['date_debut']);
                        $dateFin = DateTime::createFromFormat('Y-m-d', $_POST['date_fin']);

                        if (!$dateDebut || !$dateFin || $dateDebut > $dateFin) {
                            throw new Exception("Dates invalides");
                        }

                        $stageStmt = $pdo->prepare("INSERT INTO Stage (entreprise_id, tuteur_id, date_debut, date_fin) VALUES (:entreprise_id, :tuteur_id, :date_debut, :date_fin)");
                        $stageStmt->execute([
                            ':entreprise_id' => filter_input(INPUT_POST, 'entreprise_id', FILTER_VALIDATE_INT),
                            ':tuteur_id' => filter_input(INPUT_POST, 'tuteur_id', FILTER_VALIDATE_INT),
                            ':date_debut' => $dateDebut->format('Y-m-d'),
                            ':date_fin' => $dateFin->format('Y-m-d')
                        ]);
                        break;

                    default:
                        // Traitement générique sécurisé
                        $columns = array_keys(array_filter($_POST, function ($key) {
                            return !in_array($key, ['action', 'table', 'csrf_token'], true);
                        }, ARRAY_FILTER_USE_KEY));

                        $placeholders = array_map(function ($col) {
                            return ":$col";
                        }, $columns);
                        $sql = "INSERT INTO `" . $pdo->quote($table) . "` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

                        $stmt = $pdo->prepare($sql);
                        $values = array_combine($placeholders, array_map(function ($key) {
                            return filter_input(INPUT_POST, $key);
                        }, $columns));
                        $stmt->execute($values);
                        break;
                }
                break;

            case 'edit':
                // Vérification de l'existence de l'ID
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new Exception("ID invalide");
                }

                switch ($table) {
                    case 'Entreprises':
                        // Vérification que l'entreprise existe
                        $checkStmt = $pdo->prepare("SELECT id FROM Entreprises WHERE id = ?");
                        $checkStmt->execute([$id]);
                        if (!$checkStmt->fetch()) {
                            throw new Exception("Entreprise non trouvée");
                        }

                        // Mise à jour sécurisée de l'adresse
                        $addrStmt = $pdo->prepare("UPDATE Adresse SET numero = :numero, rue = :rue, code_postal = :code_postal, commune = :commune, lieu_dit = :lieu_dit, complement = :complement WHERE id = (SELECT adresse_id FROM Entreprises WHERE id = :id)");
                        $addrStmt->execute([
                            ':numero' => filter_input(INPUT_POST, 'numero'),
                            ':rue' => filter_input(INPUT_POST, 'rue'),
                            ':code_postal' => filter_input(INPUT_POST, 'code_postal'),
                            ':commune' => filter_input(INPUT_POST, 'commune'),
                            ':lieu_dit' => filter_input(INPUT_POST, 'lieu_dit'),
                            ':complement' => filter_input(INPUT_POST, 'complement'),
                            ':id' => $id
                        ]);

                        // Validation et mise à jour du contact
                        $mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
                        if (!$mail) {
                            throw new Exception("Format d'email invalide");
                        }

                        $contactStmt = $pdo->prepare("UPDATE Contact SET mail = :mail, telephone = :telephone, site_web = :site_web WHERE id = (SELECT contact_id FROM Entreprises WHERE id = :id)");
                        $contactStmt->execute([
                            ':mail' => $mail,
                            ':telephone' => filter_input(INPUT_POST, 'telephone'),
                            ':site_web' => filter_input(INPUT_POST, 'site_web', FILTER_VALIDATE_URL),
                            ':id' => $id
                        ]);

                        // Validation et mise à jour juridique
                        $siren = filter_input(INPUT_POST, 'SIREN');
                        $siret = filter_input(INPUT_POST, 'SIRET');
                        if (!preg_match('/^[0-9]{9}$/', $siren) || !preg_match('/^[0-9]{14}$/', $siret)) {
                            throw new Exception("Format SIREN/SIRET invalide");
                        }

                        $jurStmt = $pdo->prepare("UPDATE Juridique SET SIREN = :siren, SIRET = :siret, creation = :creation, employés = :employes WHERE id = (SELECT juridique_id FROM Entreprises WHERE id = :id)");
                        $jurStmt->execute([
                            ':siren' => $siren,
                            ':siret' => $siret,
                            ':creation' => filter_input(INPUT_POST, 'creation'),
                            ':employes' => filter_input(INPUT_POST, 'employés', FILTER_VALIDATE_INT),
                            ':id' => $id
                        ]);

                        // Mise à jour entreprise
                        $entStmt = $pdo->prepare("UPDATE Entreprises SET nom = :nom, lasallien = :lasallien, checked = :checked WHERE id = :id");
                        $entStmt->execute([
                            ':nom' => filter_input(INPUT_POST, 'nom'),
                            ':lasallien' => filter_input(INPUT_POST, 'lasallien', FILTER_VALIDATE_BOOLEAN) ?? false,
                            ':checked' => filter_input(INPUT_POST, 'checked', FILTER_VALIDATE_BOOLEAN) ?? false,
                            ':id' => $id
                        ]);

                        // Gestion sécurisée du logo
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mimeType = $finfo->file($_FILES['logo']['tmp_name']);
                            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                            if (!in_array($mimeType, $allowedTypes, true)) {
                                throw new Exception("Type de fichier non autorisé");
                            }

                            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                                throw new Exception("Fichier trop volumineux");
                            }

                            $logoUploaded = handleLogoUpload($_FILES['logo'], $id);
                            if (!$logoUploaded) {
                                throw new Exception("Erreur lors de l'upload du logo");
                            }
                        }
                        break;

                    case 'Stage':
                        // Validation des dates
                        $dateDebut = DateTime::createFromFormat('Y-m-d', $_POST['date_debut']);
                        $dateFin = DateTime::createFromFormat('Y-m-d', $_POST['date_fin']);

                        if (!$dateDebut || !$dateFin || $dateDebut > $dateFin) {
                            throw new Exception("Dates invalides");
                        }

                        $stageStmt = $pdo->prepare("UPDATE Stage SET entreprise_id = :entreprise_id, tuteur_id = :tuteur_id, date_debut = :date_debut, date_fin = :date_fin WHERE id = :id");
                        $stageStmt->execute([
                            ':entreprise_id' => filter_input(INPUT_POST, 'entreprise_id', FILTER_VALIDATE_INT),
                            ':tuteur_id' => filter_input(INPUT_POST, 'tuteur_id', FILTER_VALIDATE_INT),
                            ':date_debut' => $dateDebut->format('Y-m-d'),
                            ':date_fin' => $dateFin->format('Y-m-d'),
                            ':id' => $id
                        ]);
                        break;

                    default:
                        // Traitement générique sécurisé pour la mise à jour
                        $updates = [];
                        $values = [];
                        foreach ($_POST as $key => $value) {
                            if (!in_array($key, ['action', 'table', 'id', 'csrf_token'], true)) {
                                $updates[] = "`" . $pdo->quote($key) . "` = :$key";
                                $values[":$key"] = filter_input(INPUT_POST, $key);
                            }
                        }
                        $values[':id'] = $id;

                        $sql = "UPDATE `" . $pdo->quote($table) . "` SET " . implode(', ', $updates) . " WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($values);
                }
                break;

            case 'delete':
                // Validation de l'ID
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new Exception("ID invalide");
                }

                // Vérification que l'enregistrement existe
                $checkStmt = $pdo->prepare("SELECT id FROM `$table` WHERE id = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception("Enregistrement non trouvé");
                }

                // Pour les tables avec des relations, la suppression en cascade est gérée par les contraintes de la base de données
                $stmt = $pdo->prepare("DELETE FROM `" . $pdo->quote($table) . "` WHERE id = :id");
                $stmt->execute([':id' => $id]);

                // Si c'est une entreprise, supprimer aussi le logo de manière sécurisée
                if ($table === 'Entreprises') {
                    $logoPath = LOGOS_URL . '/' . $id . '.webp';
                    if (file_exists($logoPath) && is_file($logoPath)) {
                        // Vérification supplémentaire du chemin pour éviter la traversée de répertoire
                        $realLogoPath = realpath($logoPath);
                        $realLogoDir = realpath(LOGOS_URL);

                        if (
                            $realLogoPath !== false &&
                            $realLogoDir !== false &&
                            strpos($realLogoPath, $realLogoDir) === 0
                        ) {
                            if (!unlink($realLogoPath)) {
                                error_log("Impossible de supprimer le logo: $logoPath");
                            }
                        }
                    }
                }
                break;

            default:
                throw new Exception("Action non valide");
        }

        $pdo->commit();

        // Redirection avec message de succès
        $_SESSION['success_message'] = "Opération effectuée avec succès";
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur lors de l'opération sur la table " . htmlspecialchars($table) . ": " . $e->getMessage());

        // Stockage sécurisé du message d'erreur dans la session
        $_SESSION['error_message'] = "Une erreur est survenue lors de l'opération";
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
        exit();
    }
}

function handleAjaxRequest()
{
    // Validation du type de contenu
    if (!isset($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== 0) {
        http_response_code(415);
        exit(json_encode(['error' => 'Type de contenu non supporté']));
    }

    // Limitation de taux
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit:ajax:$ip";
    if (isset($_SESSION[$key])) {
        $requests = $_SESSION[$key];
        if ($requests['count'] > 100 && time() - $requests['time'] < 3600) {
            http_response_code(429);
            exit(json_encode(['error' => 'Trop de requêtes']));
        }
        if (time() - $requests['time'] >= 3600) {
            $_SESSION[$key] = ['count' => 1, 'time' => time()];
        } else {
            $_SESSION[$key]['count']++;
        }
    } else {
        $_SESSION[$key] = ['count' => 1, 'time' => time()];
    }

    // Validation CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit(json_encode(['error' => 'Token CSRF invalide']));
    }
    // Validation des entrées
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$action || !$table) {
        http_response_code(400);
        exit(json_encode(['error' => 'Paramètres invalides']));
    }

    try {
        $pdo = Database::getInstance()->getConnection();
        $pdo->beginTransaction();

        switch ($action) {
            case 'add':
                // Implémentez la logique d'ajout sécurisée ici
                break;
            case 'edit':
                // Implémentez la logique d'édition sécurisée ici
                break;
            case 'delete':
                // Implémentez la logique de suppression sécurisée ici
                break;
            default:
                throw new Exception('Action non valide');
        }

        $pdo->commit();
        echo json_encode(getTableData($pdo, $table));
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        error_log($e->getMessage());
        exit(json_encode(['error' => 'Une erreur est survenue']));
    }
    exit;
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    handleAjaxRequest();
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
    <meta
      name="description"
      content="Panel d'administration de PageBleue.">
    <title>Panel - <?php echo htmlspecialchars($siteName); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.crud-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'panel.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    // Mettre à jour le tableau avec les nouvelles données
                    updateTable(response);
                }
            });
        });
    });
    </script>
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
            margin-bottom: 15px;
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
        .tab-content {
            width: 100%;
        }
        /* Modif pour eviter le scroll horizontal */
        .table-responsive {
            margin-top: 20px;
            overflow-x: auto; /* Permet le défilement horizontal si nécessaire sur les petits écrans */
        }
        .table {
            width: 100%;
            table-layout: auto; /* Permet au tableau de s'adapter au contenu */
        }
        .table th,
        .table td {
            padding: 0.5rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            word-wrap: break-word; /* Permet la coupure des mots longs */
            min-width: 100px; /* Largeur minimale pour chaque cellule */
        }
        .table .address-cell {
            min-width: 200px; /* Plus large pour l'adresse */
        }
        .logo-cell img {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }
        .action-buttons {
            white-space: nowrap;
            min-width: 150px; /* Espace pour les boutons d'action */
        }
        @media (min-width: 992px) {
            .table th,
            .table td {
                min-width: auto; /* Réinitialise la largeur minimale sur les grands écrans */
            }
            .table .address-cell {
                min-width: auto;
                max-width: 300px; /* Limite la largeur maximale de l'adresse sur grands écrans */
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <div class="container mt-5" style="padding-top: 30px;">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="mb-4">Panel d'administration</h1>
            <form method="POST" action="">
                <button type="submit" name="logout" class="btn btn-danger btn-lg">Déconnexion</button>
            </form>
        </div>

        <div class="panel-content">
            <!-- Onglets pour les différentes tables -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <?php foreach ($tables as $index => $table) : ?>
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
                <?php foreach ($tables as $index => $table) : ?>
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
                                        <?php foreach (getTableStructure($pdo, $table) as $column) : ?>
                                            <th><?php echo ucfirst($column['Field']); ?></th>
                                        <?php endforeach; ?>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableData[$table] as $row) : ?>
                                        <tr>
                                            <?php foreach (getTableStructure($pdo, $table) as $column) : ?>
                                                <?php if ($column['Field'] === 'logo' && $table === 'Entreprises') : ?>
                                                    <td class="logo-cell">
                                                        <img src="<?php echo getLogoUrl($row['id']); ?>" 
                                                             alt="Logo" 
                                                             class="img-fluid">
                                                    </td>
                                                <?php else : ?>
                                                    <td><?php echo htmlspecialchars($row[$column['Field']] ?? ''); ?></td>
                                                <?php endif; ?>
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

    <!-- Modals d'ajout et d'édition pour chaque table -->
    <?php foreach ($tables as $table) : ?>
        <!-- Modal d'ajout -->
        <div class="modal fade" id="add<?php echo ucfirst($table); ?>Modal" tabindex="-1" aria-labelledby="add<?php echo ucfirst($table); ?>ModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add<?php echo ucfirst($table); ?>ModalLabel">Ajouter <?php echo rtrim(ucfirst($table), 's'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="table" value="<?php echo $table; ?>">

                        <?php foreach (getTableStructure($pdo, $table) as $column) : ?>
                            <?php if ($column['Field'] !== 'id') : ?>
                                <div class="mb-3">
                                    <label for="add_<?php echo $column['Field']; ?>" class="form-label"><?php echo ucfirst($column['Field']); ?></label>

                                    <?php if ($column['Field'] === 'logo') : ?>
                                        <input type="file" class="form-control" id="add_<?php echo $column['Field']; ?>" name="<?php echo $column['Field']; ?>" accept="image/*">
                                        <small class="form-text text-muted">Formats acceptés : PNG, JPEG, WebP, JPG. L'image sera convertie en WebP.</small>
                                    <?php else : ?>
                                        <input type="text" class="form-control" id="add_<?php echo $column['Field']; ?>" name="<?php echo $column['Field']; ?>" <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modals d'édition -->
        <?php foreach ($tableData[$table] as $row) : ?>
            <?php if (isset($row['id'])) : ?>
            <div class="modal fade" id="edit<?php echo ucfirst($table); ?>Modal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="edit<?php echo ucfirst($table); ?>ModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="edit<?php echo ucfirst($table); ?>ModalLabel<?php echo $row['id']; ?>">Éditer <?php echo rtrim(ucfirst($table), 's'); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="table" value="<?php echo $table; ?>">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                            <?php foreach (getTableStructure($pdo, $table) as $column) : ?>
                                <?php if ($column['Field'] !== 'id') : ?>
                                    <div class="mb-3">
                                        <label for="<?php echo $column['Field'] . $row['id']; ?>" class="form-label"><?php echo ucfirst($column['Field']); ?></label>

                                        <?php if ($column['Field'] === 'logo') : ?>
                                            <?php if (!empty($row['logo'])) : ?>
                                                <img src="<?php echo htmlspecialchars($row['logo']); ?>" alt="Logo actuel" class="img-fluid mb-2" style="max-width: 100px; max-height: 100px;">
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="<?php echo $column['Field'] . $row['id']; ?>" name="<?php echo $column['Field']; ?>" accept="image/*">
                                            <small class="form-text text-muted">Laissez vide pour conserver le logo actuel. Formats acceptés : PNG, JPEG, WebP, JPG.</small>
                                        <?php else : ?>
                                            <input type="text" class="form-control" id="<?php echo $column['Field'] . $row['id']; ?>" name="<?php echo $column['Field']; ?>" value="<?php echo htmlspecialchars($row[$column['Field']] ?? ''); ?>" <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>

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
