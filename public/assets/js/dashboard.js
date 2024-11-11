document.addEventListener('DOMContentLoaded', function() {
    // Désactiver le comportement par défaut de Bootstrap pour les modales
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.removeAttribute('data-bs-toggle');
    });

    // Initialisation des modales
    const modals = {};
    
    // Fonction pour ouvrir une modale
    function openModal(modalId) {
        if (!modals[modalId]) {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                modals[modalId] = new bootstrap.Modal(modalElement);
            }
        }
        if (modals[modalId]) {
            modals[modalId].show();
        }
    }

    // Fonction pour fermer une modale
    function closeModal(modalId) {
        if (modals[modalId]) {
            modals[modalId].hide();
        }
    }

    // Gestion des boutons d'ouverture de modale
    document.querySelectorAll('button[data-bs-target]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target').replace('#', '');
            openModal(targetId);
        });
    });

    // Gestion des boutons de fermeture
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal && modal.id) {
                closeModal(modal.id);
            }
        });
    });

    // Gestion des formulaires
    document.querySelectorAll('form.add-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateForm(this)) {
                return false;
            }

            console.log('Validation du formulaire...');
            const formData = new FormData(this);
            console.log('Données du formulaire:', Object.fromEntries(formData));
            
            const modalElement = this.closest('.modal');
            const modalId = modalElement ? modalElement.id : null;

            try {
                console.log('Envoi des données...');
                const response = await fetch('/panel/ajax', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Réponse non-JSON reçue:', text);
                    throw new Error('Réponse invalide du serveur');
                }

                console.log('Réponse du serveur:', data);

                if (response.ok && data.success) {
                    console.log('Soumission réussie:', data.message);
                    
                    if (modalId) {
                        closeModal(modalId);
                    }
                    
                    // Attendre la fermeture de la modale
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erreur lors de la soumission');
                }
            } catch (error) {
                console.error('Erreur détaillée:', error);
                alert('Une erreur est survenue: ' + error.message);
            }
        });
    });

    // Fonction de validation
    function validateForm(form) {
        let isValid = true;
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        return isValid;
    }

    // Gestion des formulaires de suppression
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const modalId = e.target.closest('.modal').id;
            const formData = new FormData(this);

            try {
                console.log('Envoi de la demande de suppression...');
                const response = await fetch('/panel/ajax', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    console.log('Suppression réussie');
                    
                    // Fermer la modale
                    const modal = document.getElementById(modalId);
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                    
                    // Recharger la page après un court délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue: ' + error.message);
            }
        });
    });

    // Gestion des champs d'adresse
    const adresseTypeInputs = document.querySelectorAll('input[name="adresse_type"]');
    const numeroRueFields = document.getElementById('numero_rue_fields');
    const lieuDitField = document.getElementById('lieu_dit_field');

    function toggleAdresseFields() {
        const selectedType = document.querySelector('input[name="adresse_type"]:checked').value;
        
        if (selectedType === 'numero_rue') {
            numeroRueFields.style.display = 'block';
            lieuDitField.style.display = 'none';
            // Activer les champs numéro et rue
            numeroRueFields.querySelectorAll('input').forEach(input => input.required = true);
            lieuDitField.querySelector('input').required = false;
            // Réinitialiser le lieu-dit
            lieuDitField.querySelector('input').value = '';
        } else {
            numeroRueFields.style.display = 'none';
            lieuDitField.style.display = 'block';
            // Activer le champ lieu-dit
            numeroRueFields.querySelectorAll('input').forEach(input => {
                input.required = false;
                input.value = '';
            });
            lieuDitField.querySelector('input').required = true;
        }
    }

    adresseTypeInputs.forEach(input => {
        input.addEventListener('change', toggleAdresseFields);
    });

    // Initialisation
    if (adresseTypeInputs.length > 0) {
        toggleAdresseFields();
    }
});
