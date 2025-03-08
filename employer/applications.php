<?php
require_once '../config.php';

// Check if user is logged in and is an employer
if (!isLoggedIn() || !isEmployer()) {
    flashMessage("You must be logged in as an employer to access this page", "danger");
    redirect('../login.php');
}

// Get selected job filter (if any)
$job_filter = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Get selected status filter (if any)
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
    $application_id = (int)$_POST['application_id'];
    $status = sanitizeInput($_POST['status']);
    
    try {
        // Verify the application belongs to one of the employer's jobs
        $stmt = $pdo->prepare("SELECT a.id FROM job_applications a 
                              JOIN jobs j ON a.job_id = j.id 
                              WHERE a.id = :application_id AND j.user_id = :user_id");
        $stmt->bindParam(':application_id', $application_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            flashMessage("You don't have permission to update this application", "danger");
            redirect('applications.php');
        }
        
        // Update the status
        $stmt = $pdo->prepare("UPDATE job_applications SET status = :status WHERE id = :application_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':application_id', $application_id);
        $stmt->execute();
        
        flashMessage("Application status updated successfully", "success");
        
        // Redirect back to the same page with filters preserved
        $redirect_url = 'applications.php';
        if ($job_filter) {
            $redirect_url .= '?job_id=' . $job_filter;
            if ($status_filter) {
                $redirect_url .= '&status=' . $status_filter;
            }
        } elseif ($status_filter) {
            $redirect_url .= '?status=' . $status_filter;
        }
        
        redirect($redirect_url);
        
    } catch (PDOException $e) {
        error_log("Error updating application status: " . $e->getMessage());
        flashMessage("An error occurred while updating the application status", "danger");
    }
}

// Get employer's jobs for filtering
try {
    $stmt = $pdo->prepare("SELECT id, title FROM jobs WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $employer_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching employer jobs: " . $e->getMessage());
    $employer_jobs = [];
}

// Build query based on filters
$query = "SELECT a.*, j.title as job_title, u.first_name, u.last_name, u.email, u.phone 
         FROM job_applications a 
         JOIN jobs j ON a.job_id = j.id 
         JOIN users u ON a.user_id = u.id 
         WHERE j.user_id = :user_id";
$params = [':user_id' => $_SESSION['user_id']];

if ($job_filter) {
    $query .= " AND j.id = :job_id";
    $params[':job_id'] = $job_filter;
}

if ($status_filter) {
    $query .= " AND a.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY a.created_at DESC";

// Get applications based on filters
try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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

// Count applications by status
$status_counts = [
    'all' => 0,
    'pending' => 0,
    'reviewed' => 0,
    'interviewed' => 0,
    'offered' => 0,
    'rejected' => 0
];

foreach ($applications as $app) {
    $status_counts['all']++;
    $status_counts[$app['status']]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/updated-styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Applications</h1>
            <p>Review and respond to job applications</p>
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
                            <h2><i class="fas fa-file-alt"></i> Job Applications</h2>
                        </div>
                        
                        <div class="content-body">
                            <!-- Filters -->
                            <div class="application-filters">
                                <div class="filter-row">
                                    <div class="filter-group">
                                        <label for="job-filter">Filter by Job:</label>
                                        <select id="job-filter" class="form-control" onchange="applyFilters()">
                                            <option value="">All Jobs</option>
                                            <?php foreach ($employer_jobs as $job): ?>
                                                <option value="<?php echo $job['id']; ?>" <?php echo $job_filter == $job['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($job['title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="filter-group">
                                        <label for="status-filter">Filter by Status:</label>
                                        <select id="status-filter" class="form-control" onchange="applyFilters()">
                                            <option value="">All Statuses</option>
                                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="reviewed" <?php echo $status_filter === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                            <option value="interviewed" <?php echo $status_filter === 'interviewed' ? 'selected' : ''; ?>>Interviewed</option>
                                            <option value="offered" <?php echo $status_filter === 'offered' ? 'selected' : ''; ?>>Offered</option>
                                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="status-tabs">
                                    <a href="applications.php" class="status-tab <?php echo (!$status_filter) ? 'active' : ''; ?>">
                                        All <span class="count"><?php echo $status_counts['all']; ?></span>
                                    </a>
                                    <a href="applications.php?status=pending<?php echo $job_filter ? '&job_id='.$job_filter : ''; ?>" class="status-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                                        Pending <span class="count"><?php echo $status_counts['pending']; ?></span>
                                    </a>
                                    <a href="applications.php?status=reviewed<?php echo $job_filter ? '&job_id='.$job_filter : ''; ?>" class="status-tab <?php echo $status_filter === 'reviewed' ? 'active' : ''; ?>">
                                        Reviewed <span class="count"><?php echo $status_counts['reviewed']; ?></span>
                                    </a>
                                    <a href="applications.php?status=interviewed<?php echo $job_filter ? '&job_id='.$job_filter : ''; ?>" class="status-tab <?php echo $status_filter === 'interviewed' ? 'active' : ''; ?>">
                                        Interviewed <span class="count"><?php echo $status_counts['interviewed']; ?></span>
                                    </a>
                                    <a href="applications.php?status=offered<?php echo $job_filter ? '&job_id='.$job_filter : ''; ?>" class="status-tab <?php echo $status_filter === 'offered' ? 'active' : ''; ?>">
                                        Offered <span class="count"><?php echo $status_counts['offered']; ?></span>
                                    </a>
                                    <a href="applications.php?status=rejected<?php echo $job_filter ? '&job_id='.$job_filter : ''; ?>" class="status-tab <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                                        Rejected <span class="count"><?php echo $status_counts['rejected']; ?></span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Applications List -->
                            <?php if (empty($applications)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-file-alt"></i>
                                    <p>No applications found matching your filters.</p>
                                    <?php if ($job_filter || $status_filter): ?>
                                        <a href="applications.php" class="btn-secondary">Clear Filters</a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="applications-list">
                                    <?php foreach ($applications as $application): ?>
                                        <div class="application-item">
                                            <div class="application-header">
                                                <div class="applicant-info">
                                                    <div class="applicant-avatar">
                                                        <i class="fas fa-user-circle"></i>
                                                    </div>
                                                    <div class="applicant-details">
                                                        <h3><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h3>
                                                        <div class="applicant-meta">
                                                            <span><i class="fas fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>"><?php echo htmlspecialchars($application['email']); ?></a></span>
                                                            <?php if (!empty($application['phone'])): ?>
                                                                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($application['phone']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="application-meta">
                                                    <div class="applied-for">
                                                        <span class="meta-label">Applied for:</span>
                                                        <span class="meta-value"><?php echo htmlspecialchars($application['job_title']); ?></span>
                                                    </div>
                                                    <div class="applied-date">
                                                        <span class="meta-label">Date:</span>
                                                        <span class="meta-value"><?php echo formatDate($application['created_at']); ?></span>
                                                    </div>
                                                    <div class="application-status">
                                                        <span class="status-badge <?php echo $application['status']; ?>">
                                                            <?php echo ucfirst($application['status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="application-actions">
                                                    <button class="action-btn view toggle-application" data-id="<?php echo $application['id']; ?>">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="application-content" id="application-content-<?php echo $application['id']; ?>">
                                                <div class="content-section">
                                                    <div class="application-resume">
                                                        <h4><i class="fas fa-file-alt"></i> Resume</h4>
                                                        <a href="../<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank" class="download-link">
                                                            <i class="fas fa-download"></i> Download Resume
                                                        </a>
                                                    </div>
                                                    
                                                    <?php if (!empty($application['cover_letter'])): ?>
                                                        <div class="application-cover-letter">
                                                            <h4><i class="fas fa-envelope-open-text"></i> Cover Letter</h4>
                                                            <div class="cover-letter-text">
                                                                <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="status-update-section">
                                                    <h4>Update Application Status</h4>
                                                    <form method="POST" action="applications.php<?php echo ($job_filter || $status_filter) ? '?' . http_build_query(array_filter(['job_id' => $job_filter, 'status' => $status_filter])) : ''; ?>">
                                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                        <div class="form-row">
                                                            <div class="form-group" style="flex: 1;">
                                                                <select name="status" class="form-control">
                                                                    <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="reviewed" <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                                    <option value="interviewed" <?php echo $application['status'] === 'interviewed' ? 'selected' : ''; ?>>Interviewed</option>
                                                                    <option value="offered" <?php echo $application['status'] === 'offered' ? 'selected' : ''; ?>>Offered</option>
                                                                    <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                                </select>
                                                            </div>
                                                            <button type="submit" class="btn-primary">Update Status</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                
                                                <div class="applicant-actions">
                                                    <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>" class="btn-secondary">
                                                        <i class="fas fa-envelope"></i> Email Applicant
                                                    </a>
                                                </div>
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
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        // Filter functionality
        function applyFilters() {
            const jobFilter = document.getElementById('job-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            
            let url = 'applications.php';
            const params = [];
            
            if (jobFilter) {
                params.push('job_id=' + jobFilter);
            }
            
            if (statusFilter) {
                params.push('status=' + statusFilter);
            }
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        }
        
        // Toggle application content
        document.querySelectorAll('.toggle-application').forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-id');
                const content = document.getElementById('application-content-' + applicationId);
                const icon = this.querySelector('i');
                
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    content.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
        });
    </script>
    
    <style>
    .application-filters {
        margin-bottom: 20px;
    }
    
    .filter-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .filter-group {
        flex: 1;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 5px;
        color: #666;
        font-weight: 600;
    }
    
    .status-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .status-tab {
        padding: 8px 16px;
        border-radius: 20px;
        background-color: #f5f5f5;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .status-tab .count {
        background-color: #e0e0e0;
        color: #666;
        font-size: 12px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 5px;
    }
    
    .status-tab.active {
        background-color: #0066cc;
        color: white;
    }
    
    .status-tab.active .count {
        background-color: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    .status-tab:hover {
        background-color: #e0e0e0;
        transform: translateY(-2px);
    }
    
    .status-tab.active:hover {
        background-color: #0052a3;
    }
    
    .applications-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .application-item {
        background-color: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .application-item:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .application-header {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .applicant-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 2;
        min-width: 250px;
    }
    
    .applicant-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #f0f7ff;
        color: #0066cc;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
    }
    
    .applicant-details h3 {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: #333;
    }
    
    .applicant-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 14px;
        color: #666;
    }
    
    .applicant-meta i {
        color: #0066cc;
        margin-right: 5px;
    }
    
    .application-meta {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 2;
        min-width: 200px;
    }
    
    .meta-label {
        color: #666;
        font-size: 13px;
    }
    
    .meta-value {
        font-weight: 600;
        color: #333;
    }
    
    .application-content {
        padding: 0 20px 20px;
        border-top: 1px solid #eee;
        display: none;
    }
    
    .content-section {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 20px;
        padding-top: 20px;
    }
    
    .application-resume, .application-cover-letter {
        flex: 1;
        min-width: 250px;
    }
    
    .application-resume h4, .application-cover-letter h4, .status-update-section h4 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #333;
        display: flex;
        align-items: center;
    }
    
    .application-resume h4 i, .application-cover-letter h4 i, .status-update-section h4 i {
        color: #0066cc;
        margin-right: 8px;
    }
    
    .download-link {
        display: inline-block;
        padding: 8px 15px;
        background-color: #f0f7ff;
        color: #0066cc;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .download-link:hover {
        background-color: #0066cc;
        color: white;
    }
    
    .cover-letter-text {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
        max-height: 200px;
        overflow-y: auto;
        line-height: 1.6;
    }
    
    .status-update-section {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-end;
    }
    
    .applicant-actions {
        display: flex;
        gap: 15px;
    }
    
    .btn-primary, .btn-secondary {
        padding: 8px 16px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    
    .btn-primary {
        background-color: #0066cc;
        color: white;
    }
    
    .btn-secondary {
        background-color: #f0f7ff;
        color: #0066cc;
    }
    
    .btn-primary:hover, .btn-secondary:hover {
        transform: translateY(-2px);
    }
    
    .btn-primary:hover {
        background-color: #0052a3;
    }
    
    .btn-secondary:hover {
        background-color: #dae8fa;
    }
    
    .btn-primary i, .btn-secondary i {
        margin-right: 5px;
    }
    
    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
            gap: 10px;
        }
        
        .application-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .application-meta {
            width: 100%;
            margin-top: 10px;
        }
        
        .application-actions {
            align-self: flex-end;
            margin-top: -30px;
        }
    }
    </style>
</body>
</html>