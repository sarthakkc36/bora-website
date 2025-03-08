<?php
require_once __DIR__ . '/../config.php';

// Get current page to highlight active nav link
$current_page = basename($_SERVER['PHP_SELF']);

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

<!-- Header -->
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php' : '../index.php'; ?>">
                    <img src="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'images/logo.png' : '../images/logo.png'; ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo">
                </a>
            </div>
            <ul class="nav-menu">
                <li>
                    <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php' : '../index.php'; ?>" 
                       class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
                </li>
                <li>
                    <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'jobs.php' : '../jobs.php'; ?>" 
                       class="<?php echo $current_page === 'jobs.php' ? 'active' : ''; ?>">Jobs</a>
                </li>
                <li>
                    <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#services' : '../index.php#services'; ?>" 
                       class="<?php echo $current_page === 'services.php' ? 'active' : ''; ?>">Services</a>
                </li>
                <li>
                    <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#about' : '../index.php#about'; ?>" 
                       class="<?php echo $current_page === 'about.php' ? 'active' : ''; ?>">About Us</a>
                </li>
                <li>
                    <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php#contact' : '../index.php#contact'; ?>" 
                       class="<?php echo $current_page === 'contact.php' ? 'active' : ''; ?>">Contact</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="user-menu">
                    <a href="#" class="user-toggle">
                        <i class="fas fa-user-circle"></i> <?php echo isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User'; ?> <i class="fas fa-caret-down"></i>
                    </a>
                        <ul class="user-dropdown">
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'admin/dashboard.php' : '../admin/dashboard.php'; ?>">
                                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
                                </li>
                            <?php elseif (isEmployer()): ?>
                                <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'employer/dashboard.php' : '../employer/dashboard.php'; ?>">
                                    <i class="fas fa-tachometer-alt"></i> Employer Dashboard</a>
                                </li>
                            <?php else: ?>
                                <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'job-seeker/dashboard.php' : '../job-seeker/dashboard.php'; ?>">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                </li>
                            <?php endif; ?>
                            
                            <li><a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'logout.php' : '../logout.php'; ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="auth-buttons">
                        <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'login.php' : '../login.php'; ?>" 
                           class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?> login-btn">
                           <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'register.php' : '../register.php'; ?>" 
                           class="<?php echo $current_page === 'register.php' ? 'active' : ''; ?> highlight-btn">
                           <i class="fas fa-building"></i> Register as Employer
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
</header>