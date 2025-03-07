
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B&H Employment & Consultancy Inc</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            overflow-x: hidden;
        }
        
        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .fade-in.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .slide-in-left {
            opacity: 0;
            transform: translateX(-40px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .slide-in-right {
            opacity: 0;
            transform: translateX(40px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .slide-in-left.active, .slide-in-right.active {
            opacity: 1;
            transform: translateX(0);
        }
        
        .scale-in {
            opacity: 0;
            transform: scale(0.8);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }
        
        .scale-in.active {
            opacity: 1;
            transform: scale(1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            animation: slideDown 0.5s ease-out forwards;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 60px;
            transition: transform 0.3s ease;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li {
            margin-left: 30px;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.3s, transform 0.3s;
            position: relative;
            padding: 5px 0;
        }
        
        .nav-menu a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #0066cc;
            transition: width 0.3s ease;
        }
        
        .nav-menu a:hover, .nav-menu a.active {
            color: #0066cc;
        }
        
        .nav-menu a:hover:after, .nav-menu a.active:after {
            width: 100%;
        }
        
        .mobile-menu-btn {
            display: none;
            cursor: pointer;
            font-size: 24px;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0066cc, #0052a3);
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('/api/placeholder/1200/600') center/cover no-repeat;
            opacity: 0.1;
            animation: pulse 15s infinite alternate;
        }
        
        @keyframes pulse {
            0% {
                opacity: 0.05;
                transform: scale(1);
            }
            100% {
                opacity: 0.15;
                transform: scale(1.05);
            }
        }
        
        .hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .hero p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
            opacity: 0;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .cta-btn {
            display: inline-block;
            background-color: white;
            color: #0066cc;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease-out 0.4s forwards;
            opacity: 0;
        }
        
        .cta-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s ease;
        }
        
        .cta-btn:hover {
            background-color: #f8f8f8;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.2);
        }
        
        .cta-btn:hover:before {
            left: 100%;
        }
        
        /* Services Section */
        .services {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .service-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #0066cc, #4da6ff);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease-out;
            z-index: -1;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .service-card:hover:before {
            transform: scaleX(1);
        }
        
        .service-icon {
            font-size: 40px;
            color: #0066cc;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .service-card:hover .service-icon {
            transform: rotate(10deg) scale(1.1);
            color: #0052a3;
        }
        
        .service-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .service-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Contact Form Section */
        .contact {
            padding: 80px 0;
            background-color: #f9f9f9;
        }
        
        .contact-form {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.6s ease-out;
        }
        
        .contact-form.active {
            transform: translateY(0);
            opacity: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .form-control:focus {
            border-color: #0066cc;
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
            transform: translateY(-2px);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: #0066cc;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .submit-btn:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
            z-index: -1;
        }
        
        .submit-btn:hover {
            background-color: #0052a3;
            box-shadow: 0 6px 15px rgba(0, 102, 204, 0.3);
            transform: translateY(-3px);
        }
        
        .submit-btn:hover:after {
            width: 300px;
            height: 300px;
        }
        
        /* Featured Jobs Section */
        .featured-jobs {
            padding: 80px 0;
        }
        
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .job-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 0px solid #0066cc;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid #0066cc;
        }
        
        .job-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .company-name {
            font-size: 16px;
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .job-details {
            display: flex;
            margin-bottom: 15px;
        }
        
        .job-detail {
            margin-right: 20px;
            display: flex;
            align-items: center;
            color: #666;
        }
        
        .job-detail i {
            margin-right: 5px;
            color: #0066cc;
        }
        
        .job-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .apply-btn {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .apply-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            z-index: -1;
        }
        
        .apply-btn:hover {
            background-color: #0052a3;
            transform: translateY(-2px);
        }
        
        .apply-btn:hover:before {
            width: 100%;
        }
        
        /* Company Info Section */
        .company-info {
            padding: 50px 0;
            background-color: #f0f7ff;
        }
        
        .info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        .info-item {
            flex: 1;
            min-width: 250px;
            margin: 15px;
            display: flex;
            align-items: center;
        }
        
        .info-icon {
            background-color: #0066cc;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .info-content h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .info-content p {
            color: #666;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .footer-column {
            flex: 1;
            min-width: 200px;
            margin-bottom: 30px;
        }
        
        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 50px;
            background-color: #0066cc;
        }
        
        .footer-menu {
            list-style: none;
        }
        
        .footer-menu li {
            margin-bottom: 10px;
        }
        
        .footer-menu a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-menu a:hover {
            color: white;
        }
        
        .footer-contact p {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .footer-contact i {
            margin-right: 10px;
            color: #0066cc;
        }
        
        .social-links {
            display: flex;
            margin-top: 20px;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            background-color: #444;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        
        .social-link:hover {
            background-color: #0066cc;
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 20px;
            text-align: center;
            color: #bbb;
            font-size: 14px;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                flex-direction: column;
                background-color: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
                padding: 20px 0;
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            .nav-menu li {
                margin: 15px 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero h1 {
                font-size: 36px;
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 30px;
            }
            
            .section-title h2 {
                font-size: 30px;
            }
            
            .contact-form {
                padding: 25px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Your Path to Career Success Starts Here</h1>
            <p>B&H Employment & Consultancy Inc connects qualified candidates with top employers. Let us help you find your perfect job match or the ideal candidate for your business.</p>
            <a href="jobs.html" class="cta-btn">Browse Jobs</a>
        </div>
    </section>

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
                        <p>bh.jobagency@gmail.com</p>
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
                        <p>(Office) (1)347680-2869</p>
                        <p>(Mobile) (929)823-7040</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Our Location</h3>
                        <p>37-51 75th St.1A, Jackson Heights, NY 11372</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>We provide comprehensive employment and consultancy services to both job seekers and employers.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Job Placement</h3>
                    <p>We match qualified candidates with suitable job opportunities across various industries, ensuring a perfect fit for both parties.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Resume Building</h3>
                    <p>Our experts help you create professional resumes that highlight your skills and experience, increasing your chances of getting hired.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Recruitment Services</h3>
                    <p>We offer comprehensive recruitment solutions for businesses looking to hire talented professionals for their team.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Career Counseling</h3>
                    <p>Get professional guidance on career development, job transitions, and skill enhancement to advance in your professional journey.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Interview Preparation</h3>
                    <p>We prepare candidates for successful interviews with mock sessions, feedback, and industry-specific guidance.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>HR Consulting</h3>
                    <p>We provide businesses with expert HR consulting services to optimize their workforce management and recruitment processes.</p>
                </div>
            </div>
        </div>
    </section>
    <?php include 'includes/services.php'; ?>
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
                    // In a real application, you would send an email here
                    // For now, just simulate success
                    $contact_success = "Your message has been sent successfully! We'll get back to you shortly.";
                    
                    // Reset form fields
                    $name = $email = $phone = $subject = $message = '';
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

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
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
    </script>
</body>
</html>