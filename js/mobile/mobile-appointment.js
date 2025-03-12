// Mobile Appointment Booking Enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Only run on mobile devices
    if (window.innerWidth <= 768) {
        // Enhance time slot selection
        const timeSlots = document.querySelectorAll('.time-slot:not(.unavailable)');
        const appointmentForm = document.querySelector('.appointment-form');
        const submitBtn = document.getElementById('submit-btn');
        
        if (timeSlots.length > 0 && appointmentForm) {
            timeSlots.forEach(slot => {
                slot.addEventListener('click', function() {
                    // Add animation class
                    this.classList.add('time-slot-selection-animation');
                    
                    // Show selection hint
                    showTimeSelectionHint('Time selected: ' + this.textContent.trim());
                    
                    // Remove animation class after it completes
                    setTimeout(() => {
                        this.classList.remove('time-slot-selection-animation');
                    }, 300);
                });
            });
            
            // Create mobile booking button (visible when scrolling)
            const mobileBookingBtn = document.createElement('div');
            mobileBookingBtn.className = 'mobile-booking-btn';
            mobileBookingBtn.innerHTML = '<button type="button" class="submit-btn" id="mobile-submit-btn" disabled>Book Appointment</button>';
            document.body.appendChild(mobileBookingBtn);
            
            // Show/hide mobile booking button based on scroll position
            window.addEventListener('scroll', function() {
                const formBottom = appointmentForm.getBoundingClientRect().bottom;
                const windowHeight = window.innerHeight;
                
                if (formBottom < windowHeight && submitBtn) {
                    // Form submit button is in view, hide mobile button
                    mobileBookingBtn.classList.remove('visible');
                } else if (submitBtn) {
                    // Form submit button is out of view, show mobile button
                    mobileBookingBtn.classList.add('visible');
                    
                    // Sync disabled state with main button
                    const mobileSubmitBtn = document.getElementById('mobile-submit-btn');
                    if (mobileSubmitBtn) {
                        mobileSubmitBtn.disabled = submitBtn.disabled;
                    }
                }
            });
            
            // Mobile button click handler
            const mobileSubmitBtn = document.getElementById('mobile-submit-btn');
            if (mobileSubmitBtn) {
                mobileSubmitBtn.addEventListener('click', function() {
                    // Scroll to form and click the main submit button
                    appointmentForm.scrollIntoView({ behavior: 'smooth' });
                    
                    setTimeout(() => {
                        if (submitBtn) submitBtn.click();
                    }, 500);
                });
            }
            
            // Update mobile button state when time slot is selected
            const selectedTimeDisplay = document.getElementById('selected-time-display');
            const preferredTimeInput = document.getElementById('preferred_time');
            
            // Create mutation observer to watch for changes to the selected time display
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'characterData' || mutation.type === 'childList') {
                        const mobileSubmitBtn = document.getElementById('mobile-submit-btn');
                        if (mobileSubmitBtn) {
                            // Enable/disable based on whether a time is selected
                            const timeSelected = selectedTimeDisplay.textContent !== 'Please select a time slot';
                            mobileSubmitBtn.disabled = !timeSelected;
                        }
                    }
                });
            });
            
            // Start observing
            observer.observe(selectedTimeDisplay, { childList: true, characterData: true, subtree: true });
            
            // Function to show time selection hint
            function showTimeSelectionHint(message) {
                // Create or get hint element
                let hint = document.querySelector('.time-selection-hint');
                if (!hint) {
                    hint = document.createElement('div');
                    hint.className = 'time-selection-hint';
                    document.body.appendChild(hint);
                }
                
                // Set message and show
                hint.textContent = message;
                hint.classList.add('visible');
                
                // Remove visible class after animation completes
                setTimeout(() => {
                    hint.classList.remove('visible');
                }, 2000);
            }
        }
        
        // Enhanced date navigation with swipe gestures
        const weekCalendar = document.querySelector('.week-calendar');
        if (weekCalendar) {
            let touchStartX = 0;
            let touchEndX = 0;
            
            weekCalendar.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
            });
            
            weekCalendar.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].clientX;
                handleDateSwipe();
            });
            
            function handleDateSwipe() {
                const swipeThreshold = 50;
                
                // Right swipe (previous day)
                if (touchEndX - touchStartX > swipeThreshold) {
                    const prevDay = document.getElementById('prev-day');
                    if (prevDay) prevDay.click();
                }
                
                // Left swipe (next day)
                if (touchStartX - touchEndX > swipeThreshold) {
                    const nextDay = document.getElementById('next-day');
                    if (nextDay) nextDay.click();
                }
            }
        }
        
        // Make form validation more user-friendly
        const form = document.getElementById('appointment-form');
        if (form) {
            // Prevent form submission if time not selected
            form.addEventListener('submit', function(e) {
                const preferredTimeInput = document.getElementById('preferred_time');
                if (!preferredTimeInput.value) {
                    e.preventDefault();
                    
                    // Scroll to time slots and show hint
                    const timeSlotsContainer = document.querySelector('.time-slots-container');
                    if (timeSlotsContainer) {
                        timeSlotsContainer.scrollIntoView({ behavior: 'smooth' });
                        
                        // Highlight the time slot section
                        timeSlotsContainer.classList.add('highlight-section');
                        setTimeout(() => {
                            timeSlotsContainer.classList.remove('highlight-section');
                        }, 1500);
                        
                        // Show hint
                        showTimeSelectionHint('Please select a time first');
                    }
                }
            });
            
            // Add validation feedback on all required fields
            const requiredInputs = form.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                input.addEventListener('invalid', function(e) {
                    // Prevent default browser validation bubble
                    e.preventDefault();
                    
                    // Add error class to parent form group
                    const formGroup = this.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                        
                        // Create error message if it doesn't exist
                        let errorMsg = formGroup.querySelector('.error-message');
                        if (!errorMsg) {
                            errorMsg = document.createElement('div');
                            errorMsg.className = 'error-message';
                            formGroup.appendChild(errorMsg);
                        }
                        
                        // Set error message
                        errorMsg.textContent = this.validationMessage || 'This field is required';
                        
                        // Scroll to first error
                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
                
                // Clear error on input
                input.addEventListener('input', function() {
                    const formGroup = this.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.remove('has-error');
                        
                        const errorMsg = formGroup.querySelector('.error-message');
                        if (errorMsg) errorMsg.textContent = '';
                    }
                });
            });
        }
        
        // Add styles for validation and highlighting
        const style = document.createElement('style');
        style.textContent = `
            .has-error .form-control {
                border-color: #dc3545;
                background-color: #fff8f8;
            }
            
            .error-message {
                color: #dc3545;
                font-size: 12px;
                margin-top: 5px;
            }
            
            .highlight-section {
                animation: highlightBorder 1.5s ease;
            }
            
            @keyframes highlightBorder {
                0%, 100% { box-shadow: none; }
                50% { box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.5); }
            }
        `;
        document.head.appendChild(style);
        
        // Function to show time selection hint
        function showTimeSelectionHint(message) {
            // Create or get hint element
            let hint = document.querySelector('.time-selection-hint');
            if (!hint) {
                hint = document.createElement('div');
                hint.className = 'time-selection-hint';
                document.body.appendChild(hint);
            }
            
            // Set message and show
            hint.textContent = message;
            hint.classList.add('visible');
            
            // Remove visible class after animation completes
            setTimeout(() => {
                hint.classList.remove('visible');
            }, 2000);
        }
    }
});