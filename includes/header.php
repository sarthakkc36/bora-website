<?php
require_once __DIR__ . '/../config.php';
?>
<?php
// Get current page to highlight active nav link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Header -->
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'index.php' : '../index.php'; ?>">
                    <img src="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'images/logo.png' : '../images/logo.png'; ?>" alt="B&H Employment & Consultancy Logo">
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
                    <li>
                        <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'login.php' : '../login.php'; ?>" 
                           class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a>
                    </li>
                    <li>
                        <a href="<?php echo $current_page == 'index.php' || strpos($current_page, '/') !== false ? 'register.php' : '../register.php'; ?>" 
                           class="<?php echo $current_page === 'register.php' ? 'active' : ''; ?> highlight-btn">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
</header>