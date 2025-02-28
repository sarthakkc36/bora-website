document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Tabs Functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Service Card Link Handling
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Only trigger if not clicking on the read more link
            if (!e.target.closest('.read-more')) {
                const readMoreLink = this.querySelector('.read-more');
                if (readMoreLink) {
                    window.location.href = readMoreLink.getAttribute('href');
                }
            }
        });
    });
    
    // Scroll to service details when clicking "Learn More"
    const readMoreLinks = document.querySelectorAll('.read-more');
    
    readMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Scroll to the target element
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                
                // Add highlight animation
                targetElement.classList.add('highlight');
                
                // Remove highlight after 2 seconds
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });
    
    // Add CSS for highlight animation
    const highlightStyle = document.createElement('style');
    highlightStyle.textContent = `
        @keyframes highlight {
            0% { background-color: var(--primary-light); }
            100% { background-color: transparent; }
        }
        
        .service-detail.highlight {
            animation: highlight 2s ease;
        }
    `;
    document.head.appendChild(highlightStyle);
    
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
    
    // Pricing Plan Interaction
    const pricingPlans = document.querySelectorAll('.pricing-plan');
    
    pricingPlans.forEach(plan => {
        plan.addEventListener('mouseover', function() {
            pricingPlans.forEach(p => {
                if (p !== this && p.classList.contains('recommended')) {
                    p.style.transform = 'scale(1)';
                }
            });
        });
        
        plan.addEventListener('mouseout', function() {
            pricingPlans.forEach(p => {
                if (p.classList.contains('recommended')) {
                    p.style.transform = 'scale(1.05)';
                }
            });
        });
    });
    
    // Check URL hash for direct navigation to service sections
    function checkHash() {
        const hash = window.location.hash;
        if (hash) {
            const targetElement = document.querySelector(hash);
            if (targetElement) {
                // Delay to ensure DOM is fully loaded
                setTimeout(() => {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Add highlight animation
                    targetElement.classList.add('highlight');
                    
                    // Remove highlight after 2 seconds
                    setTimeout(() => {
                        targetElement.classList.remove('highlight');
                    }, 2000);
                }, 500);
            }
        }
    }
    
    // Run hash check on page load
    checkHash();
    
    // Also check hash when it changes
    window.addEventListener('hashchange', checkHash);
    
    // Button hover effects for pricing plans
    const pricingButtons = document.querySelectorAll('.pricing-plan .btn');
    
    pricingButtons.forEach(button => {
        button.addEventListener('mouseover', function() {
            // Get the parent pricing plan
            const plan = this.closest('.pricing-plan');
            
            // Add a subtle glow effect to the plan
            plan.style.boxShadow = '0 10px 30px rgba(0, 86, 179, 0.2)';
        });
        
        button.addEventListener('mouseout', function() {
            // Get the parent pricing plan
            const plan = this.closest('.pricing-plan');
            
            // Remove the glow effect
            plan.style.boxShadow = '';
        });
    });
    
    // Smooth scroll for all links pointing to service details
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            // Check if the link points to a service detail section
            const href = this.getAttribute('href');
            if (href.startsWith('#') && document.querySelector(href) && 
                document.querySelector(href).classList.contains('service-detail')) {
                e.preventDefault();
                
                const targetElement = document.querySelector(href);
                
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                
                // Add highlight animation
                targetElement.classList.add('highlight');
                
                // Remove highlight after 2 seconds
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });
});