<?php
require_once '../config.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isJobSeeker()) {
    flashMessage("You must be logged in as a job seeker to access this page", "danger");
    redirect('../login.php');
}

// Handle filtering
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'all';
$filter_condition = "";

if ($filter !== 'all') {
    $filter_condition = "AND ja.status = :filter_status";
}

// Get job applications with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Count total applications with filter
    $count_query = "SELECT COUNT(*) as total FROM job_applications ja WHERE ja.user_id = :user_id " . $filter_condition;
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':user_id', $_SESSION['user_id']);
    
    if ($filter !== 'all') {
        $count_stmt->bindParam(':filter_status', $filter);
    }
    
    $count_stmt->execute();
    $total_applications = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_applications / $per_page);
    
    // Get applications with pagination and filter
    $query = "SELECT ja.*, j.title, j.company_name, j.location, j.job_type, j.experience_level
              FROM job_applications ja
              JOIN jobs j ON ja.job_id = j.id
              WHERE ja.user_id = :user_id " . $filter_condition . "
              ORDER BY ja.created_at DESC
              LIMIT :offset, :per_page";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    
    if ($filter !== 'all') {
        $stmt->bindParam(':filter_status', $filter);
    }
    
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching applications: " . $e->getMessage());
    flashMessage("An error occurred while retrieving your applications", "danger");
    $applications = [];
    $total_applications = 0;
    $total_pages = 1;
}

// Format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Status descriptions
$status_descriptions = [
    'pending' => 'Your application is being reviewed by the employer.',
    'reviewed' => 'Your application has been reviewed by the employer.',
    'interviewed' => 'You have been selected for an interview.',
    'offered' => 'Congratulations! You have received a job offer.',
    'rejected' => 'Unfortunately, the employer has selected another candidate.'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - B&H Employment & Consultancy Inc</title>
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
.filter-tabs {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.filter-tab {
    padding: 10px 20px;
    margin-right: 5px;
    cursor: pointer;
    color: #666;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    color: #0066cc;
}

.filter-tab.active {
    color: #0066cc;
    border-bottom-color: #0066cc;
    font-weight: 600;
}

.application-status {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
}

.application-status.pending {
    background-color: #fff3cd;
    color: #856404;
}

.application-status.reviewed {
    background-color: #cce5ff;
    color: #004085;
}

.application-status.interviewed {
    background-color: #d1ecf1;
    color: #0c5460;
}

.application-status.offered {
    background-color: #d4edda;
    color: #155724;
}

.application-status.rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.application-card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.application-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.application-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    position: relative;
}

.application-content {
    padding: 20px;
}

.application-footer {
    padding: 15px 20px;
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.job-title {
    font-size: 18px;
    margin-bottom: 5px;
    color: #333;
}

.job-company {
    font-size: 16px;
    color: #0066cc;
    margin-bottom: 10px;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 5px;
}

.job-meta span {
    display: flex;
    align-items: center;
    color: #666;
    font-size: 14px;
}

.job-meta i {
    margin-right: 5px;
    color: #0066cc;
}

.status-info {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.status-description {
    color: #666;
    margin-top: 10px;
}

.application-actions {
    display: flex;
    gap: 10px;
}

.empty-state {
    text-align: center;
    padding: 40px 0;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 15px;
}

.empty-state h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.empty-state p {
    color: #666;
    margin-bottom: 20px;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.page-item {
    margin: 0 5px;
}

.page-link {
    display: block;
    padding: 8px 16px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #0066cc;
    transition: all 0.3s ease;
}

.page-link:hover {
    background-color: #f0f7ff;
}

.page-item.active .page-link {
    background-color: #0066cc;
    color: #fff;
    border-color: #0066cc;
}

.page-item.disabled .page-link {
    color: #ccc;
    pointer-events: none;
}
</style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>My Applications</h1>
            <p>Track and manage your job applications</p>
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
                        <a href="applications.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            All <span class="count">(<?php echo $total_applications; ?>)</span>
                        </a>
                        <a href="applications.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                            Pending
                        </a>
                        <a href="applications.php?filter=reviewed" class="filter-tab <?php echo $filter === 'reviewed' ? 'active' : ''; ?>">
                            Reviewed
                        </a>
                        <a href="applications.php?filter=interviewed" class="filter-tab <?php echo $filter === 'interviewed' ? 'active' : ''; ?>">
                            Interviewed
                        </a>
                        <a href="applications.php?filter=offered" class="filter-tab <?php echo $filter === 'offered' ? 'active' : ''; ?>">
                            Offered
                        </a>
                        <a href="applications.php?filter=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">
                            Rejected
                        </a>
                    </div>
                    
                    <div class="applications-container">
                        <?php if (empty($applications)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <h3>No applications found</h3>
                                <?php if ($filter !== 'all'): ?>
                                    <p>You don't have any <?php echo $filter; ?> applications.</p>
                                    <a href="applications.php" class="btn-primary">View All Applications</a>
                                <?php else: ?>
                                    <p>You haven't applied to any jobs yet.</p>
                                    <a href="../jobs.php" class="btn-primary">Browse Jobs</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($applications as $application): ?>
                                <div class="application-card">
                                    <div class="application-header">
                                        <h3 class="job-title"><?php echo htmlspecialchars($application['title']); ?></h3>
                                        <p class="job-company"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                        <div class="job-meta">
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($application['location']); ?></span>
                                            <span><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $application['job_type'])); ?></span>
                                            <span><i class="fas fa-user-tie"></i> <?php echo ucfirst($application['experience_level']); ?> Level</span>
                                        </div>
                                        <div class="job-meta">
                                            <span><i class="fas fa-calendar"></i> Applied on <?php echo formatDate($application['created_at']); ?></span>
                                            <?php if (!empty($application['interview_date'])): ?>
                                                <span><i class="fas fa-calendar-check"></i> Interview on <?php echo formatDate($application['interview_date']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="application-content">
                                        <div class="status-info">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <h4>Application Status:</h4>
                                                <span class="application-status <?php echo $application['status']; ?>"><?php echo ucfirst($application['status']); ?></span>
                                            </div>
                                            <p class="status-description"><?php echo $status_descriptions[$application['status']]; ?></p>
                                        </div>
                                        
                                        <?php if (!empty($application['employer_notes'])): ?>
                                            <div class="employer-notes">
                                                <h4>Employer Notes:</h4>
                                                <p><?php echo nl2br(htmlspecialchars($application['employer_notes'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="application-footer">
                                        <div class="application-actions">
                                            <a href="../job-details.php?id=<?php echo $application['job_id']; ?>" class="btn-secondary" target="_blank">
                                                <i class="fas fa-eye"></i> View Job
                                            </a>
                                            <a href="application-detail.php?id=<?php echo $application['id']; ?>" class="btn-primary">
                                                <i class="fas fa-file-alt"></i> View Application
                                            </a>
                                        </div>
                                        
                                        <?php if ($application['status'] === 'rejected'): ?>
                                            <a href="../jobs.php?similar=<?php echo $application['job_id']; ?>" class="btn-secondary">
                                                <i class="fas fa-search"></i> Find Similar Jobs
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <div class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </div>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <div class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                                        </div>
                                    <?php endfor; ?>
                                    
                                    <div class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
</body>
</html>