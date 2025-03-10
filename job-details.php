<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the intended destination URL in the session
    $_SESSION['redirect_after_login'] = 'jobs.php';
    
    // Redirect to login page with message
    flashMessage("Please log in to view job listings", "info");
    redirect('login.php');
    exit;
}

// Get jobs from database - show only approved jobs
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE is_active = 1 AND approval_status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    $jobs = [];
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
    <title>Jobs - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Find Your Perfect Job</h1>
            <p>Explore job opportunities from top employers</p>
        </div>
    </section>

    <section class="jobs-list">
        <div class="container">
            <?php displayFlashMessage(); ?>
            
            <div class="jobs-header">
                <div class="jobs-count">
                    <strong><?php echo count($jobs); ?></strong> jobs found
                </div>
            </div>
            
            <div class="job-confidentiality-notice">
                <p><i class="fas fa-lock"></i> <strong>Confidential Hiring Process:</strong> To maintain our clients' privacy, company names and locations are only revealed after initial application screening.</p>
            </div>
            
            <div class="jobs-grid">
                <?php if (empty($jobs)): ?>
                    <div class="no-jobs-found">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>No jobs found</h3>
                        <p>Check back later for new job postings.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-header">
                                <div class="job-company-logo">
                                    <i class="fas fa-briefcase" style="font-size: 32px; color: #0066cc;"></i>
                                </div>
                                <div class="job-info">
                                    <h3 class="job-title">
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="job-company">B&H Employment & Consultancy Client</p>
                                    <div class="job-tags">
                                        <span class="job-tag"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                        <span class="job-tag"><?php echo ucfirst($job['experience_level']) . ' Level'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="job-features">
                                <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                    <div class="job-feature">
                                        <i class="fas fa-dollar-sign"></i>
                                        <?php 
                                            if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                                echo '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                                            } elseif (!empty($job['salary_min'])) {
                                                echo 'From $' . number_format($job['salary_min']);
                                            } elseif (!empty($job['salary_max'])) {
                                                echo 'Up to $' . number_format($job['salary_max']);
                                            }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <div class="job-feature">
                                    <i class="fas fa-calendar"></i> Posted <?php echo formatDate($job['created_at']); ?>
                                </div>
                            </div>
                            <div class="job-description">
                                <?php echo substr(strip_tags($job['description']), 0, 200) . '...'; ?>
                            </div>
                            <div class="job-actions">
                                <a href="job-details.php?id=<?php echo $job['id']; ?>" class="apply-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <style>
        .job-confidentiality-notice {
            background-color: #f0f7ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .job-confidentiality-notice p {
            margin: 0;
            color: #333;
        }
        
        .job-confidentiality-notice i {
            color: #0066cc;
            margin-right: 5px;
        }
    </style>
    
    <script src="js/script.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>