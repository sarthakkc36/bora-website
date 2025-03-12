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
<?php
// Get site settings for favicon
$favicon_path = '';
if (isset($site_settings) && !empty($site_settings['favicon'])) {
    $favicon_path = '/' . ltrim($site_settings['favicon'], '/');
} else {
    $favicon_path = '/favicon.ico';
}
?>
<!-- Dynamic Favicon -->
<link rel="icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
<link rel="shortcut icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
<?php
// Get site settings for favicon
$favicon_path = '';
if (isset($site_settings) && !empty($site_settings['favicon'])) {
    $favicon_path = '/' . ltrim($site_settings['favicon'], '/');
} else {
    $favicon_path = '/favicon.ico';
}
?>
<!-- Dynamic Favicon -->
<link rel="icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
<link rel="shortcut icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
<style>/* Image-Only Hero Section */
.hero-image-only {
    position: relative;
    height: 100vh;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0066cc, #003366);
    z-index: 1;
}

.hero-image-only .container {
    position: relative;
    z-index: 2;
    max-width: 1400px;
    width: 100%;
    padding: 0 20px;
}

.hero-image-only .hero-content {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.image-container {
    position: relative;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    perspective: 1000px;
}

.main-image {
    width: 90%;
    max-width: 900px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    transform: translateY(-15px) rotateY(5deg);
    transition: all 0.5s ease;
    border: 8px solid white;
    display: block;
    margin: 0 auto;
}

.secondary-image {
    position: absolute;
    width: 70%;
    max-width: 700px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    transform: translateY(60px) translateX(20%) rotateY(-5deg);
    transition: all 0.5s ease;
    border: 8px solid white;
    z-index: 3;
    right: 0;
}

.image-container:hover .main-image {
    transform: translateY(-20px) rotateY(3deg);
}

.image-container:hover .secondary-image {
    transform: translateY(70px) translateX(23%) rotateY(-3deg);
}

.floating-badge {
    position: absolute;
    padding: 15px;
    border-radius: 15px;
    color: white;
    font-weight: 600;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 4;
    transition: transform 0.3s ease;
}

.jobs-badge {
    background-color: #4CAF50;
    top: 40px;
    right: 10%;
    animation: float 4s ease-in-out infinite;
}

.services-badge {
    background-color: #FF9800;
    bottom: 40px;
    left: 10%;
    animation: float 4s ease-in-out infinite 2s;
}

.badge-number {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.badge-text {
    font-size: 14px;
    text-align: center;
}

.floating-badge i {
    font-size: 24px;
    margin-bottom: 5px;
}

.cta-container {
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 20px;
    z-index: 5;
}

.hero-cta-btn {
    display: inline-block;
    padding: 16px 36px;
    border-radius: 50px;
    background-color: #0066cc;
    color: white;
    font-size: 18px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 10px 20px rgba(0, 102, 204, 0.3);
    transition: all 0.3s ease;
    border: none;
}

.hero-cta-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 102, 204, 0.4);
    background-color: #0052a3;
}

.hero-cta-btn.secondary {
    background-color: white;
    color: #0066cc;
    border: 2px solid #0066cc;
}

.hero-cta-btn.secondary:hover {
    background-color: #f5f5f5;
    color: #0052a3;
    border-color: #0052a3;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-15px);
    }
}


</style>
    <!-- Hero Section with Image -->
    <section class="hero-image-only">
    <div class="hero-background"></div>
    <div class="container">
        <div class="hero-content">
            <div class="image-container">
                <img src="images/hero.jpeg" alt="B&H Employment & Consultancy" class="main-image">
                <div class="floating-badge jobs-badge">
                    <span class="badge-number">7+</span>
                    <span class="badge-text">Jobs Available</span>
                </div>
                <div class="floating-badge services-badge">
                    <i class="fas fa-briefcase"></i>
                    <span class="badge-text">Multiple Industries</span>
                </div>
                <div class="cta-container">
                    <a href="#services" class="hero-cta-btn secondary">Our Services</a>
                    <a href="request-appointment.php" class="hero-cta-btn secondary">Book an Appointment</a>
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

<!-- Eye-Catching Appointment Booking Section -->
<section id="book-appointment" class="eye-catching-appointment">
    <div class="container">
        <div class="appointment-cta">
            <div class="cta-content">
                <div class="cta-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="cta-text">
                    <h2>Ready to take the next step?</h2>
                    <p>Our experts are available to guide your career journey</p>
                </div>
            </div>
            <a href="request-appointment.php" class="cta-btn pulse-btn">Book an Appointment</a>
        </div>
    </div>
</section>

<style>
.eye-catching-appointment {
    padding: 50px 0;
    background: linear-gradient(135deg, #0066cc, #40BFBF);
    margin: 40px 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
}

.appointment-cta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.cta-content {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 2;
}

.cta-icon {
    background-color: rgba(255, 255, 255, 0.2);
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.cta-icon i {
    font-size: 30px;
    color: white;
}

.cta-text {
    color: white;
}

.cta-text h2 {
    margin-bottom: 5px;
    font-size: 24px;
}

.cta-text p {
    margin: 0;
    opacity: 0.9;
}

.cta-btn {
    background-color: white;
    color: #0066cc;
    padding: 12px 25px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.cta-btn:hover {
    background-color: #f0f0f0;
    transform: translateY(-3px);
}

</style>
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
    <?php include 'includes/footer.php'; ?>
<script>// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {

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