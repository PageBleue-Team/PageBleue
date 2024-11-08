<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<body>
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>
    <div class="container mt-5" style="padding-top: 60px;">
        <h1>Liste des entreprises</h1>
        <?php if (!empty($enterprises)): ?>
            <div class="row">
                <?php foreach ($enterprises as $enterprise): ?>
                    <div class="col-12 mb-4">
                        <a href="/list/<?php echo $enterprise['id']; ?>" class="card-link">
                            <div class="card">
                                <?php if ($enterprise['lasallien']): ?>
                                    <div class="lasalle-badge">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex">
                                    <div class="enterprise-logo-container">
                                        <img src="<?php 
                                            $logoPath = '/assets/images/logos/' . $enterprise['id'] . '.webp';
                                            $defaultPath = '/assets/images/logos/default.png';
                                            echo file_exists(PUBLIC_PATH . $logoPath) ? $logoPath : $defaultPath;
                                        ?>" class="enterprise-logo" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
                                    </div>
                                    <div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?></h5>
                                        <p class="card-text">
                                            <?php
                                            $description = isset($enterprise['description']) ? htmlspecialchars($Utils->nullSafe($enterprise['description'])) : 'Non renseigné';
                                            if ($description === "Non renseigné") {
                                                echo "Aucune description disponible";
                                            } else {
                                                echo htmlspecialchars(mb_substr($description, 0, 150)) . (mb_strlen($description) > 150 ? '...' : '');
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/list?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php else: ?>
            <p>Aucune entreprise trouvée.</p>
        <?php endif; ?>
    </div>
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html> 