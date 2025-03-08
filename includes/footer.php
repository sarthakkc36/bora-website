<?php
// Get site settings if not already loaded in header
if (!isset($site_settings)) {
    try {
        $site_settings_stmt = $pdo->prepare("SELECT * FROM site_settings");
        $site_settings_stmt->execute();
        $site_settings_rows = $site_settings_stmt->fetchAll();
        
        // Convert to associative array
        $site_settings = [];
        foreach ($site_settings_rows as $row) {
            $site_settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching site settings: " . $e->getMessage());
        $site_settings = [];
    }
}

// Set default values if settings are not found
$site_title = $site_settings['site_title'] ?? 'B&H Employment & Consultancy Inc';
$contact_email = $site_settings['contact_email'] ?? 'bh.jobagency@gmail.com';
$contact_phone = $site_settings['contact_phone'] ?? '(1)347680-2869';
$contact_address = $site_settings['contact_address'] ?? '37-51 75th St.1A, Jackson Heights, NY 11372';
$social_facebook = $site_settings['social_facebook'] ?? '#';
$social_twitter = $site_settings['social_twitter'] ?? '#';
$social_linkedin = $site_settings['social_linkedin'] ?? '#';
$social_instagram = $site_settings['social_instagram'] ?? '#';
?>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>About Us</h3>
                <p style="color: #bbb; line-height: 1.6; margin-bottom: 20px;"><?php echo htmlspecialchars($site_description ?? $site_settings['site_description'] ?? 'B&H Employment & Consultancy Inc is a leading employment agency dedicated to connecting qualified candidates with top employers across various industries.'); ?></p>
                <div class="social-links">
                    <a href="<?php echo htmlspecialchars($social_facebook); ?>" class="social-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo htmlspecialchars($social_twitter); ?>" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="<?php echo htmlspecialchars($social_linkedin); ?>" class="social-link" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                    <a href="<?php echo htmlspecialchars($social_instagram); ?>" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-menu">
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php' : '../index.php'; ?>">Home</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'jobs.php' : '../jobs.php'; ?>">Jobs</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#services' : '../index.php#services'; ?>">Services</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#about' : '../index.php#about'; ?>">About Us</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#contact' : '../index.php#contact'; ?>">Contact</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'privacy.php' : '../privacy.php'; ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'terms.php' : '../terms.php'; ?>">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="footer-column footer-contact">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($contact_address); ?></p>
                <p><i class="fas fa-phone"></i> (Office) <?php echo htmlspecialchars($contact_phone); ?></p>
                <p><i class="fas fa-mobile-alt"></i> (Mobile) (929)823-7040</p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact_email); ?></p>
                <p><i class="fas fa-globe"></i> www.bh.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Mobile menu toggle
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
        document.querySelector('.nav-menu').classList.toggle('active');
    });
    
    // User dropdown toggle
    const userToggle = document.querySelector('.user-toggle');
    if (userToggle) {
        userToggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.nextElementSibling.classList.toggle('active');
        });
    }
    
    // Close the dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userToggle && !userToggle.contains(e.target)) {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                document.querySelector('.nav-menu').classList.remove('active');
            }
        });
    });
    
    // Scroll animation for elements
    function handleScrollAnimation() {
        const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in, .contact-form');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('active');
            }
        });
    }
    
    // Run on load and scroll
    window.addEventListener('load', function() {
        handleScrollAnimation();
        
        // Add animation classes to elements
        document.querySelectorAll('.service-card').forEach((card, index) => {
            card.classList.add('fade-in');
            card.style.transitionDelay = `${0.1 * index}s`;
        });
        
        document.querySelectorAll('.job-card').forEach((card, index) => {
            card.classList.add('fade-in');
            card.style.transitionDelay = `${0.1 * index}s`;
        });
        
        document.querySelectorAll('.info-item').forEach((item, index) => {
            item.classList.add(index % 2 === 0 ? 'slide-in-left' : 'slide-in-right');
            item.style.transitionDelay = `${0.1 * index}s`;
        });
        
        document.querySelectorAll('.section-title').forEach(title => {
            title.classList.add('scale-in');
        });
    });
    
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.alert');
    if (flashMessages.length > 0) {
        setTimeout(() => {
            flashMessages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
</script>