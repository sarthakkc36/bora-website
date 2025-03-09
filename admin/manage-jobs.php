<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Handle job actions (approve/reject/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'], $_POST['action'])) {
    $job_id = (int)$_POST['job_id'];
    $action = $_POST['action'];
    $admin_notes = isset($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : '';
    
    try {
        // Verify that the job exists
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            flashMessage("Job not found", "danger");
            redirect('manage-jobs.php');
        }
        
        $job = $stmt->fetch();
        
        // Perform the requested action
        switch ($action) {
            case 'approve':
                $stmt = $pdo->prepare("UPDATE jobs SET approval_status = 'approved', admin_notes = :admin_notes WHERE id = :job_id");
                $stmt->bindParam(':admin_notes', $admin_notes);
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                
                // Send email notification to submitter
                if (!empty($job['submitter_email'])) {
                    $subject = "Job Posting Approved: " . $job['title'];
                    $message = "<p>Dear " . htmlspecialchars($job['submitter_name']) . ",</p>";
                    $message .= "<p>Good news! Your job posting for <strong>" . htmlspecialchars($job['title']) . "</strong> at <strong>" . htmlspecialchars($job['company_name']) . "</strong> has been approved and is now live on our website.</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Admin Notes:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>Thank you for using our services!</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($job['submitter_email'], $subject, $message);
                }
                
                flashMessage("Job approved successfully", "success");
                break;
                
            case 'reject':
                $stmt = $pdo->prepare("UPDATE jobs SET approval_status = 'rejected', admin_notes = :admin_notes WHERE id = :job_id");
                $stmt->bindParam(':admin_notes', $admin_notes);
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                
                // Send email notification to submitter
                if (!empty($job['submitter_email'])) {
                    $subject = "Job Posting Not Approved: " . $job['title'];
                    $message = "<p>Dear " . htmlspecialchars($job['submitter_name']) . ",</p>";
                    $message .= "<p>Thank you for submitting your job posting for <strong>" . htmlspecialchars($job['title']) . "</strong> at <strong>" . htmlspecialchars($job['company_name']) . "</strong>.</p>";
                    $message .= "<p>After reviewing your submission, we are unable to approve it at this time.</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Reason:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>You are welcome to submit a revised job posting addressing the feedback provided.</p>";
                    $message .= "<p>If you have any questions, please contact our support team.</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($job['submitter_email'], $subject, $message);
                }
                
                flashMessage("Job rejected successfully", "success");
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = :job_id");
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                flashMessage("Job deleted successfully", "success");
                break;
                
            default:
                flashMessage("Invalid action", "danger");
        }
    } catch (PDOException $e) {
        error_log("Error performing job action: " . $e->getMessage());
        flashMessage("An error occurred. Please try again.", "danger");
    }
    
    redirect('manage-jobs.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query based on filters
$query = "SELECT * FROM jobs";
$params = [];

if (!empty($status_filter)) {
    $query .= " WHERE approval_status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY created_at DESC";

// Get all jobs with employer information
try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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
    <title>Manage Jobs - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .filter-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .filter-tab {
            padding: 8px 16px;
            margin-right: 8px;
            background-color: #f5f5f5;
            border-radius: 20px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .filter-tab:hover {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .filter-tab.active {
            background-color: #0066cc;
            color: white;
        }
        
        .job-actions .action-btn {
            margin-bottom: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .submitter-info {
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Jobs</h1>
            <p>Review, approve, and manage job postings</p>
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
                    
                    <div class="filter-tabs">
                        <a href="manage-jobs.php" class="filter-tab <?php echo empty($status_filter) ? 'active' : ''; ?>">All Jobs</a>
                        <a href="manage-jobs.php?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending Approval</a>
                        <a href="manage-jobs.php?status=approved" class="filter-tab <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">Approved</a>
                        <a href="manage-jobs.php?status=rejected" class="filter-tab <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-briefcase"></i> Job Postings</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($jobs)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-briefcase"></i>
                                    <p>No jobs found for the selected filter.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Submitted By</th>
                                                <th>Submission Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($jobs as $job): ?>
                                                <tr>
                                                    <td>
                                                        <a href="#" onclick="viewJobDetails(<?php echo $job['id']; ?>)">
                                                            <?php echo htmlspecialchars($job['title']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                                    <td>
                                                        <?php if (!empty($job['submitter_name'])): ?>
                                                            <?php echo htmlspecialchars($job['submitter_name']); ?>
                                                            <?php if (!empty($job['submitter_email'])): ?>
                                                                <br><small><?php echo htmlspecialchars($job['submitter_email']); ?></small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo formatDate($job['created_at']); ?></td>
                                                    <td>
                                                        <?php echo getJobStatusLabel($job['approval_status']); ?>
                                                    </td>
                                                    <td class="job-actions">
                                                        <a href="#" class="action-btn view" title="View Details" onclick="viewJobDetails(<?php echo $job['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if ($job['approval_status'] === 'pending'): ?>
                                                            <a href="#" class="action-btn activate" title="Approve Job" onclick="showApproveModal(<?php echo $job['id']; ?>)">
                                                                <i class="fas fa-check-circle"></i>
                                                            </a>
                                                            
                                                            <a href="#" class="action-btn deactivate" title="Reject Job" onclick="showRejectModal(<?php echo $job['id']; ?>)">
                                                                <i class="fas fa-times-circle"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.');">
                                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="action-btn delete" title="Delete">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
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

    <!-- Job Details Modal -->
    <div id="jobDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('jobDetailsModal')">&times;</span>
            <h2>Job Details</h2>
            <div id="jobDetailsContent">Loading...</div>
        </div>
    </div>
    
    <!-- Approve Job Modal -->
    <div id="approveJobModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('approveJobModal')">&times;</span>
            <h2>Approve Job</h2>
            <p>Are you sure you want to approve this job posting? It will be visible to job seekers.</p>
            <form method="POST" id="approveJobForm">
                <input type="hidden" name="job_id" id="approveJobId">
                <input type="hidden" name="action" value="approve">
                
                <div class="form-group">
                    <label for="approveNotes">Notes (Optional, will be sent to the submitter)</label>
                    <textarea id="approveNotes" name="admin_notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="submit-btn">Approve Job</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('approveJobModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reject Job Modal -->
    <div id="rejectJobModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('rejectJobModal')">&times;</span>
            <h2>Reject Job</h2>
            <p>Please provide a reason for rejecting this job posting. This information will be sent to the submitter.</p>
            <form method="POST" id="rejectJobForm">
                <input type="hidden" name="job_id" id="rejectJobId">
                <input type="hidden" name="action" value="reject">
                
                <div class="form-group">
                    <label for="rejectNotes">Reason for Rejection</label>
                    <textarea id="rejectNotes" name="admin_notes" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="submit-btn">Reject Job</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('rejectJobModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // View job details
        function viewJobDetails(jobId) {
            document.getElementById('jobDetailsContent').innerHTML = 'Loading...';
            document.getElementById('jobDetailsModal').style.display = 'block';
            
            // Fetch job details with AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax/get_job_details.php?id=' + jobId, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('jobDetailsContent').innerHTML = xhr.responseText;
                } else {
                    document.getElementById('jobDetailsContent').innerHTML = 'Error loading job details.';
                }
            };
            
            xhr.onerror = function() {
                document.getElementById('jobDetailsContent').innerHTML = 'Error loading job details.';
            };
            
            xhr.send();
        }
        
        // Show approve modal
        function showApproveModal(jobId) {
            document.getElementById('approveJobId').value = jobId;
            document.getElementById('approveJobModal').style.display = 'block';
        }
        
        // Show reject modal
        function showRejectModal(jobId) {
            document.getElementById('rejectJobId').value = jobId;
            document.getElementById('rejectJobModal').style.display = 'block';
        }
        
        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>