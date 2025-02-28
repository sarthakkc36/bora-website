document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Password Toggle Visibility
    const passwordToggle = document.querySelector('.password-toggle');
    const passwordField = document.getElementById('password');
    
    if (passwordToggle && passwordField) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Confirm Password Toggle Visibility
    const confirmPasswordToggle = document.querySelector('.confirm-password-toggle');
    const confirmPasswordField = document.getElementById('confirmPassword');
    
    if (confirmPasswordToggle && confirmPasswordField) {
        confirmPasswordToggle.addEventListener('click', function() {
            const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Login Form Validation & Submission
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove previous error messages
            const prevErrors = this.querySelectorAll('.error-message');
            prevErrors.forEach(error => error.remove());
            
            // Reset form fields
            this.querySelectorAll('input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Get form values
            const email = this.querySelector('#email').value.trim();
            const password = this.querySelector('#password').value.trim();
            
            // Basic validation
            let isValid = true;
            
            if (!email) {
                showError('#email', 'Email address is required');
                isValid = false;
            } else if (!isValidEmail(email)) {
                showError('#email', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (!password) {
                showError('#password', 'Password is required');
                isValid = false;
            }
            
            if (isValid) {
                // In a real app, you would send an AJAX request to the server
                // For this demo, we'll simulate a successful login and redirect
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                submitBtn.disabled = true;
                
                // Simulate server request
                setTimeout(() => {
                    // For demo purposes, let's simulate a successful login
                    
                    // In a real application, you'd check the server's response
                    // If login is successful:
                    
                    // Store user info in localStorage/sessionStorage for session management
                    localStorage.setItem('isLoggedIn', 'true');
                    localStorage.setItem('user', JSON.stringify({
                        email: email,
                        name: email.split('@')[0] // Just for demo
                    }));
                    
                    // Redirect to dashboard or homepage
                    window.location.href = 'index.html';
                    
                    // If login fails:
                    // showError('#email', 'Invalid email or password');
                    // submitBtn.innerHTML = originalText;
                    // submitBtn.disabled = false;
                    
                }, 1500);
            }
        });
    }
    
    // Registration Form Validation & Submission
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        const passwordInput = registerForm.querySelector('#password');
        const confirmPasswordInput = registerForm.querySelector('#confirmPassword');
        
        // Password strength checker
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }
        
        // Password match checker
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (password !== confirmPassword) {
                    showError('#confirmPassword', 'Passwords do not match');
                } else {
                    // Remove error
                    const errorElement = confirmPasswordInput.parentElement.parentElement.querySelector('.error-message');
                    if (errorElement) {
                        errorElement.remove();
                    }
                    confirmPasswordInput.classList.remove('error');
                }
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove previous error messages
            const prevErrors = this.querySelectorAll('.error-message');
            prevErrors.forEach(error => error.remove());
            
            // Reset form fields
            this.querySelectorAll('input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Get form values
            const fullName = this.querySelector('#fullName')?.value.trim();
            const email = this.querySelector('#email').value.trim();
            const password = this.querySelector('#password').value.trim();
            const confirmPassword = this.querySelector('#confirmPassword')?.value.trim();
            const termsCheck = this.querySelector('#termsCheck')?.checked;
            
            // Basic validation
            let isValid = true;
            
            if (fullName !== undefined && !fullName) {
                showError('#fullName', 'Full name is required');
                isValid = false;
            }
            
            if (!email) {
                showError('#email', 'Email address is required');
                isValid = false;
            } else if (!isValidEmail(email)) {
                showError('#email', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (!password) {
                showError('#password', 'Password is required');
                isValid = false;
            } else if (password.length < 8) {
                showError('#password', 'Password must be at least 8 characters long');
                isValid = false;
            }
            
            if (confirmPassword !== undefined) {
                if (!confirmPassword) {
                    showError('#confirmPassword', 'Please confirm your password');
                    isValid = false;
                } else if (password !== confirmPassword) {
                    showError('#confirmPassword', 'Passwords do not match');
                    isValid = false;
                }
            }
            
            if (termsCheck !== undefined && !termsCheck) {
                showError('#termsCheck', 'You must agree to the Terms and Conditions');
                isValid = false;
            }
            
            if (isValid) {
                // In a real app, you would send an AJAX request to the server
                // For this demo, we'll simulate a successful registration
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                submitBtn.disabled = true;
                
                // Simulate server request
                setTimeout(() => {
                    // For demo purposes, let's simulate a successful registration
                    
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message visible';
                    successMessage.textContent = 'Registration successful! Redirecting to login...';
                    
                    this.insertBefore(successMessage, this.firstChild);
                    
                    // Reset form
                    this.reset();
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // In a real app, you might redirect to login page
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                    
                }, 1500);
            }
        });
    }
    
    // Forgot Password Form
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove previous error messages
            const prevErrors = this.querySelectorAll('.error-message');
            prevErrors.forEach(error => error.remove());
            
            // Reset form fields
            this.querySelectorAll('input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Get form values
            const email = this.querySelector('#email').value.trim();
            
            // Basic validation
            let isValid = true;
            
            if (!email) {
                showError('#email', 'Email address is required');
                isValid = false;
            } else if (!isValidEmail(email)) {
                showError('#email', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Reset Link...';
                submitBtn.disabled = true;
                
                // Simulate server request
                setTimeout(() => {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message visible';
                    successMessage.textContent = 'Password reset link has been sent to your email address.';
                    
                    this.insertBefore(successMessage, this.firstChild);
                    
                    // Reset form
                    this.reset();
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }
        });
    }
    
    // Password Reset Form
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Remove previous error messages
            const prevErrors = this.querySelectorAll('.error-message');
            prevErrors.forEach(error => error.remove());
            
            // Reset form fields
            this.querySelectorAll('input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Get form values
            const password = this.querySelector('#password').value.trim();
            const confirmPassword = this.querySelector('#confirmPassword').value.trim();
            
            // Basic validation
            let isValid = true;
            
            if (!password) {
                showError('#password', 'New password is required');
                isValid = false;
            } else if (password.length < 8) {
                showError('#password', 'Password must be at least 8 characters long');
                isValid = false;
            }
            
            if (!confirmPassword) {
                showError('#confirmPassword', 'Please confirm your new password');
                isValid = false;
            } else if (password !== confirmPassword) {
                showError('#confirmPassword', 'Passwords do not match');
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting Password...';
                submitBtn.disabled = true;
                
                // Simulate server request
                setTimeout(() => {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message visible';
                    successMessage.textContent = 'Your password has been reset successfully! Redirecting to login...';
                    
                    this.insertBefore(successMessage, this.firstChild);
                    
                    // Reset form
                    this.reset();
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Redirect to login page
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                }, 1500);
            }
        });
    }
    
    // Helper functions
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function showError(selector, message) {
        const inputElement = document.querySelector(selector);
        
        if (inputElement) {
            // Add error class to input
            inputElement.classList.add('error');
            
            // Create error message
            const errorElement = document.createElement('div');
            errorElement.className = 'error-message visible';
            errorElement.textContent = message;
            
            // Insert error message after input group
            const parent = selector === '#termsCheck' ? 
                inputElement.closest('.checkbox-label').parentElement : 
                inputElement.parentElement.parentElement;
            
            parent.appendChild(errorElement);
        }
    }
    
    function checkPasswordStrength(password) {
        const requirements = document.querySelector('.password-requirements');
        
        if (!requirements) return;
        
        const lengthReq = requirements.querySelector('#length-req');
        const upperReq = requirements.querySelector('#upper-req');
        const lowerReq = requirements.querySelector('#lower-req');
        const numberReq = requirements.querySelector('#number-req');
        const specialReq = requirements.querySelector('#special-req');
        
        // Check each requirement
        if (lengthReq) {
            if (password.length >= 8) {
                lengthReq.classList.add('met');
                lengthReq.classList.remove('unmet');
            } else {
                lengthReq.classList.add('unmet');
                lengthReq.classList.remove('met');
            }
        }
        
        if (upperReq) {
            if (/[A-Z]/.test(password)) {
                upperReq.classList.add('met');
                upperReq.classList.remove('unmet');
            } else {
                upperReq.classList.add('unmet');
                upperReq.classList.remove('met');
            }
        }
        
        if (lowerReq) {
            if (/[a-z]/.test(password)) {
                lowerReq.classList.add('met');
                lowerReq.classList.remove('unmet');
            } else {
                lowerReq.classList.add('unmet');
                lowerReq.classList.remove('met');
            }
        }
        
        if (numberReq) {
            if (/\d/.test(password)) {
                numberReq.classList.add('met');
                numberReq.classList.remove('unmet');
            } else {
                numberReq.classList.add('unmet');
                numberReq.classList.remove('met');
            }
        }
        
        if (specialReq) {
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                specialReq.classList.add('met');
                specialReq.classList.remove('unmet');
            } else {
                specialReq.classList.add('unmet');
                specialReq.classList.remove('met');
            }
        }
    }
    
    // Check for existing login session
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    const currentPath = window.location.pathname;
    
    if (isLoggedIn) {
        // If user is already logged in and tries to access auth pages
        if (currentPath.includes('login.html') || 
            currentPath.includes('register.html') || 
            currentPath.includes('forgot-password.html')) {
            
            // Redirect to dashboard or home
            window.location.href = 'index.html';
        }
        
        // If user is logged in, update UI accordingly
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        
        // Update auth button display
        const authButtons = document.querySelector('.auth-buttons');
        if (authButtons) {
            authButtons.innerHTML = `
                <div class="dropdown">
                    <button class="dropdown-toggle">
                        <span>Hello, ${user.name || 'User'}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="profile.html"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="applications.html"><i class="fas fa-clipboard-list"></i> My Applications</a></li>
                        <li><a href="saved-jobs.html"><i class="fas fa-bookmark"></i> Saved Jobs</a></li>
                        <li class="divider"></li>
                        <li><a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            `;
            
            // Dropdown toggle functionality
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function() {
                    this.parentElement.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.dropdown')) {
                        document.querySelector('.dropdown')?.classList.remove('active');
                    }
                });
                
                // Logout functionality
                document.getElementById('logoutBtn')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Clear user data from localStorage
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('user');
                    
                    // Redirect to login page
                    window.location.href = 'login.html';
                });
            }
        }
    }
});
// Fix for the register page terms and conditions modal display
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Terms Modal Functionality
    const termsLink = document.getElementById('termsLink');
    const privacyLink = document.getElementById('privacyLink');
    const termsModal = document.getElementById('termsModal');
    const privacyModal = document.getElementById('privacyModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const acceptTermsBtn = document.getElementById('acceptTerms');
    const acceptPrivacyBtn = document.getElementById('acceptPrivacy');
    const termsCheck = document.getElementById('termsCheck');
    
    // Function to open modal
    function openModal(modal) {
        if (modal) {
            // Make sure modal has proper styles
            modal.style.display = 'flex';
            modal.classList.add('active');
            
            // Add necessary styles if not already set by CSS
            if (!modal.style.alignItems) {
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                modal.style.zIndex = '1000';
            }
        }
    }
    
    // Function to close modal
    function closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }
    
    // Terms link click handler
    if (termsLink) {
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(termsModal);
        });
    }
    
    // Privacy link click handler
    if (privacyLink) {
        privacyLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(privacyModal);
        });
    }
    
    // Close buttons click handlers
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Accept terms button
    if (acceptTermsBtn) {
        acceptTermsBtn.addEventListener('click', function() {
            if (termsCheck) {
                termsCheck.checked = true;
            }
            closeModal(termsModal);
        });
    }
    
    // Accept privacy button
    if (acceptPrivacyBtn) {
        acceptPrivacyBtn.addEventListener('click', function() {
            if (termsCheck) {
                termsCheck.checked = true;
            }
            closeModal(privacyModal);
        });
    }
    
    // Close modal when clicking outside content
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });
    
    // Add extra CSS for modals if needed
    const modalStyle = document.createElement('style');
    modalStyle.textContent = `
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            opacity: 1;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: modalFadeIn 0.3s ease;
            position: relative;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin-bottom: 0;
            font-size: 1.5rem;
        }
        
        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
            transition: color 0.3s ease;
        }
        
        .close-modal:hover {
            color: #0056b3;
        }
        
        .modal-body {
            padding: 15px;
        }
        
        .modal-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }
    `;
    document.head.appendChild(modalStyle);
});