<?php
// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);

// Get user info
$user_id = $_SESSION['user_id'];
try {
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();
    $user_data = $user_stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching user data for sidebar: " . $e->getMessage());
    $user_data = [];
}

// Get profile completion percentage
$profile_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'skills', 'education', 'experience'];
$filled_fields = 0;
$total_fields = count($profile_fields);

foreach ($profile_fields as $field) {
    if (isset($user_data[$field]) && !empty($user_data[$field])) {
        $filled_fields++;
    }
}

$profile_percentage = ($total_fields > 0) ? round(($filled_fields / $total_fields) * 100) : 0;
?>

<div class="dashboard-user-info">
    <div class="user-avatar">
        <?php if (!empty($user_data['profile_image'])): ?>
            <img src="<?php echo htmlspecialchars($user_data['profile_image']); ?>" alt="<?php echo htmlspecialchars($user_data['first_name']); ?>">
        <?php else: ?>
            <i class="fas fa-user"></i>
        <?php endif; ?>
    </div>
    <div class="user-details">
        <h3><?php echo isset($user_data['first_name']) ? htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) : $_SESSION['user_name']; ?></h3>
        <p><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        <span class="user-role job-seeker">Job Seeker</span>
    </div>
    
    <div class="profile-completion">
        <div class="completion-label">
            <span>Profile Completion</span>
            <span><?php echo $profile_percentage; ?>%</span>
        </div>
        <div class="completion-bar">
            <div class="completion-fill" style="width: <?php echo $profile_percentage; ?>%"></div>
        </div>
    </div>
</div>

<ul class="dashboard-menu">
    <li class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
        <a href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
        <a href="profile.php">
            <i class="fas fa-user"></i> My Profile
        </a>
    </li>
    <li class="<?php echo $current_page === 'resume.php' ? 'active' : ''; ?>">
        <a href="resume.php">
            <i class="fas fa-file-alt"></i> My Resume
        </a>
    </li>
    <li class="<?php echo $current_page === 'applications.php' ? 'active' : ''; ?>">
        <a href="applications.php">
            <i class="fas fa-paper-plane"></i> My Applications
        </a>
    </li>
    <li class="<?php echo $current_page === 'saved-jobs.php' ? 'active' : ''; ?>">
        <a href="saved-jobs.php">
            <i class="fas fa-bookmark"></i> Saved Jobs
        </a>
    </li>
    <li class="<?php echo $current_page === 'job-alerts.php' ? 'active' : ''; ?>">
        <a href="job-alerts.php">
            <i class="fas fa-bell"></i> Job Alerts
        </a>
    </li>
    <li class="<?php echo $current_page === 'messages.php' ? 'active' : ''; ?>">
        <a href="messages.php">
            <i class="fas fa-envelope"></i> Messages
            <?php
            // Check for unread messages
            try {
                $msg_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE recipient_id = :user_id AND is_read = 0");
                $msg_stmt->bindParam(':user_id', $user_id);
                $msg_stmt->execute();
                $unread_count = $msg_stmt->fetch()['unread_count'];
                
                if ($unread_count > 0): 
            ?>
                <span class="badge-small"><?php echo $unread_count; ?></span>
            <?php 
                endif;
            } catch (PDOException $e) {
                error_log("Error checking unread messages: " . $e->getMessage());
            }
            ?>
        </a>
    </li>
    <li class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
        <a href="settings.php">
            <i class="fas fa-cog"></i> Account Settings
        </a>
    </li>
    <li>
        <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </li>
</ul>

<div class="sidebar-footer">
    <a href="../jobs.php" class="btn-primary">
        <i class="fas fa-search"></i> Browse Jobs
    </a>
</div>

<style>
.profile-completion {
    margin-top: 15px;
    width: 100%;
}

.completion-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
    color: #666;
}

.completion-bar {
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.completion-fill {
    height: 100%;
    background: linear-gradient(90deg, #0066cc, #4da6ff);
    border-radius: 4px;
    transition: width 1s ease-out;
}

.sidebar-footer {
    margin-top: 20px;
    padding: 15px;
    text-align: center;
}

.badge-small {
    background-color: #ff3366;
    color: white;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 5px;
}
</style>