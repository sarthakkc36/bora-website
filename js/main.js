document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Fix for iOS touch events on mobile menu
    document.addEventListener('touchend', function() {
        // This empty handler helps iOS recognize touch events better
    }, false);
    
    // Initialize AOS animations
    AOS.init({
        duration: 800,
        easing: 'ease',
        once: true,
        offset: 100
    });

    // Preloader
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }, 1500);
    }

    // Header scroll effect
    const header = document.querySelector('header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Mobile Menu Toggle - Improved version
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            navLinks.classList.toggle('active');
            
            // Update icon based on menu state
            if (navLinks.classList.contains('active')) {
                this.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                this.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (navLinks && navLinks.classList.contains('active')) {
            if (!event.target.closest('.nav-links') && !event.target.closest('.mobile-menu-toggle')) {
                navLinks.classList.remove('active');
                if (mobileMenuToggle) {
                    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }
        }
    });

    // Testimonial Slider
    const testimonialSlides = document.querySelectorAll('.testimonial-slide');
    const testimonialDots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.testimonial-btn.prev');
    const nextBtn = document.querySelector('.testimonial-btn.next');
    let currentSlide = 0;

    function showSlide(n) {
        testimonialSlides.forEach(slide => slide.classList.remove('active'));
        testimonialDots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (n + testimonialSlides.length) % testimonialSlides.length;
        
        testimonialSlides[currentSlide].classList.add('active');
        if (testimonialDots.length > 0) {
            testimonialDots[currentSlide].classList.add('active');
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            showSlide(currentSlide - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            showSlide(currentSlide + 1);
        });
    }

    testimonialDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
        });
    });

    // Auto rotate testimonials
    if (testimonialSlides.length > 0) {
        setInterval(() => {
            showSlide(currentSlide + 1);
        }, 5000);
    }

    // Counter Animation for Stats
    const statNumbers = document.querySelectorAll('.stat-number');
    
    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const step = Math.ceil(target / (duration / 20)); // Update every 20ms
        let current = 0;
        
        const counter = setInterval(() => {
            current += step;
            if (current > target) {
                el.textContent = target;
                clearInterval(counter);
            } else {
                el.textContent = current;
            }
        }, 20);
    }
    
    // Intersection Observer for Stats Animation
    if (statNumbers.length > 0) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        statNumbers.forEach(stat => {
            statsObserver.observe(stat);
        });
    }

    // Back to Top Button
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('active');
            } else {
                backToTopBtn.classList.remove('active');
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Form Submission with validation
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            let isValid = true;
            const formInputs = contactForm.querySelectorAll('input, textarea');
            
            formInputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                } else if (input.type === 'email' && input.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        isValid = false;
                        input.classList.add('error');
                    } else {
                        input.classList.remove('error');
                    }
                } else {
                    input.classList.remove('error');
                }
            });
            
            if (isValid) {
                // Here you would normally send the form data to the server
                // For now, we'll just show a success message
                const formMessage = document.createElement('div');
                formMessage.className = 'form-message success';
                formMessage.innerHTML = '<p>Thank you! Your message has been sent successfully.</p>';
                
                contactForm.reset();
                contactForm.appendChild(formMessage);
                
                setTimeout(() => {
                    formMessage.remove();
                }, 5000);
            } else {
                const formMessage = document.createElement('div');
                formMessage.className = 'form-message error';
                formMessage.innerHTML = '<p>Please fill in all required fields correctly.</p>';
                
                contactForm.appendChild(formMessage);
                
                setTimeout(() => {
                    formMessage.remove();
                }, 5000);
            }
        });
        
        // Remove error class on input focus
        contactForm.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('focus', function() {
                this.classList.remove('error');
            });
        });
    }

    // Newsletter Form
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailRegex.test(emailInput.value.trim())) {
                const formMessage = document.createElement('div');
                formMessage.className = 'form-message success';
                formMessage.innerHTML = '<p>Thank you for subscribing to our job alerts!</p>';
                
                this.reset();
                this.appendChild(formMessage);
                
                setTimeout(() => {
                    formMessage.remove();
                }, 5000);
            } else {
                const formMessage = document.createElement('div');
                formMessage.className = 'form-message error';
                formMessage.innerHTML = '<p>Please enter a valid email address.</p>';
                
                this.appendChild(formMessage);
                
                setTimeout(() => {
                    formMessage.remove();
                }, 5000);
            }
        });
    }

    // Smooth Scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Close mobile menu if open
                    if (navLinks && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        if (mobileMenuToggle) {
                            mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                        }
                    }
                    
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Featured Jobs API Integration (Mock data for now)
    const featuredJobsContainer = document.getElementById('featured-jobs-container');
    
    // This would be replaced with actual API call in production
    function loadFeaturedJobs() {
        // Sample jobs data for demonstration
        const jobsData = [
            {
                id: 1,
                title: 'Senior Software Developer',
                company: 'Tech Solutions Inc.',
                location: 'New York, NY',
                type: 'Full Time',
                salary: '$90,000 - $120,000',
                logo: 'images/placeholder-logo.png'
            },
            {
                id: 2,
                title: 'Marketing Specialist',
                company: 'Global Marketing Group',
                location: 'Remote',
                type: 'Part Time',
                salary: '$45,000 - $55,000',
                logo: 'images/placeholder-logo.png'
            },
            {
                id: 3,
                title: 'Project Manager',
                company: 'Construct Builders LLC',
                location: 'Chicago, IL',
                type: 'Contract',
                salary: '$75,000 - $95,000',
                logo: 'images/placeholder-logo.png'
            },
            {
                id: 4,
                title: 'Administrative Assistant',
                company: 'Executive Office Services',
                location: 'Jackson Heights, NY',
                type: 'Full Time',
                salary: '$45,000 - $55,000',
                logo: 'images/placeholder-logo.png'
            }
        ];
        
        // This code is for when we implement the actual API fetch
        /*
        fetch('api/jobs/featured')
            .then(response => response.json())
            .then(data => {
                renderJobs(data);
            })
            .catch(error => {
                console.error('Error fetching featured jobs:', error);
            });
        */
        
        // For now, use the sample data
        // renderJobs(jobsData);
    }
    
    function renderJobs(jobs) {
        if (featuredJobsContainer) {
            featuredJobsContainer.innerHTML = '';
            
            jobs.forEach((job, index) => {
                const jobCard = document.createElement('div');
                jobCard.className = 'job-card';
                jobCard.setAttribute('data-aos', 'fade-up');
                jobCard.setAttribute('data-aos-delay', index * 100);
                
                const typeClass = job.type.toLowerCase().replace(' ', '-');
                
                jobCard.innerHTML = `
                    <div class="job-card-header">
                        <div class="company-logo">
                            <img src="${job.logo}" alt="${job.company} Logo">
                        </div>
                        <div class="job-type ${typeClass}">${job.type}</div>
                    </div>
                    <h3>${job.title}</h3>
                    <div class="company-name">
                        <i class="fas fa-building"></i>
                        <span>${job.company}</span>
                    </div>
                    <div class="job-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${job.location}</span>
                    </div>
                    <div class="job-salary">
                        <i class="fas fa-dollar-sign"></i>
                        <span>${job.salary}</span>
                    </div>
                    <a href="job-details.html?id=${job.id}" class="btn btn-outline btn-sm">View Details</a>
                `;
                
                featuredJobsContainer.appendChild(jobCard);
            });
        }
    }
    
    // Load featured jobs on homepage
    // loadFeaturedJobs();

    // Add floating animation to CTA buttons
    const heroBtns = document.querySelector('.hero-buttons');
    if (heroBtns) {
        setInterval(() => {
            heroBtns.style.transform = 'translateY(-5px)';
            setTimeout(() => {
                heroBtns.style.transform = 'translateY(0)';
            }, 500);
        }, 3000);
    }

    // Typing effect for hero headline on homepage
    const heroHeadline = document.querySelector('.hero-content h1');
    if (heroHeadline) {
        const text = heroHeadline.textContent;
        heroHeadline.textContent = '';
        
        let i = 0;
        const typeInterval = setInterval(() => {
            if (i < text.length) {
                heroHeadline.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(typeInterval);
            }
        }, 50);
    }

    // Live chat widget (mock functionality)
    setTimeout(() => {
        const chatWidget = document.createElement('div');
        chatWidget.innerHTML = `
            <div id="chat-widget" style="position: fixed; bottom: 30px; right: 30px; z-index: 999;">
                <div id="chat-bubble" style="width: 60px; height: 60px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--shadow-md);">
                    <i class="fas fa-comments" style="color: white; font-size: 24px;"></i>
                </div>
                <div id="chat-box" style="position: absolute; bottom: 70px; right: 0; width: 300px; height: 400px; background-color: white; border-radius: var(--border-radius-md); box-shadow: var(--shadow-lg); display: none; overflow: hidden;">
                    <div style="background-color: var(--primary-color); color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: bold;">Chat with Us</div>
                            <div style="font-size: 0.8rem;">We're online</div>
                        </div>
                        <i id="close-chat" class="fas fa-times" style="cursor: pointer;"></i>
                    </div>
                    <div id="chat-messages" style="height: 290px; padding: 15px; overflow-y: auto;">
                        <div style="background-color: var(--primary-light); padding: 10px; border-radius: var(--border-radius-md); margin-bottom: 10px; max-width: 80%;">
                            <div style="font-weight: bold; margin-bottom: 5px;">Support Team</div>
                            <div>Hello! How can we help you today with your job search?</div>
                        </div>
                    </div>
                    <div style="padding: 10px; border-top: 1px solid var(--border-color); display: flex; gap: 10px;">
                        <input type="text" id="chat-input" placeholder="Type your message..." style="flex: 1; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-md);">
                        <button id="send-chat" style="background-color: var(--primary-color); color: white; border: none; border-radius: var(--border-radius-md); padding: 10px; cursor: pointer;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(chatWidget);
        
        const chatBubble = document.getElementById('chat-bubble');
        const chatBox = document.getElementById('chat-box');
        const closeChat = document.getElementById('close-chat');
        const chatInput = document.getElementById('chat-input');
        const sendChat = document.getElementById('send-chat');
        const chatMessages = document.getElementById('chat-messages');
        
        chatBubble.addEventListener('click', () => {
            chatBox.style.display = 'block';
            chatBubble.style.display = 'none';
        });
        
        closeChat.addEventListener('click', () => {
            chatBox.style.display = 'none';
            chatBubble.style.display = 'flex';
        });
        
        function sendMessage() {
            const message = chatInput.value.trim();
            if (message) {
                // Add user message
                const userMessage = document.createElement('div');
                userMessage.style.cssText = 'background-color: var(--primary-color); color: white; padding: 10px; border-radius: var(--border-radius-md); margin-bottom: 10px; max-width: 80%; margin-left: auto; text-align: right;';
                userMessage.innerHTML = `<div>${message}</div>`;
                chatMessages.appendChild(userMessage);
                
                // Clear input
                chatInput.value = '';
                
                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Mock response after 1s
                setTimeout(() => {
                    const botMessage = document.createElement('div');
                    botMessage.style.cssText = 'background-color: var(--primary-light); padding: 10px; border-radius: var(--border-radius-md); margin-bottom: 10px; max-width: 80%;';
                    botMessage.innerHTML = `
                        <div style="font-weight: bold; margin-bottom: 5px;">Support Team</div>
                        <div>Thank you for your message! Our team will get back to you shortly. Feel free to browse our job listings in the meantime.</div>
                    `;
                    chatMessages.appendChild(botMessage);
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1000);
            }
        }
        
        sendChat.addEventListener('click', sendMessage);
        
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }, 3000);

    // Job alerts notification
    setTimeout(() => {
        const notification = document.createElement('div');
        notification.style.cssText = 'position: fixed; bottom: 20px; left: 20px; background-color: white; box-shadow: var(--shadow-md); border-radius: var(--border-radius-md); padding: 15px 20px; display: flex; align-items: center; gap: 15px; width: 300px; z-index: 999; transform: translateY(150%); transition: transform 0.5s ease;';
        
        notification.innerHTML = `
            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-bell" style="color: white;"></i>
            </div>
            <div>
                <div style="font-weight: bold; margin-bottom: 5px;">New job alert!</div>
                <div style="font-size: 0.9rem;">15 new jobs matching your profile</div>
            </div>
            <button id="close-notification" style="background: none; border: none; cursor: pointer; margin-left: auto;">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
        }, 500);
        
        document.getElementById('close-notification').addEventListener('click', () => {
            notification.style.transform = 'translateY(150%)';
            setTimeout(() => {
                notification.remove();
            }, 500);
        });
        
        setTimeout(() => {
            notification.style.transform = 'translateY(150%)';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 8000);
    }, 5000);
});