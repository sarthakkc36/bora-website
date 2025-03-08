<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Handle job actions (activate/deactivate/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'], $_POST['action'])) {
    $job_id = (int)$_POST['job_id'];
    $action = $_POST['action'];
    
    try {
        // Verify that the job exists
        $stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = :job_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            flashMessage("Job not found", "danger");
            redirect('manage-jobs.php');
        }
        
        // Perform the requested action
        switch ($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE jobs SET is_active = 1 WHERE id = :job_id");
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                flashMessage("Job activated successfully", "success");
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE jobs SET is_active = 0 WHERE id = :job_id");
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                flashMessage("Job deactivated successfully", "success");
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

// Get all jobs with employer information
try {
    $stmt = $pdo->prepare("SELECT j.*, u.first_name, u.last_name, u.username, u.email, u.company_name as employer_company 
                          FROM jobs j 
                          LEFT JOIN users u ON j.user_id = u.id
                          ORDER BY j.created_at DESC");
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
    <link rel="stylesheet" href="../css/updated-styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Jobs</h1>
            <p>Oversee all job postings across the platform</p>
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
                            <h2><i class="fas fa-briefcase"></i> All Job Postings</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($jobs)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-briefcase"></i>
                                    <p>No jobs found in the system.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Employer</th>
                                                <th>Location</th>
                                                <th>Applications</th>
                                                <th>Posted On</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($jobs as $job): ?>
                                                <tr>
                                                    <td>
                                                        <a href="../job-details.php?id=<?php echo $job['id']; ?>" target="_blank">
                                                            <?php echo htmlspecialchars($job['title']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?><br>
                                                        <small><?php echo htmlspecialchars($job['email']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                                    <td><?php echo number_format($job['applications']); ?></td>
                                                    <td><?php echo formatDate($job['created_at']); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                                            <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="actions">
                                                        <a href="../job-details.php?id=<?php echo $job['id']; ?>" target="_blank" class="action-btn view" title="View Job">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if ($job['is_active']): ?>
                                                            <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to deactivate this job?');">
                                                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <button type="submit" class="action-btn deactivate" title="Deactivate">
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" class="action-form">
                                                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="action-btn activate" title="Activate">
                                                                    <i class="fas fa-check-circle"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <a href="view-applications.php?job_id=<?php echo $job['id']; ?>" class="action-btn view" title="View Applications">
                                                            <i class="fas fa-users"></i>
                                                        </a>
                                                        
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

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
</body>
</html>