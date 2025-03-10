<?php
require_once __DIR__ . '/../config.php';

// Get current page to highlight active nav link
$current_page = basename($_SERVER['PHP_SELF']);

// Determine if we're in the root directory or a subdirectory
$is_root = (dirname($_SERVER['PHP_SELF']) == '/' || dirname($_SERVER['PHP_SELF']) == '\\');
$base_path = $is_root ? '' : '../';

// Get site settings
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

// Set default values if settings are not found
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/updated-styles.css">
    
    <!-- Dynamic Favicon -->
    <?php
    $favicon_path = '';
    if (!empty($site_settings['favicon'])) {
        $favicon_path = $base_path . $site_settings['favicon'];
        $favicon_type = pathinfo($site_settings['favicon'], PATHINFO_EXTENSION);
        $favicon_type = ($favicon_type === 'ico') ? 'x-icon' : $favicon_type;
    } else {
        $favicon_path = $base_path . 'images/favicon.ico';
        $favicon_type = 'x-icon';
    }
    ?>
    <link rel="icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo $favicon_type; ?>">
    <link rel="shortcut icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo $favicon_type; ?>">
    
    <!-- Mobile-friendly meta tags -->
    <meta name="theme-color" content="#0066cc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>

<!-- Header -->
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    <?php if (!empty($site_settings['site_logo']) && file_exists($is_root ? $site_settings['site_logo'] : '../' . $site_settings['site_logo'])): ?>
                        <img src="<?php echo $base_path . $site_settings['site_logo']; ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo">
                    <?php else: ?>
                        <img src="<?php echo $base_path; ?>images/logo.png" alt="<?php echo htmlspecialchars($site_title); ?> Logo">
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="mobile-menu-btn" aria-label="Toggle navigation menu">
                <i class="fas fa-bars"></i>
            </div>
            
            <nav class="main-navigation">
                <ul class="nav-menu">
                    <li>
                        <a href="<?php echo $base_path; ?>index.php" <?php echo $current_page === 'index.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-home"></i> <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>jobs.php" <?php echo $current_page === 'jobs.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-briefcase"></i> <span>Jobs</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>index.php#services" <?php echo $current_page === 'services.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-cogs"></i> <span>Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>index.php#about" <?php echo $current_page === 'about.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-info-circle"></i> <span>About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>index.php#contact" <?php echo $current_page === 'contact.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-envelope"></i> <span>Contact</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>submit-job.php" <?php echo $current_page === 'submit-job.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-plus-circle"></i> <span>Submit a Job</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_path; ?>request-appointment.php" <?php echo $current_page === 'request-appointment.php' ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-calendar-alt"></i> <span>Request Appointment</span>
                        </a>
                    </li>
                </ul>
                
                <ul class="auth-menu">
                    <?php if (isLoggedIn()): ?>
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <i class="fas fa-user-circle"></i> 
                                <span><?php echo isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User'; ?></span>
                                <i class="fas fa-caret-down"></i>
                            </a>
                            <ul class="user-dropdown">
                                <?php if (isAdmin()): ?>
                                    <li>
                                        <a href="<?php echo $base_path; ?>admin/dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                        </a>
                                    </li>
                                <?php elseif (isEmployer()): ?>
                                    <li>
                                        <a href="<?php echo $base_path; ?>employer/dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> Employer Dashboard
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="<?php echo $base_path; ?>job-seeker/dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <li>
                                    <a href="<?php echo $base_path; ?>logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo $base_path; ?>login.php" class="login-btn <?php echo $current_page === 'login.php' ? 'active' : ''; ?>">
                                <i class="fas fa-sign-in-alt"></i> <span>Login</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_path; ?>register.php" class="highlight-btn <?php echo $current_page === 'register.php' ? 'active' : ''; ?>">
                                <i class="fas fa-user-plus"></i> <span>Register</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<style>
/* Enhanced Header Styles */
header {
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    background-color: #fff;
    transition: all 0.3s ease;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
}

.logo {
    flex: 0 0 auto;
    z-index: 101;
}

.logo img {
    height: 60px;
    max-width: 100%;
    transition: all 0.3s ease;
}

.main-navigation {
    display: flex;
    justify-content: space-between;
    flex: 1;
    margin-left: 20px;
}

.nav-menu,
.auth-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
}

.nav-menu {
    flex-grow: 1;
    justify-content: center;
    flex-wrap: wrap;
}

.auth-menu {
    margin-left: 15px;
}

.nav-menu li,
.auth-menu li {
    margin: 0 5px;
    position: relative;
}

.nav-menu a,
.auth-menu a {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #333;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 30px;
}

.nav-menu a i,
.auth-menu a i {
    margin-right: 5px;
    font-size: 16px;
}

.nav-menu a:hover,
.auth-menu a:hover {
    color: #0066cc;
    background-color: rgba(0, 102, 204, 0.05);
}

.nav-menu a.active,
.auth-menu a.active {
    color: #0066cc;
    background-color: rgba(0, 102, 204, 0.08);
}

.login-btn, 
.highlight-btn {
    padding: 8px 20px;
}

.login-btn {
    border: 2px solid #0066cc;
    color: #0066cc !important;
}

