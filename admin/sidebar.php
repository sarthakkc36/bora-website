<?php
// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);

// Get count of unread messages for admin sidebar
$unread_messages_count = 0;
try {
    $messages_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM contact_messages WHERE is_read = 0");
    $messages_stmt->execute();
    $unread_messages_count = $messages_stmt->fetch()['unread_count'];
} catch (PDOException $e) {
    error_log("Error fetching unread messages count: " . $e->getMessage());
}
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
    <li class="<?php echo $current_page === 'contact-messages.php' ? 'active' : ''; ?>">
        <a href="contact-messages.php">
            <i class="fas fa-envelope"></i> Messages
            <?php if ($unread_messages_count > 0): ?>
                <span class="badge-small"><?php echo $unread_messages_count; ?></span>
            <?php endif; ?>
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

<style>
.badge-small {
    background-color: #0066cc;
    color: white;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 5px;
}
</style>