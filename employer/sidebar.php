<?php
// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="dashboard-user-info">
    <div class="user-avatar">
        <i class="fas fa-user-circle"></i>
    </div>
    <div class="user-details">
        <h3><?php echo $_SESSION['user_name']; ?></h3>
        <p><?php echo $_SESSION['user_email']; ?></p>
        <span class="user-role">Employer</span>
    </div>
</div>

<ul class="dashboard-menu">
    <li class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
        <a href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="<?php echo $current_page === 'post-job.php' ? 'active' : ''; ?>">
        <a href="post-job.php">
            <i class="fas fa-plus-circle"></i> Post a Job
        </a>
    </li>
    <li class="<?php echo $current_page === 'manage-jobs.php' ? 'active' : ''; ?>">
        <a href="manage-jobs.php">
            <i class="fas fa-briefcase"></i> Manage Jobs
        </a>
    </li>
    <li class="<?php echo $current_page === 'applications.php' ? 'active' : ''; ?>">
        <a href="applications.php">
            <i class="fas fa-file-alt"></i> Applications
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