/**
 * Update your JavaScript to ensure the auth-menu is visible when the mobile menu is toggled
 * Add this to your script.js file
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNavigation = document.querySelector('.main-navigation');
    const authMenu = document.querySelector('.auth-menu');
    
    if (mobileMenuBtn && mainNavigation) {
        mobileMenuBtn.addEventListener('click', function() {
            // Toggle active class on menu button
            mobileMenuBtn.classList.toggle('active');
            
            // Toggle active class on main navigation
            mainNavigation.classList.toggle('active');
            
            // Make sure auth menu is visible in mobile view
            if (window.innerWidth <= 992 && authMenu) {
                authMenu.style.display = 'block';
            }
            
            // Toggle body class for preventing scroll
            document.body.classList.toggle('menu-open');
            
            // Log for debugging
            console.log('Mobile menu toggled');
            console.log('Auth menu exists:', !!authMenu);
            console.log('Auth menu items:', authMenu ? authMenu.querySelectorAll('li').length : 0);
        });
    }
    
    // Handle window resize to ensure auth menu displays correctly
    window.addEventListener('resize', function() {
        if (authMenu) {
            if (window.innerWidth <= 992) {
                // On mobile
                if (mainNavigation.classList.contains('active')) {
                    authMenu.style.display = 'block';
                }
            } else {
                // On desktop
                authMenu.style.display = '';
            }
        }
    });
    
    // Initialize auth menu visibility
    if (authMenu && window.innerWidth <= 992 && mainNavigation.classList.contains('active')) {
        authMenu.style.display = 'block';
    }
});