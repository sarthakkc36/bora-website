<?php
require_once '../config.php';
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_jobs FROM jobs WHERE approval_status = 'pending'");
    $stmt->execute();
    $pending_jobs_count = $stmt->fetch()['pending_jobs'];
} catch (PDOException $e) {
    error_log("Error fetching pending jobs: " . $e->getMessage());
    $pending_jobs_count = 0;
}

// Get pending appointment count
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_appointments FROM appointments WHERE status = 'pending'");
    $stmt->execute();
    $pending_appointments_count = $stmt->fetch()['pending_appointments'];
} catch (PDOException $e) {
    error_log("Error fetching pending appointments: " . $e->getMessage());
    $pending_appointments_count = 0;
}

// Get recent pending jobs
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs 
                          WHERE approval_status = 'pending' 
                          ORDER BY created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $pending_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching pending jobs: " . $e->getMessage());
    $pending_jobs = [];
}

// Get recent pending appointments
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments 
                          WHERE status = 'pending' 
                          ORDER BY created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $pending_appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching pending appointments: " . $e->getMessage());
    $pending_appointments = [];
}
// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Get site statistics
try {
    // User counts
    $stmt = $pdo->prepare("SELECT 
                          COUNT(*) as total_users,
                          SUM(CASE WHEN role = 'employer' THEN 1 ELSE 0 END) as employer_count,
                          SUM(CASE WHEN role = 'job_seeker' THEN 1 ELSE 0 END) as job_seeker_count
                          FROM users");
    $stmt->execute();
    $user_stats = $stmt->fetch();
    
    // Job counts
    $stmt = $pdo->prepare("SELECT 
                          COUNT(*) as total_jobs,
                          SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_jobs
                          FROM jobs");
    $stmt->execute();
    $job_stats = $stmt->fetch();
    
    // Application count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_applications FROM job_applications");
    $stmt->execute();
    $application_count = $stmt->fetch()['total_applications'];
    
    // Recent users
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
    // Recent jobs
    $stmt = $pdo->prepare("SELECT j.*, u.username as employer_username 
                          FROM jobs j 
                          JOIN users u ON j.user_id = u.id 
                          ORDER BY j.created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $recent_jobs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching admin statistics: " . $e->getMessage());
    $user_stats = ['total_users' => 0, 'employer_count' => 0, 'job_seeker_count' => 0];
    $job_stats = ['total_jobs' => 0, 'active_jobs' => 0];
    $application_count = 0;
    $recent_users = $recent_jobs = [];
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
    <title>Admin Dashboard - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
        <style>
            .dashboard-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.quick-action {
    background-color: white;
    border-radius: 12px;
    padding: 25px 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    text-decoration: none;
    color: #333;
}

.quick-action:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    background-color: #f0f7ff;
    color: #0066cc;
}

.quick-action i {
    font-size: 32px;
    margin-bottom: 15px;
    color: #0066cc;
    transition: all 0.3s ease;
}

.quick-action:hover i {
    transform: scale(1.2);
}

.quick-action span {
    font-weight: 600;
    font-size: 16px;
}

.notification-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: #ff3366;
    color: white;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* For smaller screens */
@media (max-width: 768px) {
    .dashboard-quick-actions {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .quick-action {
        padding: 20px 15px;
    }
    
    .quick-action i {
        font-size: 28px;
    }
    
    .quick-action span {
        font-size: 14px;
    }
}
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff3366;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        .quick-action {
            position: relative;
        }
        </style>
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
            <h1>Admin Dashboard</h1>
            <p>Manage users, jobs, and site content</p>
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
                    
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($user_stats['total_users']); ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($user_stats['employer_count']); ?></h3>
                                <p>Employers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($job_stats['total_jobs']); ?></h3>
                                <p>Total Jobs</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon active">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($job_stats['active_jobs']); ?></h3>
                                <p>Active Jobs</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($pending_jobs_count); ?></h3>
                                <p>Pending Job Approvals</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($pending_appointments_count); ?></h3>
                                <p>Pending Appointments</p>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Get count of unread messages
                        $unread_messages_count = 0;
                        try {
                            $messages_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM contact_messages WHERE is_read = 0");
                            $messages_stmt->execute();
                            $unread_messages_count = $messages_stmt->fetch()['unread_count'];
                        } catch (PDOException $e) {
                            error_log("Error fetching unread messages count: " . $e->getMessage());
                        }
                    ?>
                    <div class="dashboard-quick-actions">
                        <a href="manage-users.php" class="quick-action">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="manage-jobs.php" class="quick-action">
                            <i class="fas fa-briefcase"></i>
                            <span>Manage Jobs</span>
                        </a>
                        <a href="manage-services.php" class="quick-action">
                            <i class="fas fa-cogs"></i>
                            <span>Manage Services</span>
                        </a>
                        <a href="contact-messages.php" class="quick-action">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                            <?php if ($unread_messages_count > 0): ?>
                                <div class="notification-badge"><?php echo $unread_messages_count; ?></div>
                            <?php endif; ?>
                        </a>
                        <a href="site-settings.php" class="quick-action">
                            <i class="fas fa-sliders-h"></i>
                            <span>Site Settings</span>
                        </a>
                        <a href="appointments.php?status=pending" class="quick-action">
                            <i class="fas fa-calendar-check"></i>
                            <span>Manage Appointments</span>
                            <?php if ($pending_appointments_count > 0): ?>
                                <div class="notification-badge"><?php echo $pending_appointments_count; ?></div>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="dashboard-row">
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-users"></i> Recent Users</h2>
                                    <a href="manage-users.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_users)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <p>No users found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="users-list">
                                            <?php foreach ($recent_users as $user): ?>
                                                <div class="user-item">
                                                    <div class="user-avatar">
                                                        <i class="fas fa-user-circle"></i>
                                                    </div>
                                                    <div class="user-details">
                                                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                                        <div class="user-meta">
                                                            <span class="user-role <?php echo $user['role']; ?>"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                                                            <span class="user-date">Joined: <?php echo formatDate($user['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-row">
                            <div class="dashboard-column">
                                <div class="content-box">
                                    <div class="content-header">
                                        <h2><i class="fas fa-briefcase"></i> Pending Job Approvals</h2>
                                        <a href="manage-jobs.php?status=pending" class="view-all">View All</a>
                                    </div>
                                    
                                    <div class="content-body">
                                        <?php if (empty($pending_jobs)): ?>
                                            <div class="empty-state">
                                                <i class="fas fa-check-circle"></i>
                                                <p>No pending job approvals at this time.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="jobs-list">
                                                <?php foreach ($pending_jobs as $job): ?>
                                                    <div class="job-item dashboard-job">
                                                        <div class="job-info">
                                                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                                            <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                                            <div class="job-meta">
                                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($job['submitter_name']); ?></span>
                                                                <span><i class="fas fa-calendar"></i> Submitted on <?php echo formatDate($job['created_at']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="job-actions">
                                                            <a href="manage-jobs.php?status=pending" class="btn-primary btn-small">Review</a>
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
                                        <h2><i class="fas fa-calendar-check"></i> Pending Appointments</h2>
                                        <a href="appointments.php?status=pending" class="view-all">View All</a>
                                    </div>
                                    
                                    <div class="content-body">
                                        <?php if (empty($pending_appointments)): ?>
                                            <div class="empty-state">
                                                <i class="fas fa-check-circle"></i>
                                                <p>No pending appointment requests at this time.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="appointments-list">
                                                <?php foreach ($pending_appointments as $appointment): ?>
                                                    <div class="application-item">
                                                        <div class="applicant-info">
                                                            <div class="applicant-avatar">
                                                                <i class="fas fa-user-circle"></i>
                                                            </div>
                                                            <div class="applicant-details">
                                                                <h4><?php echo htmlspecialchars($appointment['name']); ?></h4>
                                                                <p>Purpose: <strong><?php echo htmlspecialchars($appointment['purpose']); ?></strong></p>
                                                                <p>Preferred: <?php echo formatDate($appointment['preferred_date']); ?> at <?php echo formatTime($appointment['preferred_time']); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="applicant-actions">
                                                            <a href="appointments.php?status=pending" class="btn-primary btn-small">Review</a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-column">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2><i class="fas fa-briefcase"></i> Recent Jobs</h2>
                                    <a href="manage-jobs.php" class="view-all">View All</a>
                                </div>
                                
                                <div class="content-body">
                                    <?php if (empty($recent_jobs)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-briefcase"></i>
                                            <p>No jobs found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="jobs-list">
                                            <?php foreach ($recent_jobs as $job): ?>
                                                <div class="job-item dashboard-job">
                                                    <div class="job-info">
                                                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                                        <p class="job-employer">
                                                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                                                            <span class="job-username">(<?php echo htmlspecialchars($job['employer_username']); ?>)</span>
                                                        </p>
                                                        <div class="job-meta">
                                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                                            <span><i class="fas fa-calendar"></i> Posted on <?php echo formatDate($job['created_at']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="job-status <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                                        <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
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
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    <script>// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    }
    
    // User dropdown toggle
    const userToggle = document.querySelector('.user-toggle');
    if (userToggle) {
        userToggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.nextElementSibling.classList.toggle('active');
        });
    }
    
    // Close the dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userToggle && !userToggle.contains(e.target)) {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            e.preventDefault();
            
            const target = document.querySelector(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navMenu = document.querySelector('.nav-menu');
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                }
            }
        });
    });
    
    // Scroll animation for elements
    function handleScrollAnimation() {
        const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in, .contact-form');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('active');
            }
        });
    }
    
    // Run animation on load
    handleScrollAnimation();
    
    // Add animation classes to elements
    document.querySelectorAll('.service-card').forEach((card, index) => {
        card.classList.add('fade-in');
        card.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.job-card').forEach((card, index) => {
        card.classList.add('fade-in');
        card.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.info-item').forEach((item, index) => {
        item.classList.add(index % 2 === 0 ? 'slide-in-left' : 'slide-in-right');
        item.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.section-title').forEach(title => {
        title.classList.add('scale-in');
    });
    
    // Run animation on scroll
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Job save functionality
    const saveBtns = document.querySelectorAll('.job-save[data-job-id]');
    saveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.getAttribute('href')) return; // Let the login link work normally
            
            const jobId = this.getAttribute('data-job-id');
            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            const isSaved = icon.classList.contains('fas');
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/save-job.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            if (isSaved) {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                text.textContent = 'Save Job';
                            } else {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                text.textContent = 'Saved';
                                
                                // Add heart animation
                                icon.style.transform = 'scale(1.3)';
                                setTimeout(() => {
                                    icon.style.transform = 'scale(1)';
                                }, 300);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            
            xhr.send('job_id=' + jobId + '&action=' + (isSaved ? 'unsave' : 'save'));
        });
    });
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.alert');
    if (flashMessages.length > 0) {
        setTimeout(() => {
            flashMessages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
});</script>
    <script src="../js/script.js"></script>
</body>
</html>