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
use App\Services\ImageService;
$Utils = new Utils();
$pdo = Database::getInstance()->getConnection();
$entrepriseRepo = new EntrepriseRepository($pdo);
$imageService = new ImageService();
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
                <div class="d-flex">
                    <?php foreach ($group as $enterprise) : ?>
                        <div class="card-wrapper">
                            <a href="/list/<?php echo htmlspecialchars($enterprise['id']); ?>">
                                <div class="card">
                                    <?php if ($enterprise['lasallien']) : ?>
                                        <div class="position-absolute top-0 end-0 m-2 bg-primary rounded-circle p-2 text-white">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2" style="width: 35px; height: 35px;">
                                                <?php 
                                                // Redimensionner le logo avant l'encodage
                                                $logo = $enterprise['logo'] ?? '';
                                                if ($logo) {
                                                    $logo = $imageService->resizeImage($logo, 70, 70); // Double de la taille d'affichage pour les écrans HD
                                                    $logo = base64_encode($logo);
                                                }
                                                ?>
                                                <img 
                                                    src="data:image/webp;base64,<?php echo $logo; ?>" 
                                                    class="w-100 h-100 object-fit-contain" 
                                                    alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>"
                                                    onerror="this.src='/public/assets/images/logos/default.png'"
                                                    loading="lazy"
                                                >
                                            </div>

                                            <h2 class="card-title mb-0 fs-6 text-dark">
                                                <?php echo htmlspecialchars($Utils->nullSafe($enterprise['nom'])); ?>
                                            </h2>
                                        </div>

                                        <p class="card-text mt-2 small text-muted">
                                            <?php
                                            $adresse = '';

                                            // Si on a une rue
                                            if (!empty($enterprise['rue']) && $enterprise['rue'] !== 'Non Renseigné') {
                                                $numero = (!empty($enterprise['numero']) && $enterprise['numero'] !== 'Non Renseigné')
                                                    ? $enterprise['numero'] . ' '
                                                    : '';
                                                $adresse = $numero . $enterprise['rue'];
                                            } elseif (!empty($enterprise['lieu_dit']) && $enterprise['lieu_dit'] !== 'Non Renseigné') { // Si pas de rue mais un lieu-dit
                                                $adresse = $enterprise['lieu_dit'];
                                            }

                                            // Ajoute le code postal et la commune
                                            if (empty($adresse)) {
                                                $adresse = $enterprise['code_postal'] . ' ' . $enterprise['commune'];
                                            } else {
                                                $adresse .= ', ' . $enterprise['code_postal'] . ' ' . $enterprise['commune'];
                                            }

                                            echo htmlspecialchars($adresse);
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
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
    const carousel = new bootstrap.Carousel(document.querySelector('#enterpriseCarousel'), {
        interval: 5000, // Défilement automatique toutes les 5 secondes
        wrap: true
    });

    // Gestion des événements des flèches
    document.querySelectorAll('.carousel-control-prev, .carousel-control-next').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('carousel-control-prev')) {
                carousel.prev();
            } else {
                carousel.next();
            }
        });
    });
});
</script>
