document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // File Upload Functionality
    const fileInput = document.getElementById('companyLogo');
    const fileName = document.getElementById('noFile');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileName.textContent = this.files[0].name;
                
                // File size validation
                const fileSize = this.files[0].size; // in bytes
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (fileSize > maxSize) {
                    alert('File size exceeds 2MB. Please choose a smaller file.');
                    this.value = '';
                    fileName.textContent = 'No file chosen...';
                }
            } else {
                fileName.textContent = 'No file chosen...';
            }
        });
    }
    
    // Package Selection
    const packageCards = document.querySelectorAll('.package-card');
    const packageRadios = document.querySelectorAll('input[name="package"]');
    
    packageCards.forEach(card => {
        card.addEventListener('click', function() {
            // Get the radio button inside this card
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                
                // Remove selected class from all cards
                packageCards.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
            }
        });
    });
    
    packageRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected class from all cards
            packageCards.forEach(card => card.classList.remove('selected'));
            
            // Add selected class to card containing checked radio
            if (this.checked) {
                this.closest('.package-card').classList.add('selected');
            }
        });
    });
    
    // FAQ Toggle
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Close all FAQ items
            faqItems.forEach(faq => faq.classList.remove('active'));
            
            // If clicked item wasn't active before, open it
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
    
    // Terms Modal
    const termsLink = document.getElementById('termsLink');
    const termsModal = document.getElementById('termsModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const acceptTermsBtn = document.getElementById('acceptTerms');
    const termsCheck = document.getElementById('termsCheck');
    
    if (termsLink) {
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            termsModal.classList.add('active');
        });
    }
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('active');
            });
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.modal').forEach(modal => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    if (acceptTermsBtn) {
        acceptTermsBtn.addEventListener('click', function() {
            termsCheck.checked = true;
            termsModal.classList.remove('active');
        });
    }
    
    // Form Validation & Submission
    const jobPostForm = document.getElementById('jobPostForm');
    const successModal = document.getElementById('successModal');
    const modalOkBtn = document.getElementById('modalOkBtn');
    const saveAsDraftBtn = document.getElementById('saveAsDraft');
    
    if (jobPostForm) {
        jobPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                // Mock form submission (would be an AJAX call to backend in production)
                // In a real application, you would send the form data to your server here
                
                // Show success modal
                successModal.classList.add('active');
                
                // Reset form
                // Uncomment the line below in production to actually reset the form
                // jobPostForm.reset();
            }
        });
    }
    
    function validateForm() {
        let isValid = true;
        const requiredFields = jobPostForm.querySelectorAll('[required]');
        
        // Remove all existing error messages
        const errorMessages = jobPostForm.querySelectorAll('.form-message.error');
        errorMessages.forEach(msg => msg.remove());
        
        // Reset fields (remove error class)
        requiredFields.forEach(field => {
            field.classList.remove('error');
        });
        
        // Check each required field
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
                
                // Add field-specific error
                const fieldGroup = field.closest('.form-group');
                if (fieldGroup && !fieldGroup.querySelector('.form-message.error')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'form-message error';
                    errorMsg.textContent = 'This field is required';
                    fieldGroup.appendChild(errorMsg);
                }
            } else if (field.type === 'email' && !isValidEmail(field.value.trim())) {
                field.classList.add('error');
                isValid = false;
                
                // Add field-specific error
                const fieldGroup = field.closest('.form-group');
                if (fieldGroup && !fieldGroup.querySelector('.form-message.error')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'form-message error';
                    errorMsg.textContent = 'Please enter a valid email address';
                    fieldGroup.appendChild(errorMsg);
                }
            }
        });
        
        // If form is invalid, show general error message
        if (!isValid) {
            const formError = document.createElement('div');
            formError.className = 'form-message error';
            formError.textContent = 'Please fill in all required fields correctly';
            
            // Insert at top of form
            jobPostForm.insertBefore(formError, jobPostForm.firstChild);
            
            // Scroll to first error
            const firstError = jobPostForm.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
        
        return isValid;
    }
    
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    // Save as Draft functionality
    if (saveAsDraftBtn) {
        saveAsDraftBtn.addEventListener('click', function() {
            // In a real application, you would save the form data to localStorage or send to server
            
            // For demonstration, we'll show a simple alert
            const draftSaved = document.createElement('div');
            draftSaved.className = 'form-message success';
            draftSaved.textContent = 'Your job post has been saved as a draft';
            
            // Insert the message
            const formButtons = document.querySelector('.form-buttons');
            formButtons.insertAdjacentElement('beforebegin', draftSaved);
            
            // Remove the message after 3 seconds
            setTimeout(() => {
                draftSaved.remove();
            }, 3000);
        });
    }
    
    // Handle closing the success modal
    if (modalOkBtn) {
        modalOkBtn.addEventListener('click', function() {
            successModal.classList.remove('active');
            // Redirect to home page
            window.location.href = 'index.html';
        });
    }
    
    // Form field focus/blur effects
    const formFields = document.querySelectorAll('input, textarea, select');
    
    formFields.forEach(field => {
        field.addEventListener('focus', function() {
            this.closest('.form-group').classList.add('focused');
        });
        
        field.addEventListener('blur', function() {
            this.closest('.form-group').classList.remove('focused');
        });
        
        // Remove error class when field is edited
        field.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.closest('.form-group').querySelector('.form-message.error');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });
    
    // Character counter for text areas
    const textAreas = document.querySelectorAll('textarea');
    
    textAreas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            
            if (maxLength) {
                const counter = this.closest('.form-group').querySelector('.char-counter');
                
                if (!counter) {
                    const charCounter = document.createElement('div');
                    charCounter.className = 'char-counter';
                    charCounter.textContent = `${currentLength}/${maxLength}`;
                    this.closest('.form-group').appendChild(charCounter);
                } else {
                    counter.textContent = `${currentLength}/${maxLength}`;
                }
                
                // Warning when approaching limit
                if (currentLength > maxLength * 0.9) {
                    counter.classList.add('warning');
                } else {
                    counter.classList.remove('warning');
                }
            }
        });
    });
    
    // Preview job listing functionality
    const jobTitle = document.getElementById('jobTitle');
    const jobDescription = document.getElementById('jobDescription');
    const companyName = document.getElementById('companyName');
    const previewBtn = document.createElement('button');
    
    // Add preview button if all required elements exist
    if (jobTitle && jobDescription && companyName) {
        previewBtn.type = 'button';
        previewBtn.className = 'btn btn-outline preview-btn';
        previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview Job Listing';
        
        // Insert before the form buttons
        const formButtons = document.querySelector('.form-buttons');
        if (formButtons) {
            formButtons.insertAdjacentElement('beforebegin', previewBtn);
        }
        
        // Preview button functionality
        previewBtn.addEventListener('click', function() {
            // Check if minimum required fields are filled
            if (!jobTitle.value.trim() || !jobDescription.value.trim() || !companyName.value.trim()) {
                alert('Please fill in at least Job Title, Job Description, and Company Name to preview.');
                return;
            }
            
            // Create preview modal if it doesn't exist
            let previewModal = document.getElementById('previewModal');
            
            if (!previewModal) {
                previewModal = document.createElement('div');
                previewModal.id = 'previewModal';
                previewModal.className = 'modal';
                
                previewModal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Job Listing Preview</h3>
                            <span class="close-modal">&times;</span>
                        </div>
                        <div class="modal-body preview-job">
                            <div class="preview-job-header">
                                <h2 id="preview-title"></h2>
                                <div class="preview-company" id="preview-company"></div>
                                <div class="preview-details">
                                    <div class="preview-detail" id="preview-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span></span>
                                    </div>
                                    <div class="preview-detail" id="preview-type">
                                        <i class="fas fa-briefcase"></i>
                                        <span></span>
                                    </div>
                                    <div class="preview-detail" id="preview-salary">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-section">
                                <h3>Job Description</h3>
                                <div id="preview-description"></div>
                            </div>
                            <div class="preview-section">
                                <h3>Requirements & Qualifications</h3>
                                <div id="preview-requirements"></div>
                            </div>
                            <div class="preview-section" id="preview-benefits-section">
                                <h3>Benefits & Perks</h3>
                                <div id="preview-benefits"></div>
                            </div>
                            <div class="preview-section" id="preview-company-section">
                                <h3>About the Company</h3>
                                <div id="preview-company-description"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="closePreview">Close Preview</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(previewModal);
                
                // Add event listener to close preview
                document.getElementById('closePreview').addEventListener('click', function() {
                    previewModal.classList.remove('active');
                });
                
                document.querySelector('#previewModal .close-modal').addEventListener('click', function() {
                    previewModal.classList.remove('active');
                });
            }
            
            // Fill preview data
            document.getElementById('preview-title').textContent = jobTitle.value;
            document.getElementById('preview-company').textContent = companyName.value;
            
            // Location
            const jobLocation = document.getElementById('jobLocation');
            if (jobLocation && jobLocation.value.trim()) {
                document.getElementById('preview-location').style.display = 'flex';
                document.getElementById('preview-location').querySelector('span').textContent = jobLocation.value;
            } else {
                document.getElementById('preview-location').style.display = 'none';
            }
            
            // Job Type
            const jobType = document.getElementById('jobType');
            if (jobType && jobType.selectedIndex > 0) {
                document.getElementById('preview-type').style.display = 'flex';
                document.getElementById('preview-type').querySelector('span').textContent = jobType.options[jobType.selectedIndex].text;
            } else {
                document.getElementById('preview-type').style.display = 'none';
            }
            
            // Salary
            const minSalary = document.getElementById('minSalary');
            const maxSalary = document.getElementById('maxSalary');
            const salaryDisplay = document.querySelector('input[name="salaryDisplay"]:checked');
            
            if (salaryDisplay && salaryDisplay.value === 'display' && (minSalary.value || maxSalary.value)) {
                document.getElementById('preview-salary').style.display = 'flex';
                let salaryText = '';
                
                if (minSalary.value && maxSalary.value) {
                    salaryText = `$${Number(minSalary.value).toLocaleString()} - $${Number(maxSalary.value).toLocaleString()}`;
                } else if (minSalary.value) {
                    salaryText = `From $${Number(minSalary.value).toLocaleString()}`;
                } else if (maxSalary.value) {
                    salaryText = `Up to $${Number(maxSalary.value).toLocaleString()}`;
                }
                
                document.getElementById('preview-salary').querySelector('span').textContent = salaryText;
            } else {
                document.getElementById('preview-salary').style.display = 'none';
            }
            
            // Job Description
            document.getElementById('preview-description').innerHTML = jobDescription.value.replace(/\n/g, '<br>');
            
            // Requirements
            const jobRequirements = document.getElementById('jobRequirements');
            if (jobRequirements && jobRequirements.value.trim()) {
                document.getElementById('preview-requirements').innerHTML = jobRequirements.value.replace(/\n/g, '<br>');
            } else {
                document.getElementById('preview-requirements').innerHTML = '<p>No specific requirements listed.</p>';
            }
            
            // Benefits
            const jobBenefits = document.getElementById('jobBenefits');
            if (jobBenefits && jobBenefits.value.trim()) {
                document.getElementById('preview-benefits').innerHTML = jobBenefits.value.replace(/\n/g, '<br>');
                document.getElementById('preview-benefits-section').style.display = 'block';
            } else {
                document.getElementById('preview-benefits-section').style.display = 'none';
            }
            
            // Company Description
            const companyDescription = document.getElementById('companyDescription');
            if (companyDescription && companyDescription.value.trim()) {
                document.getElementById('preview-company-description').innerHTML = companyDescription.value.replace(/\n/g, '<br>');
                document.getElementById('preview-company-section').style.display = 'block';
            } else {
                document.getElementById('preview-company-section').style.display = 'none';
            }
            
            // Show preview modal
            previewModal.classList.add('active');
        });
    }
});