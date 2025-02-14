<?php
if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use App\Controller\SecurityController;
use App\Controller\AdminController;
use App\Domain\Repository\TableRepository;
use Config\Database;

// Vérification de l'authentification de l'administrateur
$SecurityController = new SecurityController();
if (!$SecurityController->isAdminLoggedIn()) {
    header('Location: login');
    exit();
}

// Initialisation des repositories
$pdo = Database::getInstance()->getConnection();
$tableRepository = new TableRepository($pdo);

// Initialisation du contrôleur admin et récupération des données
$adminController = new AdminController();
$dashboardData = $adminController->getDashboardData();
$tables = $dashboardData['tables'];
$tableData = $dashboardData['tableData'];

// Gestion de la déconnexion
$authService = new SecurityController();
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    $authService->adminLogout();
}

?>

<!DOCTYPE html>
<html lang="fr">

<!-- Header -->
<?php include ROOT_PATH . '/templates/layout/header.php'; ?>

<body>
    <!-- Navbar -->
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>

    <div class="container mt-4">
        <h1>Panel Administrateur</h1>

        <?php if (isset($_SESSION['success_message'])) : ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Navigation par onglets -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="tableTabs" role="tablist">
                    <?php foreach ($tables as $index => $table) : ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                    id="tab-<?= htmlspecialchars((string)$table) ?>" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#content-<?= htmlspecialchars((string)$table) ?>" 
                                    type="button" 
                                    role="tab">
                                <?php echo htmlspecialchars($table); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="tableTabsContent">
                    <?php foreach ($tables as $index => $table) : ?>
                        <div class="tab-pane <?= $index === 0 ? 'active' : '' ?>" 
                             id="content-<?= htmlspecialchars((string)$table) ?>" 
                             role="tabpanel">
                            
                            <!-- Bouton pour ouvrir la modale -->
                            <div class="mb-3">
                                <button type="button" 
                                        class="btn btn-primary" 
                                        data-bs-target="addModal<?= htmlspecialchars($table) ?>">
                                    <i class="fas fa-plus"></i> Ajouter
                                </button>
                            </div>

                            <!-- Tableau des données -->
                            <div class="table-responsive">
                                <?php
                                // Récupérer la structure de la table
                                $structure = $tableRepository->getTableStructure($table);
                                $columns = array_column($structure, 'Field');
                                ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <?php foreach ($structure as $column) : ?>
                                                <th><?php echo htmlspecialchars((string)$column['Field']); ?></th>
                                            <?php endforeach; ?>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($tableData[$table])) : ?>
                                            <?php foreach ($tableData[$table] as $row) : ?>
                                                <tr>
                                                    <?php foreach ($columns as $column) : ?>
                                                        <td>
                                                            <?php if ($column === 'logo' && !empty($row[$column])) : ?>
                                                                <img src="data:image/webp;base64,<?= base64_encode($row[$column]); ?>" 
                                                                     alt="Logo" 
                                                                     style="max-width: 50px; max-height: 50px;">
                                                            <?php elseif ($column === 'adresse') : ?>
                                                                <?php
                                                                try {
                                                                    $adresse = '';

                                                                    if (!empty($row['lieu_dit']) && $row['lieu_dit'] !== 'Non Renseigné') {
                                                                        $adresse = $row['lieu_dit'] . ', ' . $row['code_postal'] . ' ' . $row['commune'];
                                                                    } else {
                                                                        $numeroStr = (!empty($row['numero']) && $row['numero'] !== 'Non Renseigné') ? $row['numero'] . ' ' : '';
                                                                        $rueStr = (!empty($row['rue']) && $row['rue'] !== 'Non Renseigné') ? $row['rue'] : '';

                                                                        if (!empty($rueStr)) {
                                                                            $adresse = $numeroStr . $rueStr . ', ' . $row['code_postal'] . ' ' . $row['commune'];
                                                                        } else {
                                                                            $adresse = $row['code_postal'] . ' ' . $row['commune'];
                                                                        }
                                                                    }
                                                                    echo htmlspecialchars($adresse);
                                                                } catch (\Throwable $e) {
                                                                    echo "Champ introuvable";
                                                                }
                                                                ?>
                                                            <?php else : ?>
                                                                <?php
                                                                // En cas de valeur null, afficher "Champ non trouvé"
                                                                $value = $row[$column] ?? 'Champ non trouvé';
                                                                echo htmlspecialchars($value);
                                                                ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?= $table . $row['id'] ?>">
                                                            Modifier
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $table . $row['id'] ?>">
                                                            Supprimer
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="<?= count($columns) + 1 ?>" class="text-center">
                                                    Aucune donnée disponible
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>

    <!-- Script Dashboard -->
    <script src="/assets/js/dashboard.js"></script>

    <!-- Modales pour chaque table -->
    <?php foreach ($tables as $table) : ?>
        <!-- Modal -->
        <div class="modal" id="addModal<?= htmlspecialchars($table) ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter - <?php echo htmlspecialchars($table); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="add-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="modal-body">
                            <?php
                            $structure = $tableRepository->getTableStructure($table);
                            foreach ($structure as $column) :
                                if ($column['Field'] !== 'id') :
                                    ?>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo htmlspecialchars((string)$column['Field']); ?></label>
                                    <?php if ($column['Field'] === 'logo') : ?>
                                        <input type="file" class="form-control" name="<?php echo $column['Field']; ?>" accept="image/*">
                                    <?php elseif ($tableRepository->isForeignKey((string)$column['Field'])) : ?>
                                        <?php
                                        $referencedTable = ucfirst(str_replace('_id', '', (string)$column['Field']));
                                        $foreignData = $tableRepository->getForeignKeyData($referencedTable);
                                        ?>
                                        <select class="form-select" name="<?php echo $column['Field']; ?>" <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                            <option value="">Sélectionnez...</option>
                                            <?php foreach ($foreignData as $item) : ?>
                                                <option value="<?php echo $item['id']; ?>"
                                                        <?php echo ($item['id'] == $tableRepository->getNextId($table)) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($item['display_value']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <input type="text" 
                                               class="form-control" 
                                               name="<?php echo $column['Field']; ?>"
                                               <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                    <?php endif; ?>
                                </div>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modales d'édition pour chaque ligne -->
        <?php if (!empty($tableData[$table])) : ?>
            <?php foreach ($tableData[$table] as $row) : ?>
                <div class="modal" id="editModal<?= $table . $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier - <?php echo htmlspecialchars($table); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $SecurityController->generateCsrfToken(); ?>">

                                    <?php foreach ($structure as $column) : ?>
                                        <?php
                                        // S'assurer que Field est une string avant de l'utiliser
                                        $fieldName = isset($column['Field']) ? (string)$column['Field'] : '';
                                        if ($fieldName !== 'id') :
                                            ?>
                                            <div class="mb-3">
                                                <label class="form-label"><?= htmlspecialchars($fieldName); ?></label>
                                                <?php if ($fieldName === 'logo') : ?>
                                                    <?php if (!empty($row[$fieldName])) : ?>
                                                        <img src="data:image/webp;base64,<?php echo base64_encode($row[$fieldName]); ?>" 
                                                             alt="Logo actuel" 
                                                             class="img-fluid mb-2" 
                                                             style="max-width: 100px;">
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" name="<?= $fieldName; ?>" accept="image/*">
                                                <?php elseif ($tableRepository->isForeignKey($fieldName)) : ?>
                                                    <?php
                                                    $referencedTable = ucfirst(str_replace('_id', '', $fieldName));
                                                    $foreignData = $tableRepository->getForeignKeyData($referencedTable);
                                                    ?>
                                                    <select class="form-select" name="<?= $fieldName; ?>" <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                        <option value="">Sélectionnez...</option>
                                                        <?php foreach ($foreignData as $item) : ?>
                                                            <option value="<?php echo $item['id']; ?>" 
                                                                    <?php echo ($row[$fieldName] == $item['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($item['display_value']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php else : ?>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="<?= $fieldName; ?>"
                                                           value="<?php echo htmlspecialchars($row[$fieldName] ?? ''); ?>"
                                                           <?php echo $column['Null'] === 'NO' ? 'required' : ''; ?>>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Modales de suppression pour chaque ligne -->
        <?php if (!empty($tableData[$table])) : ?>
            <?php foreach ($tableData[$table] as $row) : ?>
                <div class="modal" id="deleteModal<?= $table . $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmer la suppression</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                                <?php if ($table === 'Entreprises' && !empty($row['nom'])) : ?>
                                    <p>Entreprise : <strong><?php echo htmlspecialchars($row['nom']); ?></strong></p>
                                <?php endif; ?>
                            </div>
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $SecurityController->generateCsrfToken(); ?>">
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
