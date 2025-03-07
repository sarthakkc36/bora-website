<?php
require_once 'config.php';

// Get jobs from database
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE is_active = 1 ORDER BY created_at DESC");
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
            <div class="jobs-header">
                <div class="jobs-count">
                    <strong><?php echo count($jobs); ?></strong> jobs found
                </div>
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
                                    <img src="/api/placeholder/80/80" alt="<?php echo htmlspecialchars($job['company_name']); ?> Logo">
                                </div>
                                <div class="job-info">
                                    <h3 class="job-title">
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                    <div class="job-tags">
                                        <span class="job-tag"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                        <span class="job-tag"><?php echo ucfirst($job['experience_level']) . ' Level'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="job-features">
                                <div class="job-feature">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                                </div>
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

    <?php include 'includes/footer.php'; ?>
</body>
</html>