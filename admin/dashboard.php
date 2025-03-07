<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Get site statistics
try {
    // User counts
    $stmt = $pdo->prepare("SELECT 
                          COUNT(*) as total_users,
                          SUM(CASE WHEN role = 'employer' THEN 1 ELSE 0 END) as employer_count,
                          SUM(CASE WHEN role = 'job_seeker' THEN 1 ELSE 0 END) as job_seeker_count
                          FROM users");
    $stmt->execute();
    $user_stats = $stmt->fetch();
    
    // Job counts
    $stmt = $pdo->prepare("SELECT 
                          COUNT(*) as total_jobs,
                          SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_jobs
                          FROM jobs");
    $stmt->execute();
    $job_stats = $stmt->fetch();
    
    // Application count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_applications FROM job_applications");
    $stmt->execute();
    $application_count = $stmt->fetch()['total_applications'];
    
    // Recent users
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
    // Recent jobs
    $stmt = $pdo->prepare("SELECT j.*, u.username as employer_username 
                          FROM jobs j 
                          JOIN users u ON j.user_id = u.id 
                          ORDER BY j.created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $recent_jobs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching admin statistics: " . $e->getMessage());
    $user_stats = ['total_users' => 0, 'employer_count' => 0, 'job_seeker_count' => 0];
    $job_stats = ['total_jobs' => 0, 'active_jobs' => 0];
    $application_count = 0;
    $recent_users = $recent_jobs = [];
}

// Format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <p>Manage users, jobs, and site content</p>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <?php include 'sidebar.php'; ?>
                </div>
                
                <div class="dashboard-content">
                    <?php displayFlashMessage(); ?>
                    
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($user_stats['total_users']); ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($user_stats['employer_count']); ?></h3>
                                <p>Employers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($job_stats['total_jobs']); ?></h3>
                                <p>Total Jobs</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon active">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($job_stats['active_jobs']); ?></h3>
                                <p>Active Jobs</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($application_count); ?></h3>
                                <p>Applications</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-quick-actions">
                        <a href="manage-users.php" class="quick-action">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="manage-jobs.php" class="quick-action">
                            <i class="fas fa-briefcase"></i>
                            <span>Manage Jobs</span>
                        </a>
                        <a href="manage-services.php" class="quick-action">
                            <i class="fas fa-cogs"></i>
                            <span>Manage Services</span>
                        </a>
                        <a href="site-settings.php" class="quick-action">
                            <i class="fas fa-sliders-h"></i>
                            <span>Site Settings</span>
                        </a>
                    </div>
                    
                    <div class="dashboard-row">
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-users"></i> Recent Users</h2>
                                    <a href="manage-users.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_users)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <p>No users found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="users-list">
                                            <?php foreach ($recent_users as $user): ?>
                                                <div class="user-item">
                                                    <div class="user-avatar">
                                                        <i class="fas fa-user-circle"></i>
                                                    </div>
                                                    <div class="user-details">
                                                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                                        <div class="user-meta">
                                                            <span class="user-role <?php echo $user['role']; ?>"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                                                            <span class="user-date">Joined: <?php echo formatDate($user['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-briefcase"></i> Recent Jobs</h2>
                                    <a href="manage-jobs.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_jobs)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-briefcase"></i>
                                            <p>No jobs found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="jobs-list">
                                            <?php foreach ($recent_jobs as $job): ?>
                                                <div class="job-item dashboard-job">
                                                    <div class="job-info">
                                                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                                        <p class="job-employer">
                                                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                                                            <span class="job-username">(<?php echo htmlspecialchars($job['employer_username']); ?>)</span>
                                                        </p>
                                                        <div class="job-meta">
                                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                                            <span><i class="fas fa-calendar"></i> Posted on <?php echo formatDate($job['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="job-status <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                                        <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
</body>
</html>