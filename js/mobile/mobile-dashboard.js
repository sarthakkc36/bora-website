// Mobile Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Only run on mobile devices
    if (window.innerWidth <= 768) {
        // Create sidebar toggle button
        const dashboardContent = document.querySelector('.dashboard-content');
        const dashboardSidebar = document.querySelector('.dashboard-sidebar');
        
        if (dashboardContent && dashboardSidebar) {
            // Create toggle button
            const sidebarToggle = document.createElement('div');
            sidebarToggle.className = 'sidebar-toggle';
            sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.appendChild(sidebarToggle);
            
            // Create close button
            const closeSidebar = document.createElement('div');
            closeSidebar.className = 'close-sidebar';
            closeSidebar.innerHTML = '<i class="fas fa-times"></i>';
            dashboardSidebar.appendChild(closeSidebar);
            
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'dashboard-sidebar-overlay';
            document.body.appendChild(overlay);
            
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                dashboardSidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            // Close sidebar
            closeSidebar.addEventListener('click', function() {
                closeSidebar();
            });
            
            overlay.addEventListener('click', function() {
                closeSidebar();
            });
            
            function closeSidebar() {
                dashboardSidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Close sidebar when clicking menu items
            const menuItems = dashboardSidebar.querySelectorAll('a');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    closeSidebar();
                });
            });
        }
        
        // Create mobile tabs for dashboard (based on content boxes)
        const contentBoxes = document.querySelectorAll('.content-box');
        if (contentBoxes.length > 1) {
            // Create tabs container
            const tabsContainer = document.createElement('div');
            tabsContainer.className = 'mobile-tabs';
            
            // Add tabs based on content boxes titles
            contentBoxes.forEach((box, index) => {
                const title = box.querySelector('.content-header h2');
                if (title) {
                    const tab = document.createElement('div');
                    tab.className = 'mobile-tab';
                    tab.textContent = title.textContent.trim();
                    tab.dataset.index = index;
                    
                    // First tab is active by default
                    if (index === 0) {
                        tab.classList.add('active');
                    }
                    
                    tabsContainer.appendChild(tab);
                }
            });
            
            // Add tabs before the first content box
            if (contentBoxes[0].parentNode) {
                contentBoxes[0].parentNode.insertBefore(tabsContainer, contentBoxes[0]);
            }
            
            // Hide all content boxes except the first one
            contentBoxes.forEach((box, index) => {
                if (index > 0) {
                    box.style.display = 'none';
                }
            });
            
            // Tab click handler
            const tabs = document.querySelectorAll('.mobile-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content box
                    const index = parseInt(this.dataset.index);
                    contentBoxes.forEach((box, i) => {
                        box.style.display = i === index ? 'block' : 'none';
                    });
                    
                    // Scroll to the top of the content
                    window.scrollTo({
                        top: tabsContainer.offsetTop - 80,
                        behavior: 'smooth'
                    });
                });
            });
        }
        
        // Mobile notifications/toast
        window.showMobileToast = function(message, type = 'info') {
            // Create or get toast element
            let toast = document.querySelector('.mobile-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'mobile-toast';
                document.body.appendChild(toast);
            }
            
            // Set message and type
            toast.textContent = message;
            toast.className = `mobile-toast ${type}`;
            
            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            // Hide toast after animation completes
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        };
        
        // Enhance form usability on mobile
        const formInputs = document.querySelectorAll('input, textarea, select');
        formInputs.forEach(input => {
            // Add touch feedback
            input.addEventListener('touchstart', function() {
                this.classList.add('touch-focus');
            });
            
            input.addEventListener('blur', function() {
                this.classList.remove('touch-focus');
            });
        });
        
        // Add floating "back to top" button
        const scrollThreshold = 300;
        const backToTopBtn = document.createElement('div');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopBtn.style.display = 'none';
        document.body.appendChild(backToTopBtn);
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > scrollThreshold) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Add CSS for mobile table data attributes and back to top button
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @media (max-width: 768px) {
            .data-table, .data-table thead, .data-table tbody, .data-table th, .data-table td, .data-table tr {
                display: block;
            }
            
            .data-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            
            .data-table tr {
                border: 1px solid #ddd;
                margin-bottom: 15px;
                border-radius: 8px;
                overflow: hidden;
            }
            
            .data-table td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50% !important;
                text-align: right;
            }
            
            .data-table td:last-child {
                border-bottom: 0;
            }
            
            .data-table td:before {
                content: attr(data-label);
                position: absolute;
                top: 50%;
                left: 10px;
                width: 45%;
                transform: translateY(-50%);
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                text-align: left;
            }
            
            .table-swipe-hint {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                opacity: 0.9;
                transition: opacity 0.3s ease;
            }
            
            .back-to-top {
                position: fixed;
                bottom: 90px;
                right: 20px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: #0066cc;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 90;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            }
            
            .touch-focus {
                box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
            }
        }
    `;
    document.head.appendChild(style);
});
