// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNavigation = document.querySelector('.main-navigation');
    const navMenu = document.querySelector('.nav-menu');
    const authMenu = document.querySelector('.auth-menu');
    const body = document.body;
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenuBtn.classList.toggle('active');
            mainNavigation.classList.toggle('active');
            navMenu.classList.toggle('active');
            if (authMenu) authMenu.classList.toggle('active');
            body.classList.toggle('menu-open');
            
            // Create menu overlay
            let overlay = document.querySelector('.menu-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'menu-overlay';
                document.body.appendChild(overlay);
            }
            
            overlay.classList.toggle('active');
            
            // Close menu when clicking overlay
            overlay.addEventListener('click', function() {
                closeMenu();
            });
            
            // Add animation to menu items
            const menuItems = document.querySelectorAll('.nav-menu li, .auth-menu li');
            menuItems.forEach((item, index) => {
                // Reset animation
                item.style.animation = 'none';
                item.offsetHeight; // Trigger reflow
                
                // Apply animation with delay based on index
                if (mainNavigation.classList.contains('active')) {
                    item.style.animation = `slideIn 0.3s forwards ${index * 0.05}s`;
                } else {
                    item.style.animation = '';
                }
            });
        });
    }
    
    // Close menu function
    function closeMenu() {
        mobileMenuBtn.classList.remove('active');
        mainNavigation.classList.remove('active');
        navMenu.classList.remove('active');
        if (authMenu) authMenu.classList.remove('active');
        body.classList.remove('menu-open');
        
        const overlay = document.querySelector('.menu-overlay');
        if (overlay) overlay.classList.remove('active');
    }
    
    // Close menu when clicking on menu items
    const menuLinks = document.querySelectorAll('.nav-menu a, .auth-menu a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only close if it's mobile view
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });
    
    // Close menu on resize if window gets larger
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mainNavigation.classList.contains('active')) {
            closeMenu();
        }
    });
    
    // Header scroll effects
    const header = document.querySelector('header');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add 'scrolled' class when scrolling down
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide header when scrolling down, show when scrolling up
        if (currentScroll > lastScrollTop && currentScroll > 200) {
            // Scrolling down
            header.classList.add('hidden');
        } else {
            // Scrolling up
            header.classList.remove('hidden');
        }
        
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    });
    
    // Improve form usability on mobile
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        // Add padding when focused to prevent content from being hidden behind fixed header
        input.addEventListener('focus', function() {
            if (window.innerWidth <= 768) {
                const inputRect = this.getBoundingClientRect();
                const headerHeight = header.offsetHeight;
                
                if (inputRect.top < headerHeight + 20) {
                    window.scrollBy(0, -(headerHeight + 20 - inputRect.top));
                }
            }
        });
    });
    
    // Adjust hero section on mobile
    const heroSection = document.querySelector('.hero-image-only');
    if (heroSection) {
        // Adjust height based on content
        const adjustHeroHeight = () => {
            if (window.innerWidth <= 768) {
                const heroContent = heroSection.querySelector('.hero-content');
                const cta = heroSection.querySelector('.cta-container');
                
                if (heroContent && cta) {
                    const contentHeight = heroContent.offsetHeight;
                    const ctaHeight = cta.offsetHeight;
                    const minHeight = contentHeight + ctaHeight + 80; // Adding some padding
                    
                    heroSection.style.minHeight = `${minHeight}px`;
                }
            } else {
                heroSection.style.minHeight = '600px';
            }
        };
        
        // Run on load and resize
        adjustHeroHeight();
        window.addEventListener('resize', adjustHeroHeight);
    }
    
    // Make tables responsive
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
    
    // Add swipe functionality for job cards on mobile
    const jobCards = document.querySelectorAll('.job-card');
    if (jobCards.length > 0 && window.innerWidth <= 768) {
        let touchStartX = 0;
        let touchEndX = 0;
        
        jobCards.forEach(card => {
            card.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            card.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe(this);
            });
        });
        
        function handleSwipe(card) {
            const threshold = 100; // Minimum distance for swipe
            
            // Swipe left (save job)
            if (touchStartX - touchEndX > threshold) {
                const saveBtn = card.querySelector('.job-save');
                if (saveBtn) saveBtn.click();
            }
            
            // Swipe right (apply to job)
            if (touchEndX - touchStartX > threshold) {
                const applyBtn = card.querySelector('.apply-btn');
                if (applyBtn) window.location.href = applyBtn.getAttribute('href');
            }
        }
    }
    
    // Add pull-to-refresh animation
    let startY = 0;
    let refreshStarted = false;
    const refreshThreshold = 80;
    
    // Only add this for mobile
    if (window.innerWidth <= 768) {
        document.addEventListener('touchstart', function(e) {
            // Only trigger when at top of page
            if (window.scrollY === 0) {
                startY = e.touches[0].pageY;
                refreshStarted = true;
            }
        });
        
        document.addEventListener('touchmove', function(e) {
            if (!refreshStarted) return;
            
            const y = e.touches[0].pageY;
            const distance = y - startY;
            
            // Only pull when positive distance (pulling down)
            if (distance > 0 && window.scrollY === 0) {
                let refresh = document.querySelector('.pull-refresh');
                
                if (!refresh) {
                    refresh = document.createElement('div');
                    refresh.className = 'pull-refresh';
                    refresh.innerHTML = '<div class="refresh-icon"><i class="fas fa-sync-alt"></i></div>';
                    document.body.prepend(refresh);
                }
                
                // Calculate height based on pull distance
                const height = Math.min(distance * 0.5, refreshThreshold);
                refresh.style.height = height + 'px';
                
                // Rotate icon based on pull distance
                const icon = refresh.querySelector('.refresh-icon');
                icon.style.transform = `rotate(${height * 4}deg)`;
                
                if (height >= refreshThreshold) {
                    refresh.classList.add('ready');
                } else {
                    refresh.classList.remove('ready');
                }
            
            }
        });
        
        document.addEventListener('touchend', function() {
            if (!refreshStarted) return;
            
            const refresh = document.querySelector('.pull-refresh');
            if (refresh) {
                if (refresh.classList.contains('ready')) {
                    refresh.innerHTML = '<div class="refresh-loading"><i class="fas fa-circle-notch fa-spin"></i></div>';
                    
                    // Reload page after animation
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    refresh.style.height = '0';
                    setTimeout(() => {
                        if (refresh.parentNode) {
                            refresh.parentNode.removeChild(refresh);
                        }
                    }, 300);
                }
            }
            
            refreshStarted = false;
        });
    }
});