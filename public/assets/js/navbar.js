addEventListener("DOMContentLoaded", function() {
    const navSlider = document.querySelector(".nav-slider");
    const navLinks = document.querySelectorAll(".nav-link");
    const navContainer = document.querySelector(".navbar-nav");
    const navbarCollapse = document.querySelector(".navbar-collapse");
    const navbarToggler = document.getElementById("navbarToggler");
    
    const isPanel = window.location.pathname.includes("/panel");
    
    function isMobile() {
        return window.innerWidth <= 991;
    }

    function moveSlider(link) {
        if (!isMobile()) {
            const navItem = link.closest('.nav-item');
            const slider = navItem.querySelector('.nav-slider');
            if (slider) {
                slider.style.width = '100%';
            }
        }
    }

    function resetSlider() {
        if (!isMobile() && navSlider) {
            const activeLink = document.querySelector(".nav-link.active") || navLinks[0];
            moveSlider(activeLink);
        }
    }

    navLinks.forEach(link => {
        link.addEventListener("mouseenter", () => {
            if (!isPanel) {
                moveSlider(link);
            }
        });
        link.addEventListener("click", (e) => {
            if (isMobile()) {
                e.preventDefault();
                navLinks.forEach(l => l.classList.remove("active"));
                link.classList.add("active");
                toggleNavbar(false);
                setTimeout(() => {
                    window.location.href = link.href;
                }, 300);
            }
        });
    });

    navContainer.addEventListener("mouseleave", () => {
        if (!isPanel) {
            resetSlider();
        }
    });

    window.addEventListener("resize", () => {
        if (!isPanel) {
            resetSlider();
        }
    });

    if (!isPanel) {
        resetSlider();
    } else {
        const panelLink = document.querySelector(`.nav-link[href="/panel"]`);
        if (panelLink) {
            moveSlider(panelLink);
        }
    }

    const searchInput = document.getElementById("search-input");
    searchInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const searchTerm = searchInput.value;
            if (searchTerm.length > 2) {
                window.location.href = `/?search=${encodeURIComponent(searchTerm)}`;
            }
        }
    });

    if (navbarToggler) {
        navbarToggler.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleNavbar();
        });
    }

    function toggleNavbar(show) {
        const isCurrentlyShown = navbarCollapse.classList.contains("show");
        const shouldShow = show !== undefined ? show : !isCurrentlyShown;
        
        if (shouldShow) {
            navbarCollapse.classList.add("show");
            document.body.classList.add('menu-open');
        } else {
            navbarCollapse.classList.remove("show");
            document.body.classList.remove('menu-open');
        }
        
        navbarToggler.setAttribute("aria-expanded", shouldShow);
    }

    const body = document.body;
    let lastScroll = 0;

    // Gestion de l'ouverture/fermeture du menu
    navbarToggler.addEventListener('click', function() {
        body.classList.toggle('menu-open');
    });

    // Gestion du scroll
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        const navbar = document.querySelector('.navbar');

        if (currentScroll <= 0) {
            navbar.classList.remove('scrolled-up');
            navbar.classList.remove('scrolled-down');
            return;
        }

        if (currentScroll > lastScroll && !navbar.classList.contains('scrolled-down')) {
            navbar.classList.remove('scrolled-up');
            navbar.classList.add('scrolled-down');
        } else if (currentScroll < lastScroll && navbar.classList.contains('scrolled-down')) {
            navbar.classList.remove('scrolled-down');
            navbar.classList.add('scrolled-up');
        }
        lastScroll = currentScroll;
    });

    // Fix pour l'adresse bar mobile
    const updateVh = () => {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    };

    updateVh();
    window.addEventListener('resize', updateVh);

    function setupMobileAnimation() {
        if (isMobile()) {
            navbarCollapse.classList.remove("show");
            document.body.classList.remove('menu-open');
        }
    }

    setupMobileAnimation();
    window.addEventListener("resize", setupMobileAnimation);

    resetSlider();

    // Ajouter cet événement pour gérer les clics en dehors
    document.addEventListener('click', function(e) {
        if (navbarCollapse.classList.contains('show')) {
            const isClickInside = navbarCollapse.contains(e.target) || 
                                navbarToggler.contains(e.target);
            if (!isClickInside) {
                toggleNavbar(false);
            }
        }
    });
});