document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Carousel(document.getElementById('enterpriseCarousel'), {
        interval: 5000, // Change de slide toutes les 5 secondes
        wrap: true,     // Boucle infinie
        touch: true     // Support tactile
    });
});
