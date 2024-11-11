<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<body>
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>
    <div class="container">
        <h1 class="section-title">Liste des entreprises</h1>
        <?php if (!empty($enterprises)) : ?>
            <div class="row">
                <?php foreach ($enterprises as $enterprise) : ?>
                    <div class="col-12 mb-4">
                        <a href="/list/<?php echo $enterprise['id']; ?>" class="card-link">
                            <div class="card">
                                <?php if ($enterprise['lasallien']) : ?>
                                    <div class="lasalle-badge">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex">
                                    <div class="enterprise-logo-container">
                                        <?php $logo = base64_encode($enterprise['logo'] ?? ''); ?>
                                        <img 
                                            src="data:image/webp;base64,<?php echo $logo; ?>" 
                                            class="enterprise-logo" 
                                            alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>"
                                            onerror="this.src='/assets/images/logos/default.png'"
                                        >
                                    </div>
                                    <div>
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?>
                                        </h5>
                                        <?php
                                        $description = isset($enterprise['description'])
                                            ? htmlspecialchars($Utils->nullSafe($enterprise['description']))
                                            : 'Non renseigné';

                                        if ($description === "Non renseigné") {
                                            echo "Aucune description disponible";
                                        } else {
                                            $truncated = htmlspecialchars(mb_substr($description, 0, 150));
                                            echo $truncated . (mb_strlen($description) > 150 ? '...' : '');
                                        }
                                        ?>
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
                    <?php if ($page > 1) : ?>
                        <!-- Bouton Précédent -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="/list?page=<?php echo $page - 1; ?>">
                                <span>&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);

                    if ($start > 1) {
                        echo '<li class="page-item"><a class="page-link" href="/list?page=1">1</a></li>';
                        if ($start > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start; $i <= $end; $i++) : ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/list?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor;

                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item">' .
                             '<a class="page-link" href="/list?page=' . $total_pages . '">' .
                             $total_pages .
                             '</a></li>';
                    }
                    ?>

                    <!-- Bouton Suivant -->
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/list?page=<?php echo $page + 1; ?>">
                            <span>&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php else : ?>
            <p>Aucune entreprise trouvée.</p>
        <?php endif; ?>
    </div>
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
