// Mobile Job Listings Interactions
document.addEventListener('DOMContentLoaded', function() {
    // Only run on mobile devices
    if (window.innerWidth <= 768) {
        // Show swipe instructions on first visit
        if (!localStorage.getItem('swipeInstructionsShown')) {
            const instructionsEl = document.createElement('div');
            instructionsEl.className = 'swipe-instructions';
            instructionsEl.innerHTML = '<i class="fas fa-hand-point-right"></i> Swipe left to save, swipe right to apply';
            
            const jobsList = document.querySelector('.jobs-grid, .jobs-list');
            if (jobsList) {
                jobsList.prepend(instructionsEl);
                
                // Store in localStorage so it's only shown once
                localStorage.setItem('swipeInstructionsShown', 'true');
                
                // Remove after 5 seconds
                setTimeout(() => {
                    instructionsEl.style.opacity = '0';
                    setTimeout(() => {
                        instructionsEl.remove();
                    }, 300);
                }, 5000);
            }
        }
        
        // Add swipe functionality to job cards
        const jobItems = document.querySelectorAll('.job-item, .job-card');
        
        if (jobItems.length > 0) {
            // Add swipe elements to each job card
            jobItems.forEach(item => {
                // Add swipe action indicators
                const swipeLeft = document.createElement('div');
                swipeLeft.className = 'swipe-action swipe-left';
                swipeLeft.innerHTML = '<i class="fas fa-bookmark"></i>';
                
                const swipeRight = document.createElement('div');
                swipeRight.className = 'swipe-action swipe-right';
                swipeRight.innerHTML = '<i class="fas fa-paper-plane"></i>';
                
                item.appendChild(swipeLeft);
                item.appendChild(swipeRight);
                
                // Initialize touch variables
                let touchStartX = 0;
                let touchEndX = 0;
                let currentX = 0;
                let initialX = 0;
                let xOffset = 0;
                
                // Handle touch start
                item.addEventListener('touchstart', function(e) {
                    initialX = e.touches[0].clientX - xOffset;
                    touchStartX = e.touches[0].clientX;
                    
                    // Reset any previous transforms
                    this.style.transform = 'translateX(0)';
                    this.classList.remove('swiping-left', 'swiping-right');
                }, false);
                
                // Handle touch move
                item.addEventListener('touchmove', function(e) {
                    currentX = e.touches[0].clientX - initialX;
                    
                    // Limit the swipe to a reasonable amount
                    if (currentX > 100) currentX = 100;
                    if (currentX < -100) currentX = -100;
                    
                    this.style.transform = `translateX(${currentX}px)`;
                    
                    // Show appropriate swipe indicator
                    if (currentX < -50) {
                        this.classList.add('swiping-left');
                        this.classList.remove('swiping-right');
                    } else if (currentX > 50) {
                        this.classList.add('swiping-right');
                        this.classList.remove('swiping-left');
                    } else {
                        this.classList.remove('swiping-left', 'swiping-right');
                    }
                    
                    e.preventDefault();
                }, false);
                
                // Handle touch end
                item.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].clientX;
                    
                    // Reset transform with animation
                    this.style.transition = 'transform 0.3s ease';
                    this.style.transform = 'translateX(0)';
                    
                    // Handle the swipe action
                    const swipeThreshold = 80;
                    
                    if (touchStartX - touchEndX > swipeThreshold) {
                        // Swipe left - save job
                        const saveBtn = this.querySelector('.job-save');
                        if (saveBtn) {
                            // Visual feedback
                            showGestureTooltip('Job saved!');
                            saveBtn.click();
                        }
                    } else if (touchEndX - touchStartX > swipeThreshold) {
                        // Swipe right - apply to job
                        const applyBtn = this.querySelector('.apply-btn');
                        if (applyBtn) {
                            // Visual feedback
                            showGestureTooltip('Opening job details...');
                            // Small delay for feedback
                            setTimeout(() => {
                                window.location.href = applyBtn.getAttribute('href');
                            }, 300);
                        }
                    }
                    
                    // Reset classes
                    setTimeout(() => {
                        this.classList.remove('swiping-left', 'swiping-right');
                        this.style.transition = '';
                    }, 300);
                    
                    // Reset variables
                    initialX = currentX = xOffset = 0;
                }, false);
            });
        }
        
        // Filter toggle on mobile
        const filterToggle = document.querySelector('.filter-toggle');
        const filtersContainer = document.querySelector('.filters-container');
        
        if (filterToggle && filtersContainer) {
            filterToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                filtersContainer.classList.toggle('active');
            });
        }
        
        // Mobile filter button
        const mobileFilterBtn = document.createElement('div');
        mobileFilterBtn.className = 'mobile-filter-btn';
        mobileFilterBtn.innerHTML = '<i class="fas fa-filter"></i>';
        document.body.appendChild(mobileFilterBtn);
        
        // Create mobile filter panel
        const mobileFilterPanel = document.createElement('div');
        mobileFilterPanel.className = 'mobile-filter-panel';
        
        // Copy desktop filters to mobile panel
        const desktopFilters = document.querySelector('.job-filters');
        if (desktopFilters) {
            const filterContent = desktopFilters.cloneNode(true);
            filterContent.classList.add('desktop-filters');
            
            // Create header for mobile filter panel
            const panelHeader = document.createElement('div');
            panelHeader.className = 'mobile-filter-header';
            panelHeader.innerHTML = '<h3>Filter Jobs</h3><div class="mobile-filter-close"><i class="fas fa-times"></i></div>';
            
            // Add filter actions
            const filterActions = document.createElement('div');
            filterActions.className = 'filter-actions';
            filterActions.innerHTML = '<button class="btn-secondary clear-filters">Clear Filters</button><button class="btn-primary apply-filters">Apply Filters</button>';
            
            // Build panel
            mobileFilterPanel.appendChild(panelHeader);
            mobileFilterPanel.appendChild(filterContent);
            mobileFilterPanel.appendChild(filterActions);
            document.body.appendChild(mobileFilterPanel);
            
            // Handle filter button click
            mobileFilterBtn.addEventListener('click', function() {
                mobileFilterPanel.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            // Close panel
            const closePanel = mobileFilterPanel.querySelector('.mobile-filter-close');
            if (closePanel) {
                closePanel.addEventListener('click', function() {
                    mobileFilterPanel.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
            
            // Apply filters
            const applyFiltersBtn = mobileFilterPanel.querySelector('.apply-filters');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    // Get all form values from mobile panel
                    const filterForm = document.querySelector('.desktop-filters form');
                    if (filterForm) {
                        filterForm.submit();
                    } else {
                        // If no form, just close the panel
                        mobileFilterPanel.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
            
            // Clear filters
            const clearFiltersBtn = mobileFilterPanel.querySelector('.clear-filters');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    // Reset all inputs in the mobile panel
                    const inputs = mobileFilterPanel.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    
                    // If there's a clear button on the page, click it
                    const mainClearBtn = document.querySelector('.clear-all');
                    if (mainClearBtn) {
                        mainClearBtn.click();
                    }
                });
            }
        }
        
        // Apply button on job details page
        const jobDetailsPage = document.querySelector('.job-details');
        if (jobDetailsPage) {
            const applySection = document.querySelector('.job-application');
            if (applySection) {
                // Add floating apply button
                const applyFab = document.createElement('div');
                applyFab.className = 'apply-now-fab';
                applyFab.innerHTML = '<i class="fas fa-paper-plane"></i>';
                document.body.appendChild(applyFab);
                
                // Toggle application form when clicking fab
                applyFab.addEventListener('click', function() {
                    applySection.classList.toggle('active');
                    
                    // Scroll to application section if not active
                    if (!applySection.classList.contains('active')) {
                        const applicationAnchor = document.querySelector('#apply-section');
                        if (applicationAnchor) {
                            applicationAnchor.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            }
        }
    }
    
    // Function to show a tooltip for gesture feedback
    function showGestureTooltip(message) {
        // Create or get tooltip element
        let tooltip = document.querySelector('.gesture-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'gesture-tooltip';
            document.body.appendChild(tooltip);
        }
        
        // Set message and show
        tooltip.textContent = message;
        tooltip.classList.add('visible');
        
        // Remove visible class after animation completes
        setTimeout(() => {
            tooltip.classList.remove('visible');
        }, 3000);
    }
});