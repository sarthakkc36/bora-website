<?php
require_once '../config.php';

// Check if user is logged in and is an employer
if (!isLoggedIn() || !isEmployer()) {
    flashMessage("You must be logged in as an employer to access this page", "danger");
    redirect('../login.php');
}

// Get employer information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $employer = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching employer information: " . $e->getMessage());
    $employer = [];
}

// Get employer job statistics
try {
    // Total jobs
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_jobs FROM jobs WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $total_jobs = $stmt->fetch()['total_jobs'];
    
    // Active jobs
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_jobs FROM jobs WHERE user_id = :user_id AND is_active = 1");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $active_jobs = $stmt->fetch()['active_jobs'];
    
    // Total applications
    $stmt = $pdo->prepare("SELECT COUNT(a.id) as total_applications 
                          FROM job_applications a 
                          JOIN jobs j ON a.job_id = j.id 
                          WHERE j.user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $total_applications = $stmt->fetch()['total_applications'];
    
    // Total views
    $stmt = $pdo->prepare("SELECT SUM(views) as total_views FROM jobs WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch();
    $total_views = $result['total_views'] ? $result['total_views'] : 0;
    
    // Recent applications
    $stmt = $pdo->prepare("SELECT a.*, j.title as job_title, u.first_name, u.last_name, u.email
                          FROM job_applications a
                          JOIN jobs j ON a.job_id = j.id
                          JOIN users u ON a.user_id = u.id
                          WHERE j.user_id = :user_id
                          ORDER BY a.created_at DESC
                          LIMIT 5");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $recent_applications = $stmt->fetchAll();
    
    // Recent jobs
    $stmt = $pdo->prepare("SELECT * FROM jobs 
                          WHERE user_id = :user_id 
                          ORDER BY created_at DESC 
                          LIMIT 5");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $recent_jobs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching employer statistics: " . $e->getMessage());
    $total_jobs = $active_jobs = $total_applications = $total_views = 0;
    $recent_applications = $recent_jobs = [];
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
    <title>Employer Dashboard - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Employer Dashboard</h1>
            <p>Manage your job postings and applications</p>
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
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_jobs); ?></h3>
                                <p>Total Jobs</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon active">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($active_jobs); ?></h3>
                                <p>Active Jobs</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_applications); ?></h3>
                                <p>Applications</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($total_views); ?></h3>
                                <p>Job Views</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-actions">
                        <a href="post-job.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-info">
                                <h3>Post a New Job</h3>
                                <p>Create a job listing to find qualified candidates</p>
                            </div>
                        </a>
                        
                        <a href="manage-jobs.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="action-info">
                                <h3>Manage Jobs</h3>
                                <p>Edit, delete or deactivate your job listings</p>
                            </div>
                        </a>
                        
                        <a href="applications.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-info">
                                <h3>Review Applications</h3>
                                <p>Manage and respond to job applications</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="dashboard-row">
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-briefcase"></i> Recent Job Postings</h2>
                                    <a href="manage-jobs.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_jobs)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-briefcase"></i>
                                            <p>You haven't posted any jobs yet.</p>
                                            <a href="post-job.php" class="btn-small">Post a Job</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="jobs-list">
                                            <?php foreach ($recent_jobs as $job): ?>
                                                <div class="job-item dashboard-job">
                                                    <div class="job-info">
                                                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                                        <div class="job-meta">
                                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                                            <span><i class="fas fa-calendar"></i> Posted on <?php echo formatDate($job['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="job-stats">
                                                        <div class="job-stat">
                                                            <span class="stat-value"><?php echo number_format($job['views']); ?></span>
                                                            <span class="stat-label">Views</span>
                                                        </div>
                                                        <div class="job-stat">
                                                            <span class="stat-value"><?php echo number_format($job['applications']); ?></span>
                                                            <span class="stat-label">Applications</span>
                                                        </div>
                                                        <div class="job-status <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                                            <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
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
                                    <h2><i class="fas fa-file-alt"></i> Recent Applications</h2>
                                    <a href="applications.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_applications)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-file-alt"></i>
                                            <p>No applications have been submitted yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="applications-list">
                                            <?php foreach ($recent_applications as $application): ?>
                                                <div class="application-item">
                                                    <div class="applicant-info">
                                                        <div class="applicant-avatar">
                                                            <i class="fas fa-user-circle"></i>
                                                        </div>
                                                        <div class="applicant-details">
                                                            <h4><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h4>
                                                            <p>Applied for: <strong><?php echo htmlspecialchars($application['job_title']); ?></strong></p>
                                                            <span class="application-date"><?php echo formatDate($application['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="application-status <?php echo $application['status']; ?>">
                                                        <?php echo ucfirst($application['status']); ?>
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