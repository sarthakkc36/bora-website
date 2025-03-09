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
$favicon = $site_settings['favicon'] ?? 'images/favicon.ico';
$current_page = basename($_SERVER['PHP_SELF']);
$is_root = (dirname($_SERVER['PHP_SELF']) == '/' || dirname($_SERVER['PHP_SELF']) == '\\');
$base_path = $is_root ? '' : '../';
?>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'B&H Employment & Consultancy Inc'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($site_settings['site_description'] ?? 'Professional employment agency connecting qualified candidates with top employers'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/updated-styles.css">
    
    <!-- Dynamic Favicon -->
    <?php if (!empty($site_settings['favicon'])): ?>
        <link rel="icon" href="<?php echo $base_path . $site_settings['favicon']; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($site_settings['favicon'], PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($site_settings['favicon'], PATHINFO_EXTENSION); ?>">
        <link rel="shortcut icon" href="<?php echo $base_path . $site_settings['favicon']; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($site_settings['favicon'], PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($site_settings['favicon'], PATHINFO_EXTENSION); ?>">
    <?php else: ?>
        <link rel="icon" href="<?php echo $base_path; ?>images/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="<?php echo $base_path; ?>images/favicon.ico" type="image/x-icon">
    <?php endif; ?>
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
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="B&H Employment & Consultancy Inc">
                </a>
            </div>
            
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
            
            <ul class="nav-menu">
                <li>
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        Home
                    </a>
                </li>
                <li>
                    <a href="submit-job.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'submit-job.php' ? 'active' : ''; ?>">
                        Submit a Job
                    </a>
                </li>
                <li>
                    <a href="request-appointment.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'request-appointment.php' ? 'active' : ''; ?>">
                        Request Appointment
                    </a>
                </li>
                <li>
                    <a href="index.php#about" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php#about' ? 'active' : ''; ?>">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="index.php#contact" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php#contact' ? 'active' : ''; ?>">
                        Contact
                    </a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                    <li>
                        <a href="jobs.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'jobs.php' ? 'active' : ''; ?>">
                            Browse Jobs
                        </a>
                    </li>
                    
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="admin/dashboard.php">
                                Admin Dashboard
                            </a>
                        </li>
                    <?php elseif (isJobSeeker()): ?>
                        <li>
                            <a href="job-seeker/dashboard.php">
                                My Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="user-menu">
                        <a href="#" class="user-toggle">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="user-dropdown">
                            <?php if (isAdmin()): ?>
                                <li>
                                    <a href="admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="admin/profile.php">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                </li>
                            <?php elseif (isJobSeeker()): ?>
                                <li>
                                    <a href="job-seeker/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="job-seeker/profile.php">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a href="job-seeker/applications.php">
                                        <i class="fas fa-file-alt"></i> My Applications
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="login-btn">
                            Login
                        </a>
                    </li>
                    <li>
                        <a href="register.php" class="highlight-btn">
                            Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>