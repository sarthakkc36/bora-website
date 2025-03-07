<?php
require_once 'config.php';

$errors = [];
$login_identifier = '';  // Store the username or email

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = sanitizeInput($_POST['login_identifier']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($login_identifier)) {
        $errors[] = "Username or Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        try {
            // Check if login_identifier is username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
            $stmt->bindParam(':identifier', $login_identifier);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // If employer, check verification status
                    if ($user['role'] === 'employer' && $user['is_verified'] != 1) {
                        $errors[] = "Your account is pending verification by an administrator. Please check back later or contact support.";
                    } else {
                        // Check subscription for employers
                        if ($user['role'] === 'employer') {
                            // If subscription has expired, note it but still allow login
                            $subscription_warning = '';
                            if (!empty($user['subscription_end']) && new DateTime() > new DateTime($user['subscription_end'])) {
                                $subscription_warning = "Your subscription has expired. Please contact an administrator to renew.";
                            }
                        }
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        
                        // Add verification and subscription data to session
                        $_SESSION['is_verified'] = $user['is_verified'];
                        $_SESSION['subscription_start'] = $user['subscription_start'] ?? null;
                        $_SESSION['subscription_end'] = $user['subscription_end'] ?? null;
                        
                        // Set subscription warning if any
                        if (!empty($subscription_warning)) {
                            flashMessage($subscription_warning, "warning");
                        }
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            redirect('admin/dashboard.php');
                        } elseif ($user['role'] === 'employer') {
                            redirect('employer/dashboard.php');
                        } else {
                            redirect('index.php');
                        }
                    }
                } else {
                    $errors[] = "Invalid username/email or password";
                }
            } else {
                $errors[] = "Invalid username/email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/updated-styles.css">
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
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Login to Your Account</h1>
            <p>Access your personalized dashboard and manage your job search or recruitment process</p>
        </div>
    </section>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php displayFlashMessage(); ?>
                
                <form class="auth-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="login_identifier">Username or Email</label>
                        <input type="text" id="login_identifier" name="login_identifier" class="form-control" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group auth-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="submit-btn">Login</button>
                    
                    <div class="auth-links">
                        <p>Don't have an account? <a href="register.php">Register Now</a></p>
                    </div>
                </form>
            </div>
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