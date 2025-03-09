<?php
require_once '../config.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isJobSeeker()) {
    flashMessage("You must be logged in as a job seeker to access this page", "danger");
    redirect('../login.php');
}

// Handle job unsave
if (isset($_POST['unsave_job']) && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        flashMessage("Job removed from saved jobs", "success");
        redirect('saved-jobs.php');
    } catch (PDOException $e) {
        error_log("Error removing saved job: " . $e->getMessage());
        flashMessage("An error occurred while removing the job", "danger");
    }
}

// Get saved jobs
try {
    $stmt = $pdo->prepare("SELECT j.*, sj.created_at as saved_date 
                          FROM saved_jobs sj 
                          JOIN jobs j ON sj.job_id = j.id 
                          WHERE sj.user_id = :user_id 
                          ORDER BY sj.created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $saved_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching saved jobs: " . $e->getMessage());
    flashMessage("An error occurred while retrieving your saved jobs", "danger");
    $saved_jobs = [];
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
    <title>Saved Jobs - B&H Employment & Consultancy Inc</title>
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
<style>
.job-card {
    position: relative;
    overflow: hidden;
}

.remove-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: #f8f9fa;
    color: #dc3545;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #eee;
    z-index: 10;
}

.remove-btn:hover {
    background-color: #dc3545;
    color: white;
}

.saved-date {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.saved-date i {
    margin-right: 5px;
    color: #0066cc;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.apply-btn, .view-btn {
    flex: 1;
    text-align: center;
}

/* Animation for removing jobs */
.job-card.removing {
    animation: slideOut 0.5s forwards;
}

@keyframes slideOut {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Saved Jobs</h1>
            <p>Jobs you've bookmarked for later consideration</p>
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
                    
                    <?php if (empty($saved_jobs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bookmark fa-3x"></i>
                            <h3>No saved jobs found</h3>
                            <p>You haven't saved any jobs yet. Browse jobs and click the bookmark icon to save them for later.</p>
                            <a href="../jobs.php" class="btn-primary">Browse Jobs</a>
                        </div>
                    <?php else: ?>
                        <div class="jobs-grid">
                            <?php foreach ($saved_jobs as $job): ?>
                                <div class="job-card" id="job-card-<?php echo $job['id']; ?>">
                                    <form method="POST" class="remove-form">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" name="unsave_job" class="remove-btn" title="Remove from saved jobs" onclick="removeJob(<?php echo $job['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    
                                    <div class="saved-date">
                                        <i class="fas fa-clock"></i> Saved on <?php echo formatDate($job['saved_date']); ?>
                                    </div>
                                    
                                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                    
                                    <div class="job-details">
                                        <span class="job-detail"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                        <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                                            <span class="job-detail"><i class="fas fa-dollar-sign"></i> 
                                            <?php 
                                                if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                                    echo '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                                                } elseif (!empty($job['salary_min'])) {
                                                    echo 'From $' . number_format($job['salary_min']);
                                                } elseif (!empty($job['salary_max'])) {
                                                    echo 'Up to $' . number_format($job['salary_max']);
                                                }
                                            ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="job-detail"><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                    </div>
                                    
                                    <p class="job-description"><?php echo substr(strip_tags($job['description']), 0, 150) . '...'; ?></p>
                                    
                                    <div class="action-buttons">
                                        <a href="../job-details.php?id=<?php echo $job['id']; ?>" class="view-btn btn-secondary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <a href="../job-details.php?id=<?php echo $job['id']; ?>#apply-section" class="apply-btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Apply Now
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Function to animate removal of job card
        function removeJob(jobId) {
            // Prevent default form submission
            event.preventDefault();
            
            // Add removing animation class
            const jobCard = document.getElementById('job-card-' + jobId);
            jobCard.classList.add('removing');
            
            // Submit the form after animation completes
            setTimeout(() => {
                document.querySelector('#job-card-' + jobId + ' .remove-form').submit();
            }, 500);
        }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>