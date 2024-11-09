<?php include ROOT_PATH . '/templates/layout/header.php'; ?>
<body>
    <?php include ROOT_PATH . '/templates/layout/navbar.php'; ?>
    <div class="container mt-5" style="padding-top: 60px;">
        <h1 class="mb-4"><?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?></h1>
        <div class="row">
            <div class="col-md-2">
                <img src="<?php
                    $logoPath = '/assets/images/logos/' . $enterprise['id'] . '.webp';
                    $defaultPath = '/assets/images/logos/default.png';
                    echo file_exists(PUBLIC_PATH . $logoPath) ? $logoPath : $defaultPath;
                ?>" class="img-fluid enterprise-logo mb-3" alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>">
            </div>
            <div class="col-md-8">
                <h2 class="section-title">Informations générales</h2>
                <div class="info-item"><span class="info-label">Téléphone :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['telephone'])); ?></div>
                <div class="info-item"><span class="info-label">Email :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['mail'])); ?></div>
                <div class="info-item"><span class="info-label">Adresse :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['numero'])) . ' ' .
                 htmlspecialchars($Utils->nullSafe($enterprise['rue'])) . ', ' .
                 htmlspecialchars($Utils->nullSafe($enterprise['code_postal'])) . ' ' .
                 htmlspecialchars($Utils->nullSafe($enterprise['commune'])); ?></div>
                <?php if (!empty($enterprise['site_web'])): ?>
                    <div class="info-item">
                        <span class="info-label">Site web :</span>
                        <a href="<?php echo htmlspecialchars($enterprise['site_web']); ?>" target="_blank">
                            <?php echo htmlspecialchars($enterprise['site_web']); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section informations juridiques -->
        <div class="mt-4">
            <h2 class="section-title">Informations juridiques</h2>
            <div class="info-item"><span class="info-label">SIREN :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['SIREN'])); ?></div>
            <div class="info-item"><span class="info-label">SIRET :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['SIRET'])); ?></div>
            <div class="info-item"><span class="info-label">RSC :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['RSC'])); ?></div>
            <div class="info-item"><span class="info-label">Activité :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['activite'])); ?></div>
            <div class="info-item"><span class="info-label">Activité principale :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['activite_main'])); ?></div>
            <div class="info-item"><span class="info-label">Nombre d'employés :</span> <?php echo htmlspecialchars($Utils->nullSafe($enterprise['employés'])); ?></div>
        </div>

        <!-- Section tuteurs -->
        <?php if (!empty($tuteurs)): ?>
            <div class="mt-4">
                <h2 class="section-title">Tuteur(s)</h2>
                <?php foreach ($tuteurs as $tuteur): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tuteur['nom'] . ' ' . $tuteur['prenom']); ?></h5>
                            <p class="card-text"><strong>Téléphone :</strong> <?php echo htmlspecialchars($Utils->nullSafe($tuteur['telephone'])); ?></p>
                            <p class="card-text"><strong>Mail :</strong> <?php echo htmlspecialchars($Utils->nullSafe($tuteur['mail'])); ?></p>
                            <p class="card-text"><strong>Poste :</strong> <?php echo htmlspecialchars($Utils->nullSafe($tuteur['poste'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="/list" class="btn btn-primary mt-3 mb-5">Retour à la liste</a>
    </div>
    <?php include ROOT_PATH . '/templates/layout/footer.php'; ?>
</body>
</html>
