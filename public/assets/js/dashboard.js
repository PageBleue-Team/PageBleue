document.addEventListener('DOMContentLoaded', function() {
    // Suppression de la classe 'fade' des modales
    document.querySelectorAll('.modal.fade').forEach(modal => {
        modal.classList.remove('fade');
    });

    // Suppression de la classe 'fade' des tab-panes
    document.querySelectorAll('.tab-pane.fade').forEach(tab => {
        tab.classList.remove('fade');
    });

    // Gestion des onglets sans animation
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    
    tabLinks.forEach(tabLink => {
        tabLink.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.dataset.bsTarget);
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Activer l'onglet sélectionné
            target.classList.add('active');
            this.classList.add('active');
        });

        // Gestion de l'historique pour les onglets
        tabLink.addEventListener('shown.bs.tab', function (event) {
            const id = event.target.getAttribute('data-bs-target').substring(1);
            history.pushState(null, null, `#${id}`);
        });
    });

    // Restaurer l'onglet actif au chargement de la page
    if (location.hash) {
        const activeTab = document.querySelector(`[data-bs-target="${location.hash}"]`);
        if (activeTab) {
            const tab = new bootstrap.Tab(activeTab);
            tab.show();
        }
    }

    // Gestion des modales
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
        });
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
        });
    });

    // Validation des formulaires
    function validateForm(form) {
        var inputs = form.querySelectorAll('input[required], select[required]');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value.trim() === '') {
                alert('Veuillez remplir tous les champs obligatoires !');
                inputs[i].focus();
                return false;
            }
        }
        return true;
    }

    // Appliquer la validation à tous les formulaires
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateForm(this)) {
                event.preventDefault();
            }
        });
    });
});
