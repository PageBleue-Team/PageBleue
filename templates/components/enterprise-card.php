<?php

/**
 * Composant carte entreprise - Affiche 5 entreprises aléatoires
 */

if (!function_exists('safeInclude')) {
    require_once __DIR__ . '/../../config/init.php';
}

use Config\Utils;
use Config\Database;
use App\Domain\Repository\EntrepriseRepository;
$Utils = new Utils();
$pdo = Database::getInstance()->getConnection();
$entrepriseRepo = new EntrepriseRepository($pdo);
// Récupère 5 entreprises aléatoires
$nbEntreprises = 5;
$enterprises = $entrepriseRepo->getFeaturedEntreprises($nbEntreprises);
$chunks = array_chunk($enterprises, 5);
?>

<div id="enterpriseCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php for ($i = 0; $i < count($chunks); $i++) : ?>
            <button type="button" 
                    data-bs-target="#enterpriseCarousel" 
                    data-bs-slide-to="<?php echo $i; ?>" 
                    <?php echo $i === 0 ? 'class="active"' : ''; ?>
                    aria-label="Slide <?php echo $i + 1; ?>">
            </button>
        <?php endfor; ?>
    </div>

    <div class="carousel-inner">
        <?php foreach ($chunks as $index => $group) : ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="d-flex justify-content-between">
                    <?php foreach ($group as $enterprise) : ?>
                        <div class="card-wrapper">
                            <div class="card h-100 shadow-sm mx-2">
                                <?php if ($enterprise['lasallien']) : ?>
                                    <div class="position-absolute top-0 end-0 m-2 bg-primary rounded-circle p-2 
                                        text-white">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 35px; height: 35px;">
                                            <img src="<?php
                                                $logoPath = '/assets/images/logos/' . $enterprise['id'] . '.webp';
                                            $defaultPath = '/assets/images/logos/default.png';
                                            echo file_exists(PUBLIC_PATH . $logoPath) ? $logoPath : $defaultPath;
                                            ?>" class="w-100 h-100 object-fit-contain" alt="Logo">
                                        </div>

                                        <h5 class="card-title mb-0 h6">
                                            <?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?>
                                        </h5>
                                    </div>

                                    <p class="card-text mt-2 small text-muted">
                                        <?php
                                        echo htmlspecialchars($Utils->nullSafe($enterprise['numero'])) . ' ' .
                                             htmlspecialchars($Utils->nullSafe($enterprise['rue'])) . ', ' .
                                             htmlspecialchars($Utils->nullSafe($enterprise['code_postal'])) . ' ' .
                                             htmlspecialchars($Utils->nullSafe($enterprise['commune']));
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach; ?>
                </div>
            </div>
            <?php
        endforeach; ?>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#enterpriseCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Précédent</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#enterpriseCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Suivant</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Carousel(document.querySelector('#enterpriseCarousel'), {
        interval: false, // Désactive le défilement automatique
        wrap: true // Permet de revenir au début après la dernière slide
    });
});
</script>
