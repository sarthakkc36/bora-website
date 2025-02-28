document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Advanced search toggle
    const searchToggle = document.querySelector('.search-toggle');
    const advancedSearch = document.querySelector('.advanced-search');
    
    if (searchToggle && advancedSearch) {
        searchToggle.addEventListener('click', function() {
            advancedSearch.classList.toggle('active');
            this.classList.toggle('active');
            
            if (advancedSearch.classList.contains('active')) {
                this.querySelector('.toggle-text').textContent = 'Hide Advanced Search';
            } else {
                this.querySelector('.toggle-text').textContent = 'Advanced Search';
            }
        });
    }
    
    // Salary range slider
    const rangeMin = document.querySelector('.range-min');
    const rangeMax = document.querySelector('.range-max');
    const minValue = document.querySelector('.min-value');
    const maxValue = document.querySelector('.max-value');
    
    if (rangeMin && rangeMax && minValue && maxValue) {
        // Function to format currency
        function formatCurrency(value) {
            return '$' + Number(value).toLocaleString();
        }
        
        // Update displayed values
        function updateValues() {
            minValue.textContent = formatCurrency(rangeMin.value);
            maxValue.textContent = formatCurrency(rangeMax.value) + (rangeMax.value == rangeMax.max ? '+' : '');
        }
        
        // Set initial values
        updateValues();
        
        // Handle min range input
        rangeMin.addEventListener('input', function() {
            if (parseInt(rangeMax.value) - parseInt(this.value) <= 0) {
                rangeMax.value = parseInt(this.value) + 5000;
            }
            updateValues();
        });
        
        // Handle max range input
        rangeMax.addEventListener('input', function() {
            if (parseInt(this.value) - parseInt(rangeMin.value) <= 0) {
                rangeMin.value = parseInt(this.value) - 5000;
            }
            updateValues();
        });
    }
    
    // Job save/bookmark functionality
    const saveButtons = document.querySelectorAll('.btn-save');
    
    saveButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('saved');
            
            if (this.classList.contains('saved')) {
                this.querySelector('i').classList.remove('far');
                this.querySelector('i').classList.add('fas');
                
                // Show feedback toast
                showToast('Job saved to bookmarks');
            } else {
                this.querySelector('i').classList.remove('fas');
                this.querySelector('i').classList.add('far');
                
                // Show feedback toast
                showToast('Job removed from bookmarks');
            }
            
            // In a real application, you would send an AJAX request to save/unsave the job
            // For now, we're just toggling the icon
        });
    });
    
    // Toast notification function
    function showToast(message) {
        // Check if a toast already exists and remove it
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-info-circle"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;
        
        // Add toast to DOM
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto hide toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
        
        // Add close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }
    
    // Add CSS for toast notifications
    const toastStyle = document.createElement('style');
    toastStyle.textContent = `
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 250px;
            max-width: 350px;
            background: var(--dark-color);
            color: white;
            border-radius: var(--border-radius-md);
            padding: 15px 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast-notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toast-content i {
            color: var(--primary-color);
        }
        
        .toast-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition-normal);
        }
        
        .toast-close:hover {
            color: white;
        }
    `;
    document.head.appendChild(toastStyle);
    
    // Job filtering functionality
    const filterForm = document.querySelector('.search-form');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // In a real application, you would gather form values and send an AJAX request
            // For demo purposes, we'll just show a searching animation
            
            const jobListings = document.querySelector('.job-listings');
            const resultsCount = document.querySelector('.results-count span');
            
            // Save current scroll position
            const scrollPos = window.pageYOffset;
            
            // Show loading animation
            jobListings.innerHTML = `
                <div class="loading-results">
                    <div class="loading-spinner"></div>
                    <p>Searching for jobs...</p>
                </div>
            `;
            
            // Add CSS for loading animation
            const loadingStyle = document.createElement('style');
            loadingStyle.textContent = `
                .loading-results {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 50px 0;
                }
                
                .loading-spinner {
                    width: 50px;
                    height: 50px;
                    border: 5px solid var(--primary-light);
                    border-top: 5px solid var(--primary-color);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 20px;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(loadingStyle);
            
            // Simulate request delay
            setTimeout(() => {
                // For demo, just reload the same content
                // In a real app, you would update with filtered results from server
                const formData = new FormData(filterForm);
                const searchParams = new URLSearchParams(formData).toString();
                
                // Add data to URL (but don't navigate)
                window.history.pushState({}, '', window.location.pathname + '?' + searchParams);
                
                // Show success message
                showToast('Search filters applied');
                
                // Restore scroll position to keep user in same area of page
                window.scrollTo(0, scrollPos);
                
                // Update results count (simulated)
                if (resultsCount) {
                    // Random number between 5 and 100 for demo
                    const randomResultCount = Math.floor(Math.random() * 95) + 5;
                    resultsCount.textContent = `${randomResultCount} Jobs Found`;
                }
                
                // Reload original content (in a real app this would be the filtered results)
                // This is just for demo purposes
                location.reload();
            }, 1500);
        });
        
        // Reset form
        filterForm.addEventListener('reset', function() {
            // Close advanced search panel
            if (advancedSearch && advancedSearch.classList.contains('active')) {
                advancedSearch.classList.remove('active');
                searchToggle.classList.remove('active');
                searchToggle.querySelector('.toggle-text').textContent = 'Advanced Search';
            }
            
            // Reset range sliders to default values
            if (rangeMin && rangeMax) {
                rangeMin.value = rangeMin.min;
                rangeMax.value = rangeMax.max;
                updateValues();
            }
            
            // Show feedback
            showToast('Search filters reset');
        });
    }
    
    // Pagination links (demo functionality)
    const paginationLinks = document.querySelectorAll('.pagination .page-link:not(.disabled)');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            paginationLinks.forEach(link => link.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // In a real app, you would load the next page of results
            // For demo, just show a loading animation and message
            
            // Scroll to top of results
            const jobResults = document.querySelector('.job-results');
            if (jobResults) {
                window.scrollTo({
                    top: jobResults.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
            
            // Show feedback
            showToast('Loading page ' + (this.textContent || 'next'));
        });
    });
    
    // Job sorting functionality
    const sortSelect = document.getElementById('sort-jobs');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            // In a real app, you would sort the results based on the selected option
            // For demo, just show a loading animation and message
            
            const jobListings = document.querySelector('.job-listings');
            
            // Save current scroll position
            const scrollPos = window.pageYOffset;
            
            // Show loading animation
            jobListings.innerHTML = `
                <div class="loading-results">
                    <div class="loading-spinner"></div>
                    <p>Sorting jobs...</p>
                </div>
            `;
            
            // Simulate request delay
            setTimeout(() => {
                // Show feedback
                showToast('Jobs sorted by ' + sortSelect.options[sortSelect.selectedIndex].text);
                
                // Restore scroll position
                window.scrollTo(0, scrollPos);
                
                // Reload original content (in a real app this would be the sorted results)
                location.reload();
            }, 1000);
        });
    }
});