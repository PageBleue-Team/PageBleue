<?php
// templates/components/enterprise-card.php
?>
<div class="col-md-4 mb-4">
    <div class="card enterprise-card" onclick="window.location.href='/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>'">
        <?php if ($enterprise['lasallien']): ?>
            <div class="alumni-icon" title="Ancien élève de La Salle">
                <i class="fas fa-user-graduate"></i>
            </div>
        <?php endif; ?>
        
        <img 
            src="<?php echo !empty($enterprise['logo']) 
                ? 'data:image/jpeg;base64,' . base64_encode($enterprise['logo']) 
                : '/assets/images/logos/default.png'; ?>" 
            class="card-img-top" 
            alt="Logo <?php echo htmlspecialchars($enterprise['nom']); ?>"
        >
        
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($enterprise['nom']); ?></h5>
            <p class="card-text">
                <strong>Adresse:</strong> 
                <?php
                $adresse = array_filter([
                    $enterprise['numero'] ?? null,
                    $enterprise['rue'] ?? null,
                    $enterprise['complement'] ?? null,
                    $enterprise['code_postal'] ?? null,
                    $enterprise['ville'] ?? null
                ]);
                echo htmlspecialchars(implode(', ', $adresse));
                ?>
            </p>
            <div class="col text-center">
                <a href="/list?id=<?php echo htmlspecialchars($enterprise['id']); ?>" 
                   class="btn btn-primary">En savoir plus</a>
            </div>
        </div>
    </div>
</div>