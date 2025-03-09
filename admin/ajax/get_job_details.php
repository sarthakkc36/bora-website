<?php
require_once '../../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "Unauthorized access";
    exit;
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    echo "No job ID provided";
    exit;
}

$job_id = (int)$_GET['id'];

// Get job details
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "Job not found";
        exit;
    }
    
    $job = $stmt->fetch();
} catch (PDOException $e) {
    echo "Error retrieving job details";
    error_log("Error getting job details: " . $e->getMessage());
    exit;
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>

<div class="job-details-view">
    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
    
    <div class="detail-section">
        <div class="detail-group">
            <span class="detail-label">Location:</span>
            <span class="detail-value"><?php echo htmlspecialchars($job['location']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Job Type:</span>
            <span class="detail-value"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Experience Level:</span>
            <span class="detail-value"><?php echo ucfirst($job['experience_level']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Salary Range:</span>
            <span class="detail-value">
                <?php 
                if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                    echo '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                } elseif (!empty($job['salary_min'])) {
                    echo 'From $' . number_format($job['salary_min']);
                } elseif (!empty($job['salary_max'])) {
                    echo 'Up to $' . number_format($job['salary_max']);
                } else {
                    echo 'Not specified';
                }
                ?>
            </span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Posted Date:</span>
            <span class="detail-value"><?php echo formatDate($job['created_at']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Status:</span>
            <span class="detail-value"><?php echo getJobStatusLabel($job['approval_status']); ?></span>
        </div>
    </div>
    
    <div class="submitter-info">
        <h4>Submitted By:</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($job['submitter_name'] ?? 'N/A'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($job['submitter_email'] ?? 'N/A'); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($job['submitter_phone'] ?? 'N/A'); ?></p>
    </div>
    
    <div class="content-section">
        <h4>Job Description</h4>
        <div class="content-text">
            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
        </div>
    </div>
    
    <div class="content-section">
        <h4>Job Requirements</h4>
        <div class="content-text">
            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
        </div>
    </div>
    
    <?php if (!empty($job['admin_notes'])): ?>
        <div class="content-section">
            <h4>Admin Notes</h4>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($job['admin_notes'])); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .job-details-view {
        font-size: 15px;
    }
    
    .job-details-view h3 {
        margin-bottom: 5px;
        color: #333;
    }
    
    .company-name {
        font-size: 18px;
        color: #0066cc;
        margin-bottom: 20px;
    }
    
    .detail-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }
    
    .detail-group {
        margin-bottom: 8px;
    }
    
    .detail-label {
        font-weight: 600;
        color: #666;
        display: block;
        margin-bottom: 3px;
    }
    
    .detail-value {
        color: #333;
    }
    
    .content-section {
        margin-top: 20px;
    }
    
    .content-section h4 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
    
    .content-text {
        line-height: 1.6;
        color: #444;
    }
</style>