<?php
require_once __DIR__ . '/../config.php';
$additional_styles = '/../css/styles.css';

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
    <link rel="stylesheet" href="<?php echo $base_path; ?>../css/styles.css">
    
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
    <!-- Mobile Optimizations -->
<link rel="stylesheet" href="<?php echo $base_path; ?>css/mobile/mobile-main.css">
<script src="<?php echo $base_path; ?>js/mobile/mobile-menu.js" defer></script>

<?php
// Load page-specific mobile optimizations
$current_page = basename($_SERVER['PHP_SELF']);

// Job listings pages
if ($current_page == 'jobs.php' || $current_page == 'job-details.php') {
    echo '<link rel="stylesheet" href="' . $base_path . 'css/mobile/mobile-job-listing.css">';
    echo '<script src="' . $base_path . 'js/mobile/mobile-job-interactions.js" defer></script>';
}

// Dashboard pages
if (strpos($current_page, 'dashboard.php') !== false || strpos($_SERVER['PHP_SELF'], '/job-seeker/') !== false) {
    echo '<link rel="stylesheet" href="' . $base_path . 'css/mobile/mobile-dashboard.css">';
    echo '<script src="' . $base_path . 'js/mobile/mobile-dashboard.js" defer></script>';
}

// Admin pages
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    echo '<link rel="stylesheet" href="' . $base_path . 'css/mobile/mobile-admin-dashboard.css">';
}

// Appointment booking page
if ($current_page == 'request-appointment.php') {
    echo '<link rel="stylesheet" href="' . $base_path . 'css/mobile/mobile-appointment-booking.css">';
    echo '<script src="' . $base_path . 'js/mobile/mobile-appointment.js" defer></script>';
}
?>
<style>/* Comprehensive Mobile Menu and Responsiveness Fixes */
@media (max-width: 768px) {
    /* Reset and prevent any unwanted boxes or margins */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        position: relative;
        overflow-x: hidden;
        width: 100%;
    }

    /* Header and Navigation Adjustments */
    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        background-color: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .container {
        padding: 0 15px;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 60px;
    }

    .logo {
        z-index: 1001;
    }

    .logo img {
        max-height: 40px;
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: block;
        cursor: pointer;
        z-index: 1001;
        position: relative;
    }

    .mobile-menu-btn i {
        font-size: 24px;
        color: #333;
    }

    /* Navigation Container */
    .main-navigation {
        position: fixed;
        top: 0;
        left: -150%;
        width: 100%;
        height: 100%;
        background-color: white;
        transition: left 0.3s ease;
        z-index: 1000;
        padding: 80px 20px 20px;
        overflow-y: auto;
    }

    .main-navigation.menu-open {
        left: 0;
    }

    /* Navigation Menus */
    .nav-menu,
    .auth-menu {
        display: none;
        width: 100%;
        list-style: none;
    }

    .nav-menu.active,
    .auth-menu.active {
        display: block;
    }

    .nav-menu li,
    .auth-menu li {
        width: 100%;
        margin: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .nav-menu a,
    .auth-menu a {
        display: block;
        padding: 15px;
        text-decoration: none;
        color: #333;
    }

    /* Prevent body scroll when menu is open */
    body.menu-locked {
        overflow: hidden;
        position: fixed;
        width: 100%;
        height: 100%;
    }

    /* Main content padding to account for fixed header */
    main {
        padding-top: 60px;
    }

    /* User Dropdown in Mobile */
    .user-dropdown {
        position: static;
        background-color: transparent;
        box-shadow: none;
        display: block;
        transform: none;
        opacity: 1;
        visibility: visible;
        width: 100%;
    }

    .user-dropdown li {
        border-bottom: 1px solid #eee;
    }

    .user-dropdown a {
        padding: 15px;
    }

    /* Ensure full-width layout */
    .page-title,
    .container {
        width: 100%;
        padding: 0 15px;
        margin: 0;
    }
}
/* Mobile Menu and Button Styles */
@media (max-width: 768px) {
    /* Reset unwanted positioning */
    .main-navigation {
        display: none;
        position: fixed;
        top: 0;
        left: -150%;
        width: 100%;
        height: 100%;
        background-color: white;
        z-index: 1000;
        transition: left 0.3s ease;
        padding: 80px 20px 20px;
        overflow-y: auto;
    }

    .main-navigation.menu-open {
        display: block;
        left: 0;
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: block;
        cursor: pointer;
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 1001;
    }

    .mobile-menu-btn i {
        font-size: 24px;
        color: #333;
    }

    /* Hero Buttons Styling */
    .hero-image-only .cta-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        position: absolute;
        bottom: auto;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 40px);
        max-width: 300px;
    }

    .hero-cta-btn {
        display: block;
        width: 100%;
        padding: 12px 20px;
        border-radius: 30px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .hero-cta-btn.secondary {
        background-color: white;
        color: #0066cc;
        border: 2px solid #0066cc;
    }

    .hero-cta-btn.secondary:hover {
        background-color: #0066cc;
        color: white;
    }
}
@media (max-width: 768px) {
    /* Admin Dropdown Styles */
    .admin-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        cursor: pointer;
        width: 100%;
        border-bottom: 1px solid #eee;
    }

    .admin-dropdown {
        display: none;
        width: 100%;
        background-color: #f9f9f9;
    }

    .admin-dropdown.show {
        display: block;
    }

    .admin-dropdown li {
        width: 100%;
        border-bottom: 1px solid #eee;
    }

    .admin-dropdown a {
        display: block;
        padding: 15px;
        text-decoration: none;
        color: #333;
    }

    /* Caret icon rotation */
    .admin-toggle .fa-caret-down {
        transition: transform 0.3s ease;
    }

    .admin-toggle .fa-caret-down.rotated {
        transform: rotate(180deg);
    }
}
</style>
</head>
<body>

