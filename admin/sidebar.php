<?php
// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="dashboard-user-info">
    <div class="user-avatar admin">
        <i class="fas fa-user-shield"></i>
    </div>
    <div class="user-details">
        <h3><?php echo $_SESSION['user_name']; ?></h3>
        <p><?php echo $_SESSION['user_email']; ?></p>
        <span class="user-role admin">Administrator</span>
    </div>
</div>

<ul class="dashboard-menu">
    <li class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
        <a href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="<?php echo $current_page === 'manage-users.php' ? 'active' : ''; ?>">
        <a href="manage-users.php">
            <i class="fas fa-users"></i> Manage Users
        </a>
    </li>
    <li class="<?php echo $current_page === 'manage-jobs.php' ? 'active' : ''; ?>">
        <a href="manage-jobs.php">
            <i class="fas fa-briefcase"></i> Manage Jobs
        </a>
    </li>
    <li class="<?php echo $current_page === 'manage-services.php' ? 'active' : ''; ?>">
        <a href="manage-services.php">
            <i class="fas fa-cogs"></i> Manage Services
        </a>
    </li>
    <li class="<?php echo $current_page === 'site-settings.php' ? 'active' : ''; ?>">
        <a href="site-settings.php">
            <i class="fas fa-sliders-h"></i> Site Settings
        </a>
    </li>
    <li class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
        <a href="profile.php">
            <i class="fas fa-user"></i> Profile
        </a>
    </li>
    <li>
        <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </li>
</ul>