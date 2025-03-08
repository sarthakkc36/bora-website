<?php
require_once 'config.php';

// Get site settings for use throughout the page
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

// Set defaults if not found
$site_title = $site_settings['site_title'] ?? 'B&H Employment & Consultancy Inc';
$site_description = $site_settings['site_description'] ?? 'Professional employment agency connecting qualified candidates with top employers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Your Path to Career Success Starts Here</h1>
            <p><?php echo htmlspecialchars($site_description); ?></p>
            <a href="jobs.php" class="cta-btn">Browse Jobs</a>
        </div>
    </section>

    <!-- Company Info Section -->
<!-- Company Info Section -->
<section class="company-info">
    <div class="container">
        <div class="info-container">
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <h3>Email Us</h3>
                    <p><?php echo htmlspecialchars($site_settings['contact_email'] ?? 'bh.jobagency@gmail.com'); ?></p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="info-content">
                    <h3>Website</h3>
                    <p>www.bh.com</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="info-content">
                    <h3>Call Us</h3>
                    <p>(Office) <?php echo htmlspecialchars($site_settings['contact_phone'] ?? '(1)347680-2869'); ?></p>
                    <p>(Mobile) (929)823-7040</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-content">
                    <h3>Our Location</h3>
                    <p><?php echo htmlspecialchars($site_settings['contact_address'] ?? '37-51 75th St.1A, Jackson Heights, NY 11372'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Services Section -->
    <?php include 'includes/services.php'; ?>
    
    <!-- About Us Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>About Us</h2>
                <p>Learn more about B&H Employment & Consultancy Inc and our mission to connect talent with opportunity</p>
            </div>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="/api/placeholder/500/350" alt="B&H Employment & Consultancy Office">
                </div>
                <div class="about-text">
                    <h3>Your Trusted Employment Partner</h3>
                    <p>Founded in 2018, B&H Employment & Consultancy Inc has quickly established itself as a leading employment agency in the New York metropolitan area. We specialize in connecting qualified candidates with top employers across various industries including healthcare, technology, finance, and hospitality.</p>
                    
                    <p>Our team of experienced recruitment professionals understands the challenges of today's job market. We are committed to providing personalized services that address the unique needs of both job seekers and employers.</p>
                    
                    <h4>Our Mission</h4>
                    <p>To facilitate meaningful employment connections that benefit both candidates and companies, creating lasting professional relationships and fostering career growth.</p>
                    
                    <h4>Why Choose Us?</h4>
                    <ul class="about-list">
                        <li><i class="fas fa-check-circle"></i> Personalized approach to recruitment</li>
                        <li><i class="fas fa-check-circle"></i> Extensive network of industry contacts</li>
                        <li><i class="fas fa-check-circle"></i> Thorough candidate screening process</li>
                        <li><i class="fas fa-check-circle"></i> Dedicated support throughout the hiring process</li>
                        <li><i class="fas fa-check-circle"></i> Post-placement follow-up and assistance</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Jobs Section -->
    <section class="featured-jobs">
        <div class="container">
            <div class="section-title">
                <h2>Featured Job Opportunities</h2>
                <p>Explore our latest job openings across various industries and locations.</p>
            </div>
            
            <?php
            // Fetch featured jobs
            try {
                $stmt = $pdo->prepare("SELECT * FROM jobs WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");
                $stmt->execute();
                $jobs = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Error fetching featured jobs: " . $e->getMessage());
                $jobs = [];
            }
            
            if (!empty($jobs)):
            ?>
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    <div class="job-details">
                        <span class="job-detail"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                        <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                            <span class="job-detail"><i class="fas fa-dollar-sign"></i> 
                            <?php 
                                if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                    echo '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                                } elseif (!empty($job['salary_min'])) {
                                    echo 'From $' . number_format($job['salary_min']);
                                } elseif (!empty($job['salary_max'])) {
                                    echo 'Up to $' . number_format($job['salary_max']);
                                }
                            ?>
                            </span>
                        <?php endif; ?>
                        <span class="job-detail"><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                    </div>
                    <p class="job-description"><?php echo substr(strip_tags($job['description']), 0, 150) . '...'; ?></p>
                    <a href="job-details.php?id=<?php echo $job['id']; ?>" class="apply-btn">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>No job postings available at the moment. Please check back soon.</p>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="jobs.php" class="cta-btn">View All Jobs</a>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
<!-- Contact Form Section -->
<section id="contact" class="contact">
    <div class="container">
        <div class="section-title">
            <h2>Get In Touch</h2>
            <p>Have questions or need assistance? Fill out the form below and our team will get back to you soon.</p>
        </div>
        <?php
        // Process contact form submission
        $contact_success = '';
        $contact_error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $subject = sanitizeInput($_POST['subject']);
            $message = sanitizeInput($_POST['message']);
            
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $contact_error = "Please fill in all required fields.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $contact_error = "Please enter a valid email address.";
            } else {
                // Store message in database
                try {
                    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) 
                                           VALUES (:name, :email, :phone, :subject, :message)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':subject', $subject);
                    $stmt->bindParam(':message', $message);
                    $stmt->execute();
                    
                    $contact_success = "Your message has been sent successfully! We'll get back to you shortly.";
                    
                    // Reset form fields
                    $name = $email = $phone = $subject = $message = '';
                } catch (PDOException $e) {
                    error_log("Error saving contact message: " . $e->getMessage());
                    $contact_error = "An error occurred while sending your message. Please try again later.";
                }
            }
        }
        ?>
        
        <?php if (!empty($contact_success)): ?>
            <div class="alert alert-success"><?php echo $contact_success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($contact_error)): ?>
            <div class="alert alert-danger"><?php echo $contact_error; ?></div>
        <?php endif; ?>
        
        <form class="contact-form" method="POST" action="#contact">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="message">Your Message</label>
                <textarea class="form-control" id="message" name="message" required><?php echo isset($message) ? $message : ''; ?></textarea>
            </div>
            <button type="submit" name="contact_submit" class="submit-btn">Send Message</button>
        </form>
    </div>
</section>

    <?php include 'includes/footer.php'; ?>
<script>// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    }
    
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
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            e.preventDefault();
            
            const target = document.querySelector(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navMenu = document.querySelector('.nav-menu');
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                }
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
    
    // Run animation on load
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
    
    // Run animation on scroll
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Job save functionality
    const saveBtns = document.querySelectorAll('.job-save[data-job-id]');
    saveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.getAttribute('href')) return; // Let the login link work normally
            
            const jobId = this.getAttribute('data-job-id');
            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            const isSaved = icon.classList.contains('fas');
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/save-job.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            if (isSaved) {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                text.textContent = 'Save Job';
                            } else {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                text.textContent = 'Saved';
                                
                                // Add heart animation
                                icon.style.transform = 'scale(1.3)';
                                setTimeout(() => {
                                    icon.style.transform = 'scale(1)';
                                }, 300);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            
            xhr.send('job_id=' + jobId + '&action=' + (isSaved ? 'unsave' : 'save'));
        });
    });
    
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
});</script>
    <script src="js/script.js"></script>
</body>
</html>