<!-- Header -->
<!-- Header -->
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    <img src="<?php echo $base_path; ?>images/logo.png" alt="B&H Employment Logo">
                </a>
            </div>
            
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
            
            <div class="main-navigation">
                <ul class="nav-menu">
                    <li><a href="<?php echo $base_path; ?>index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?php echo $base_path; ?>jobs.php"><i class="fas fa-briefcase"></i> Jobs</a></li>
                    <li><a href="<?php echo $base_path; ?>index.php#services"><i class="fas fa-cogs"></i> Services</a></li>
                    <li><a href="<?php echo $base_path; ?>index.php#about"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="<?php echo $base_path; ?>index.php#contact"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="<?php echo $base_path; ?>submit-job.php"><i class="fas fa-plus-circle"></i> Submit a Job</a></li>
                    <li><a href="<?php echo $base_path; ?>request-appointment.php"><i class="fas fa-calendar-alt"></i> Request Appointment</a></li>
                </ul>
                
                <ul class="auth-menu">
                    <?php if (isLoggedIn()): ?>
                        <li class="user-menu">
                            <a href="#" class="user-toggle">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User'; ?>
                                <i class="fas fa-caret-down"></i>
                            </a>
                            <ul class="user-dropdown">
                                <?php if (isAdmin()): ?>
                                    <li><a href="<?php echo $base_path; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a></li>
                                <?php elseif (isEmployer()): ?>
                                    <li><a href="<?php echo $base_path; ?>employer/dashboard.php"><i class="fas fa-tachometer-alt"></i> Employer Dashboard</a></li>
                                <?php else: ?>
                                    <li><a href="<?php echo $base_path; ?>job-seeker/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo $base_path; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>login.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="<?php echo $base_path; ?>register.php" class="highlight-btn"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</header>
<script>
// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNavigation = document.querySelector('.main-navigation');
    const navMenu = document.querySelector('.nav-menu');
    const authMenu = document.querySelector('.auth-menu');
    const body = document.body;

    function toggleMobileMenu() {
        if (!mobileMenuBtn || !mainNavigation) return;

        // Toggle visibility classes
        mobileMenuBtn.classList.toggle('active');
        mainNavigation.classList.toggle('menu-open');
        body.classList.toggle('menu-locked');

        // Ensure menus are visible when open
        if (navMenu) {
            navMenu.classList.toggle('active');
        }
        if (authMenu) {
            authMenu.classList.toggle('active');
        }
    }

    // Attach click event to mobile menu button
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }

    // Close menu when menu items are clicked
    const menuLinks = document.querySelectorAll('.nav-menu a, .auth-menu a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768 && mainNavigation.classList.contains('menu-open')) {
                toggleMobileMenu();
            }
        });
    });

    // Reset menu on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Reset styles for desktop
            if (mainNavigation) mainNavigation.classList.remove('menu-open');
            if (navMenu) navMenu.classList.remove('active');
            if (authMenu) authMenu.classList.remove('active');
            if (mobileMenuBtn) mobileMenuBtn.classList.remove('active');
            body.classList.remove('menu-locked');
        }
    });

    // Prevent unwanted scrolling or selection issues
    document.addEventListener('touchmove', function(e) {
        if (body.classList.contains('menu-locked')) {
            e.preventDefault();
        }
    }, { passive: false });
});
document.addEventListener('DOMContentLoaded', function() {
    // Select Admin dropdown elements more carefully
    const adminToggles = document.querySelectorAll('[class*="admin-toggle"]');
    
    adminToggles.forEach(function(adminToggle) {
        // Find the dropdown associated with this toggle
        const adminDropdown = adminToggle.nextElementSibling;
        
        if (adminDropdown && adminDropdown.classList.contains('admin-dropdown')) {
            // Prevent default link behavior and toggle dropdown
            adminToggle.addEventListener('click', function(e) {
                // Prevent default only if it's a '#' link or has no href
                if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                e.stopPropagation();
                
                // Toggle dropdown visibility
                adminDropdown.classList.toggle('show');
                
                // Rotate caret icon if exists
                const caretIcon = this.querySelector('.fa-caret-down');
                if (caretIcon) {
                    caretIcon.classList.toggle('rotated');
                }
            });
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        const openDropdowns = document.querySelectorAll('.admin-dropdown.show');
        openDropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('show');
            const caretIcon = dropdown.previousElementSibling.querySelector('.fa-caret-down');
            if (caretIcon) {
                caretIcon.classList.remove('rotated');
            }
        });
    });

    // Prevent dropdown from closing when clicking inside
    const adminDropdowns = document.querySelectorAll('.admin-dropdown');
    adminDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
</script>
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

<script src="../js/script.js"></script>

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
