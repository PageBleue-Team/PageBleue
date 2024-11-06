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
        if (!isMobile() && navSlider) {
            navSlider.style.width = `${link.offsetWidth}px`;
            navSlider.style.left = `${link.offsetLeft}px`;
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

    function toggleNavbar(show) {
        if (show === undefined) {
            show = !navbarCollapse.classList.contains("show");
        }
        
        navbarCollapse.style.transition = "transform 0.3s ease-in-out";
        
        if (show) {
            navbarCollapse.classList.add("show");
            setTimeout(() => {
                navbarCollapse.style.transform = "translateY(0)";
            }, 10);
        } else {
            navbarCollapse.style.transform = "translateY(-100%)";
            navbarCollapse.addEventListener("transitionend", function handler() {
                navbarCollapse.classList.remove("show");
                navbarCollapse.removeEventListener("transitionend", handler);
            });
        }
        
        navbarToggler.setAttribute("aria-expanded", show);
    }

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleNavbar();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const navbarToggler = document.querySelector('.navbar-toggler');
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
    
            // Empêche le comportement indésirable lors du scroll
            if (currentScroll <= 0) {
                navbar.classList.remove('scrolled-up');
                navbar.classList.remove('scrolled-down');
                return;
            }
    
            if (currentScroll > lastScroll && !navbar.classList.contains('scrolled-down')) {
                // Scroll vers le bas
                navbar.classList.remove('scrolled-up');
                navbar.classList.add('scrolled-down');
            } else if (currentScroll < lastScroll && navbar.classList.contains('scrolled-down')) {
                // Scroll vers le haut
                navbar.classList.remove('scrolled-down');
                navbar.classList.add('scrolled-up');
            }
            lastScroll = currentScroll;
        });
    
        // Fix pour l'adresse bar mobile
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    
        window.addEventListener('resize', () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        });
    });
    
    function setupMobileAnimation() {
        if (isMobile()) {
            navbarCollapse.style.transition = "transform 0.3s ease-in-out";
            navbarCollapse.style.transform = "translateY(-100%)";
        } else {
            navbarCollapse.style.transition = "";
            navbarCollapse.style.transform = "";
            navbarCollapse.classList.remove("show");
        }
    }

    setupMobileAnimation();
    window.addEventListener("resize", setupMobileAnimation);
});