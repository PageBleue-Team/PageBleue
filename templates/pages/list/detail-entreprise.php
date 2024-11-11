<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<body>
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>
    <div class="container">
        <h1 class="mb-4">
            <?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?>
        </h1>
        <div class="row">
            <div class="col-md-2">
                <img 
                    src="data:image/webp;base64,<?php echo base64_encode($enterprise['logo'] ?? ''); ?>" 
                    class="img-fluid enterprise-logo mb-3" 
                    alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>"
                    onerror="this.src='/assets/images/logos/default.png'"
                >
            </div>
            <div class="col-md-8">
                <h2 class="section-title">Informations générales</h2>
                <div class="info-item">
                    <span class="info-label">Téléphone :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['telephone'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Email :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['mail'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Adresse :</span> 
                    <?php
                    $adresse = htmlspecialchars($Utils->nullSafe($enterprise['numero'])) . ' ' .
                              htmlspecialchars($Utils->nullSafe($enterprise['rue'])) . ', ' .
                              htmlspecialchars($Utils->nullSafe($enterprise['code_postal'])) . ' ' .
                              htmlspecialchars($Utils->nullSafe($enterprise['commune']));
                    echo $adresse;
                    ?>
                </div>
            </div>
        </div>

                <div class="mt-4">
                    <!-- Section informations juridiques -->
                    <h2 class="section-title">Informations juridiques</h2>
                    <div class="info-item">
                    <span class="info-label">SIREN :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['SIREN'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">SIRET :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['SIRET'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Date de création :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($Utils->formatDate($enterprise['creation']))); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Activité :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['activite'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Activité principale :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['activite_main'])); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre d'employés :</span> 
                    <?php echo htmlspecialchars($Utils->nullSafe($enterprise['employés'])); ?>
                </div>

        <!-- Section tuteurs -->
        <?php if (!empty($tuteurs)) : ?>
            <div class="mt-4">
                <h2 class="section-title">Tuteur(s)</h2>
                <?php foreach ($tuteurs as $tuteur) : ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($tuteur['nom'] . ' ' . $tuteur['prenom']); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Téléphone :</strong> 
                                <?php echo htmlspecialchars($Utils->nullSafe($tuteur['telephone'])); ?>
                            </p>
                            <p class="card-text">
                                <strong>Mail :</strong> 
                                <?php echo htmlspecialchars($Utils->nullSafe($tuteur['mail'])); ?>
                            </p>
                            <p class="card-text">
                                <strong>Poste :</strong> 
                                <?php echo htmlspecialchars($Utils->nullSafe($tuteur['poste'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="/list" class="btn btn-primary mt-3 mb-5">Retour à la liste</a>
    </div>
</div>
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
