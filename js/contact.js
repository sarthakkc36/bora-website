document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Form Validation & Submission
    const contactForm = document.getElementById('contactForm');
    const successModal = document.getElementById('successModal');
    const modalCloseBtn = document.querySelector('.close-modal');
    const modalOkBtn = document.getElementById('modalOkBtn');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove previous error messages
            clearErrorMessages();
            
            // Validate form
            if (validateForm()) {
                // Show loading state
                const submitBtn = contactForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                submitBtn.disabled = true;
                
                // Simulate form submission with a delay
                setTimeout(() => {
                    // Reset form
                    contactForm.reset();
                    
                    // Reset button
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                    
                    // Show success modal
                    if (successModal) {
                        successModal.classList.add('active');
                    }
                }, 1500);
            }
        });
    }
    
    // Form Validation Function
    function validateForm() {
        let isValid = true;
        
        // Get form fields
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const message = document.getElementById('message');
        const consent = document.getElementById('consent');
        
        // Validate Name
        if (!name.value.trim()) {
            showError(name, 'Please enter your name');
            isValid = false;
        }
        
        // Validate Email
        if (!email.value.trim()) {
            showError(email, 'Please enter your email address');
            isValid = false;
        } else if (!isValidEmail(email.value.trim())) {
            showError(email, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate Message
        if (!message.value.trim()) {
            showError(message, 'Please enter your message');
            isValid = false;
        }
        
        // Validate Consent
        if (!consent.checked) {
            showError(consent, 'You must agree to the data collection policy');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Helper function to display error messages
    function showError(field, message) {
        const formGroup = field.closest('.form-group');
        formGroup.classList.add('error');
        
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message show';
        errorMessage.textContent = message;
        formGroup.appendChild(errorMessage);
        
        // Add event listener to remove error on field focus
        field.addEventListener('focus', function() {
            clearError(field);
        }, { once: true });
    }
    
    // Helper function to clear error for a specific field
    function clearError(field) {
        const formGroup = field.closest('.form-group');
        formGroup.classList.remove('error');
        
        const errorMessage = formGroup.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    // Helper function to clear all error messages
    function clearErrorMessages() {
        const errorMessages = contactForm.querySelectorAll('.error-message');
        const errorFields = contactForm.querySelectorAll('.error');
        
        errorMessages.forEach(message => message.remove());
        errorFields.forEach(field => field.classList.remove('error'));
    }
    
    // Helper function to validate email format
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Modal Close Button
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function() {
            successModal.classList.remove('active');
        });
    }
    
    // Modal OK Button
    if (modalOkBtn) {
        modalOkBtn.addEventListener('click', function() {
            successModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(e) {
        if (e.target === successModal) {
            successModal.classList.remove('active');
        }
    });
    
    // FAQ Accordions
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Check if this item is already active
            const isActive = item.classList.contains('active');
            
            // Close all FAQ items
            faqItems.forEach(faq => faq.classList.remove('active'));
            
            // If clicked item wasn't active before, open it
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
    
    // Copy contact info to clipboard functionality
    const contactInfo = document.querySelectorAll('.contact-info-card p');
    
    contactInfo.forEach(info => {
        info.addEventListener('click', function() {
            // Create a temporary textarea element to copy text
            const textarea = document.createElement('textarea');
            textarea.value = this.innerText;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Show toast notification
            showToast('Contact information copied to clipboard!');
        });
        
        // Add copy cursor on hover
        info.style.cursor = 'pointer';
        
        // Add tooltip
        info.setAttribute('title', 'Click to copy');
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
                <i class="fas fa-clipboard-check"></i>
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
            color: var(--secondary-color);
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
    
    // Add animation to contact info cards
    const contactCards = document.querySelectorAll('.contact-info-card');
    
    contactCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + index * 100);
    });
    
    // Make map interactive on hover
    const mapContainer = document.querySelector('.map-container');
    
    if (mapContainer) {
        const iframe = mapContainer.querySelector('iframe');
        
        // Add pointer events none by default to allow scrolling the page
        iframe.style.pointerEvents = 'none';
        
        // Enable pointer events on hover
        mapContainer.addEventListener('mouseenter', function() {
            iframe.style.pointerEvents = 'auto';
        });
        
        // Disable pointer events when mouse leaves
        mapContainer.addEventListener('mouseleave', function() {
            iframe.style.pointerEvents = 'none';
        });
    }
});