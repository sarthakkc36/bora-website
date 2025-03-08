<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Check if job ID is provided
if (!isset($_GET['job_id'])) {
    flashMessage("No job specified", "danger");
    redirect('manage-jobs.php');
}

$job_id = (int)$_GET['job_id'];

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
    $application_id = (int)$_POST['application_id'];
    $status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE job_applications SET status = :status WHERE id = :application_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':application_id', $application_id);
        $stmt->execute();
        
        flashMessage("Application status updated successfully", "success");
        redirect('view-applications.php?job_id=' . $job_id);
    } catch (PDOException $e) {
        error_log("Error updating application status: " . $e->getMessage());
        flashMessage("An error occurred while updating the application status", "danger");
    }
}

// Get job details
try {
    $stmt = $pdo->prepare("SELECT j.*, u.first_name as employer_first_name, u.last_name as employer_last_name, u.email as employer_email 
                           FROM jobs j 
                           LEFT JOIN users u ON j.user_id = u.id 
                           WHERE j.id = :job_id");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        flashMessage("Job not found", "danger");
        redirect('manage-jobs.php');
    }
    
    $job = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching job details: " . $e->getMessage());
    flashMessage("An error occurred while fetching job details", "danger");
    redirect('manage-jobs.php');
}

// Get applications for this job
try {
    $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name, u.email, u.phone 
                           FROM job_applications a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.job_id = :job_id 
                           ORDER BY a.created_at DESC");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching applications: " . $e->getMessage());
    $applications = [];
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
    <title>View Applications - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/updated-styles.css">
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
            <h1>Applications for <?php echo htmlspecialchars($job['title']); ?></h1>
            <p>View and manage applications for this job posting</p>
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
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-briefcase"></i> Job Details</h2>
                            <a href="manage-jobs.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
                        </div>
                        
                        <div class="content-body">
                            <div class="job-details-info">
                                <div class="detail-group">
                                    <span class="detail-label">Job Title:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($job['title']); ?></span>
                                </div>
                                
                                <div class="detail-group">
                                    <span class="detail-label">Company:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                </div>
                                
                                <div class="detail-group">
                                    <span class="detail-label">Employer:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($job['employer_first_name'] . ' ' . $job['employer_last_name']); ?> (<?php echo htmlspecialchars($job['employer_email']); ?>)</span>
                                </div>
                                
                                <div class="detail-group">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($job['location']); ?></span>
                                </div>
                                
                                <div class="detail-group">
                                    <span class="detail-label">Posted On:</span>
                                    <span class="detail-value"><?php echo formatDate($job['created_at']); ?></span>
                                </div>
                                
                                <div class="detail-group">
                                    <span class="detail-label">Status:</span>
                                    <span class="status-badge <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-users"></i> Applications (<?php echo count($applications); ?>)</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($applications)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-file-alt"></i>
                                    <p>No applications have been submitted for this job yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Applicant</th>
                                                <th>Contact</th>
                                                <th>Resume</th>
                                                <th>Applied On</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($applications as $application): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
                                                    <td>
                                                        <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>"><?php echo htmlspecialchars($application['email']); ?></a><br>
                                                        <?php if (!empty($application['phone'])): ?>
                                                            <small><?php echo htmlspecialchars($application['phone']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="../<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank" class="download-link">
                                                            <i class="fas fa-download"></i> Download Resume
                                                        </a>
                                                    </td>
                                                    <td><?php echo formatDate($application['created_at']); ?></td>
                                                    <td>
                                                        <span class="application-status <?php echo $application['status']; ?>">
                                                            <?php echo ucfirst($application['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="actions">
                                                        <a href="#" class="action-btn view status-change-btn" data-id="<?php echo $application['id']; ?>" title="Change Status">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </a>
                                                        
                                                        <?php if (!empty($application['cover_letter'])): ?>
                                                            <a href="#" class="action-btn view view-cover-letter" data-id="<?php echo $application['id']; ?>" title="View Cover Letter">
                                                                <i class="fas fa-file-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Status Change Form (Hidden) -->
                                                <tr class="status-change-form" id="status-form-<?php echo $application['id']; ?>" style="display: none;">
                                                    <td colspan="6">
                                                        <form method="POST" action="view-applications.php?job_id=<?php echo $job_id; ?>">
                                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                            <div class="form-row">
                                                                <div class="form-group" style="flex: 1;">
                                                                    <label for="status-<?php echo $application['id']; ?>">Update Application Status:</label>
                                                                    <select id="status-<?php echo $application['id']; ?>" name="status" class="form-control">
                                                                        <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="reviewed" <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                                        <option value="interviewed" <?php echo $application['status'] === 'interviewed' ? 'selected' : ''; ?>>Interviewed</option>
                                                        <option value="offered" <?php echo $application['status'] === 'offered' ? 'selected' : ''; ?>>Offered</option>
                                                        <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                </div>
                                                <div style="margin-left: 10px; display: flex; align-items: flex-end;">
                                                    <button type="submit" class="btn-primary">Update</button>
                                                    <button type="button" class="btn-secondary cancel-status-change" style="margin-left: 10px;">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Cover Letter Modal (Hidden) -->
                                <?php if (!empty($application['cover_letter'])): ?>
                                <tr class="cover-letter-content" id="cover-letter-<?php echo $application['id']; ?>" style="display: none;">
                                    <td colspan="6">
                                        <div class="modal-content">
                                            <h3>Cover Letter</h3>
                                            <div class="cover-letter-text">
                                                <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                                            </div>
                                            <button type="button" class="btn-secondary close-cover-letter" style="margin-top: 15px;">Close</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
</section>

<?php include '../includes/footer.php'; ?>

<script src="../js/script.js"></script>
<script>
    // Toggle status change form
    document.querySelectorAll('.status-change-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            document.querySelectorAll('.status-change-form, .cover-letter-content').forEach(form => {
                form.style.display = 'none';
            });
            document.getElementById('status-form-' + id).style.display = 'table-row';
        });
    });
    
    // Cancel status change
    document.querySelectorAll('.cancel-status-change').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.status-change-form').style.display = 'none';
        });
    });
    
    // Toggle cover letter view
    document.querySelectorAll('.view-cover-letter').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            document.querySelectorAll('.status-change-form, .cover-letter-content').forEach(form => {
                form.style.display = 'none';
            });
            document.getElementById('cover-letter-' + id).style.display = 'table-row';
        });
    });
    
    // Close cover letter
    document.querySelectorAll('.close-cover-letter').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.cover-letter-content').style.display = 'none';
        });
    });
</script>

<style>
    .job-details-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .detail-group {
        flex: 1;
        min-width: 200px;
    }
    
    .detail-label {
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
        color: #666;
    }
    
    .detail-value {
        color: #333;
    }
    
    .download-link {
        display: inline-block;
        padding: 5px 10px;
        background-color: #f0f7ff;
        color: #0066cc;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .download-link:hover {
        background-color: #0066cc;
        color: white;
    }
    
    .modal-content {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .cover-letter-text {
        background-color: white;
        padding: 15px;
        border-radius: 4px;
        border-left: 3px solid #0066cc;
        margin-top: 10px;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
</body>
</html>