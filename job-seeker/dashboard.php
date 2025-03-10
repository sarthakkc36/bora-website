<?php
require_once '../config.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isJobSeeker()) {
    flashMessage("You must be logged in as a job seeker to access this page", "danger");
    redirect('../login.php');
    exit;
}

// Check verification status - redirect to verification page if not verified
if (!isVerified()) {
    flashMessage("Your account requires verification before you can access your dashboard", "warning");
    redirect('../verification-pending.php');
    exit;
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching user information: " . $e->getMessage());
    $user = [];
}

// Get job application statistics
try {
    // Get total applications by user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_applications FROM job_applications WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $applications_count = $stmt->fetch()['total_applications'];
    
    // Get applications by status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM job_applications WHERE user_id = :user_id GROUP BY status");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $applications_by_status = $stmt->fetchAll();
    
    // Create status counts array
    $status_counts = [
        'pending' => 0,
        'reviewed' => 0,
        'interviewed' => 0,
        'offered' => 0,
        'rejected' => 0
    ];
    
    foreach ($applications_by_status as $status) {
        $status_counts[$status['status']] = $status['count'];
    }
    
    // Get saved jobs count
    $stmt = $pdo->prepare("SELECT COUNT(*) as saved_jobs FROM saved_jobs WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $saved_jobs_count = $stmt->fetch()['saved_jobs'];
    
} catch (PDOException $e) {
    error_log("Error fetching application statistics: " . $e->getMessage());
    $applications_count = 0;
    $status_counts = [
        'pending' => 0,
        'reviewed' => 0,
        'interviewed' => 0,
        'offered' => 0,
        'rejected' => 0
    ];
    $saved_jobs_count = 0;
}

// Get recent applications
try {
    $stmt = $pdo->prepare("SELECT ja.*, j.title, j.company_name, j.location
                          FROM job_applications ja
                          JOIN jobs j ON ja.job_id = j.id
                          WHERE ja.user_id = :user_id
                          ORDER BY ja.created_at DESC
                          LIMIT 5");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $recent_applications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching recent applications: " . $e->getMessage());
    $recent_applications = [];
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
    <title>Dashboard - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
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
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Job Seeker Dashboard</h1>
            <p>Manage your job applications and profile</p>
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
                    
                    <div class="welcome-section">
                        <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                        <p>Here's a summary of your job search activity.</p>
                    </div>
                    
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($applications_count); ?></h3>
                                <p>Total Applications</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($status_counts['pending']); ?></h3>
                                <p>Pending Applications</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($status_counts['interviewed']); ?></h3>
                                <p>Interviews</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon active">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($status_counts['offered']); ?></h3>
                                <p>Job Offers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-bookmark"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($saved_jobs_count); ?></h3>
                                <p>Saved Jobs</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-row">
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
                                            <p>You haven't applied to any jobs yet.</p>
                                            <a href="../jobs.php" class="btn-primary">Browse Jobs</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="applications-list">
                                            <?php foreach ($recent_applications as $application): ?>
                                                <div class="application-item">
                                                    <div class="job-info">
                                                        <h3><?php echo htmlspecialchars($application['title']); ?></h3>
                                                        <p class="job-company"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                                        <div class="job-meta">
                                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($application['location']); ?></span>
                                                            <span><i class="fas fa-calendar"></i> Applied on <?php echo formatDate($application['created_at']); ?></span>
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
                        
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-chart-pie"></i> Application Status</h2>
                                </div>
                                
                                <div class="content-body">
                                    <?php if ($applications_count == 0): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-chart-pie"></i>
                                            <p>No application data to display yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-chart">
                                            <div class="status-item">
                                                <div class="status-bar">
                                                    <div class="status-fill pending" style="width: <?php echo ($applications_count > 0) ? ($status_counts['pending'] / $applications_count * 100) : 0; ?>%"></div>
                                                </div>
                                                <div class="status-label">
                                                    <span class="status-badge pending">Pending</span>
                                                    <span class="status-count"><?php echo $status_counts['pending']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="status-item">
                                                <div class="status-bar">
                                                    <div class="status-fill reviewed" style="width: <?php echo ($applications_count > 0) ? ($status_counts['reviewed'] / $applications_count * 100) : 0; ?>%"></div>
                                                </div>
                                                <div class="status-label">
                                                    <span class="status-badge reviewed">Reviewed</span>
                                                    <span class="status-count"><?php echo $status_counts['reviewed']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="status-item">
                                                <div class="status-bar">
                                                    <div class="status-fill interviewed" style="width: <?php echo ($applications_count > 0) ? ($status_counts['interviewed'] / $applications_count * 100) : 0; ?>%"></div>
                                                </div>
                                                <div class="status-label">
                                                    <span class="status-badge interviewed">Interviewed</span>
                                                    <span class="status-count"><?php echo $status_counts['interviewed']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="status-item">
                                                <div class="status-bar">
                                                    <div class="status-fill offered" style="width: <?php echo ($applications_count > 0) ? ($status_counts['offered'] / $applications_count * 100) : 0; ?>%"></div>
                                                </div>
                                                <div class="status-label">
                                                    <span class="status-badge offered">Offered</span>
                                                    <span class="status-count"><?php echo $status_counts['offered']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="status-item">
                                                <div class="status-bar">
                                                    <div class="status-fill rejected" style="width: <?php echo ($applications_count > 0) ? ($status_counts['rejected'] / $applications_count * 100) : 0; ?>%"></div>
                                                </div>
                                                <div class="status-label">
                                                    <span class="status-badge rejected">Rejected</span>
                                                    <span class="status-count"><?php echo $status_counts['rejected']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-check-circle"></i> Quick Tips</h2>
                                </div>
                                
                                <div class="content-body">
                                    <ul class="quick-tips">
                                        <li>
                                            <i class="fas fa-file"></i>
                                            <div>
                                                <h4>Update Your Resume</h4>
                                                <p>Keep your resume up-to-date to increase your chances of landing interviews.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fas fa-search"></i>
                                            <div>
                                                <h4>Set Job Alerts</h4>
                                                <p>Create job alerts to get notified when new opportunities match your skills.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fas fa-star"></i>
                                            <div>
                                                <h4>Complete Your Profile</h4>
                                                <p>A complete profile increases your visibility to potential employers.</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Chart animations
        document.addEventListener('DOMContentLoaded', function() {
            const statusFills = document.querySelectorAll('.status-fill');
            
            statusFills.forEach(fill => {
                const originalWidth = fill.style.width;
                fill.style.width = '0';
                
                setTimeout(() => {
                    fill.style.transition = 'width 1s ease-out';
                    fill.style.width = originalWidth;
                }, 300);
            });
        });
    </script>
    <script src="../js/script.js"></script>
</body>
</html>