.highlight-btn {
    background: linear-gradient(135deg, #0066cc, #0052a3);
    color: white !important;
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
}

.login-btn:hover {
    background-color: rgba(0, 102, 204, 0.1);
}

.highlight-btn:hover {
    background: linear-gradient(135deg, #0052a3, #004080);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 102, 204, 0.4);
}

/* User dropdown menu */
.user-menu {
    position: relative;
}

.user-toggle {
    cursor: pointer;
    display: flex;
    align-items: center;
}

.user-toggle i.fa-caret-down {
    margin-left: 5px;
    transition: transform 0.3s ease;
}

.user-menu:hover .user-toggle i.fa-caret-down {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    min-width: 200px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 10px 0;
    z-index: 101;
    transform: translateY(10px);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.user-dropdown.active,
.user-menu:hover .user-dropdown {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

.user-dropdown li {
    margin: 0;
    padding: 0;
}

.user-dropdown a {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    color: #333;
    border-radius: 0;
}

.user-dropdown a i {
    margin-right: 10px;
    color: #0066cc;
}

.user-dropdown a:hover {
    background-color: #f5f5f5;
    color: #0066cc;
}

/* Mobile menu button */
.mobile-menu-btn {
    display: none;
    cursor: pointer;
    font-size: 24px;
    z-index: 101;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f5f5f5;
    color: #333;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.mobile-menu-btn:hover, 
.mobile-menu-btn.active {
    background-color: #0066cc;
    color: white;
}

/* Responsive Styles */
@media (max-width: 1200px) {
    .nav-menu a,
    .auth-menu a {
        padding: 8px 12px;
    }
    
    .nav-menu a i,
    .auth-menu a i {
        margin-right: 3px;
    }
}

@media (max-width: 992px) {
    .mobile-menu-btn {
        display: flex;
    }
    
    .main-navigation {
        position: fixed;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100vh;
        flex-direction: column;
        background-color: white;
        z-index: 100;
        padding: 80px 20px 20px;
        overflow-y: auto;
        transition: all 0.3s ease;
        margin-left: 0;
    }
    
    .main-navigation.active {
        left: 0;
    }
    
    .nav-menu, 
    .auth-menu {
        flex-direction: column;
        width: 100%;
    }
    
    .nav-menu {
        margin-bottom: 20px;
    }
    
    .auth-menu {
        margin-left: 0;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }
    
    .nav-menu li,
    .auth-menu li {
        width: 100%;
        margin: 5px 0;
    }
    
    .nav-menu a,
    .auth-menu a {
        width: 100%;
        padding: 12px 15px;
        border-radius: 8px;
    }
    
    .login-btn, 
    .highlight-btn {
        justify-content: center;
        margin-top: 10px;
    }
    
    .user-dropdown {
        position: static;
        box-shadow: none;
        opacity: 1;
        visibility: visible;
        transform: none;
        padding: 0;
        background-color: #f5f5f5;
        margin-top: 10px;
        border-radius: 8px;
        display: none;
    }
    
    .user-dropdown.active {
        display: block;
    }
    
    .user-menu:hover .user-dropdown {
        display: none;
    }
    
    .user-menu:hover .user-dropdown.active {
        display: block;
    }
}

@media (max-width: 768px) {
    .logo img {
        height: 50px;
    }
    
    .header-content {
        padding: 10px 0;
    }
}

/* Animation for mobile menu */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.main-navigation.active .nav-menu li,
.main-navigation.active .auth-menu li {
    animation: fadeIn 0.5s forwards;
    opacity: 0;
}

.main-navigation.active .nav-menu li:nth-child(1) { animation-delay: 0.1s; }
.main-navigation.active .nav-menu li:nth-child(2) { animation-delay: 0.15s; }
.main-navigation.active .nav-menu li:nth-child(3) { animation-delay: 0.2s; }
.main-navigation.active .nav-menu li:nth-child(4) { animation-delay: 0.25s; }
.main-navigation.active .nav-menu li:nth-child(5) { animation-delay: 0.3s; }
.main-navigation.active .nav-menu li:nth-child(6) { animation-delay: 0.35s; }
.main-navigation.active .nav-menu li:nth-child(7) { animation-delay: 0.4s; }
.main-navigation.active .auth-menu li:nth-child(1) { animation-delay: 0.45s; }
.main-navigation.active .auth-menu li:nth-child(2) { animation-delay: 0.5s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNavigation = document.querySelector('.main-navigation');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenuBtn.classList.toggle('active');
            mainNavigation.classList.toggle('active');
            document.body.classList.toggle('menu-open');
            
            // Accessibility
            const expanded = mainNavigation.classList.contains('active');
            mobileMenuBtn.setAttribute('aria-expanded', expanded);
        });
    }
    
    // User dropdown toggle
    const userToggle = document.querySelector('.user-toggle');
    if (userToggle) {
        userToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('active');
            
            // Accessibility
            const expanded = dropdown.classList.contains('active');
            this.setAttribute('aria-expanded', expanded);
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userToggle && !userToggle.contains(e.target)) {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
                userToggle.setAttribute('aria-expanded', 'false');
            }
        }
    });
    
    // Add scroll effect to header
    let lastScrollTop = 0;
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            header.classList.add('scrolled');
            
            if (scrollTop > lastScrollTop) {
                // Scrolling down
                header.classList.add('hidden');
            } else {
                // Scrolling up
                header.classList.remove('hidden');
            }
        } else {
            header.classList.remove('scrolled', 'hidden');
        }
        
        lastScrollTop = scrollTop;
    });
});
</script>

<style>
/* Additional header scroll effect */
header.scrolled {
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    padding: 0;
}

header.scrolled .logo img {
    height: 50px;
}

header.hidden {
    transform: translateY(-100%);
}

body.menu-open {
    overflow: hidden;
}
</style>