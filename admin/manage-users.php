<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Handle verification/unverification and subscription updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'] ?? 0;
    
    // Verify/unverify user
    if (isset($_POST['toggle_verification'])) {
        $is_verified = (int)$_POST['is_verified'] ? 0 : 1; // Toggle the status
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = :is_verified WHERE id = :user_id");
            $stmt->bindParam(':is_verified', $is_verified);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            flashMessage("User verification status updated successfully", "success");
            redirect('manage-users.php');
        } catch (PDOException $e) {
            error_log("Error updating user verification: " . $e->getMessage());
            flashMessage("An error occurred while updating the user", "danger");
            redirect('manage-users.php');
        }
    }
    
    // Update subscription
    if (isset($_POST['update_subscription'])) {
        $subscription_start = !empty($_POST['subscription_start']) ? $_POST['subscription_start'] : null;
        $subscription_end = !empty($_POST['subscription_end']) ? $_POST['subscription_end'] : null;
        
        // Validate dates
        if (!empty($subscription_start) && !empty($subscription_end)) {
            $start_date = new DateTime($subscription_start);
            $end_date = new DateTime($subscription_end);
            
            if ($end_date <= $start_date) {
                flashMessage("End date must be after start date", "danger");
                redirect('manage-users.php?edit=' . $user_id);
            }
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET subscription_start = :subscription_start, subscription_end = :subscription_end WHERE id = :user_id");
            $stmt->bindParam(':subscription_start', $subscription_start);
            $stmt->bindParam(':subscription_end', $subscription_end);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            flashMessage("Subscription updated successfully", "success");
            redirect('manage-users.php');
        } catch (PDOException $e) {
            error_log("Error updating subscription: " . $e->getMessage());
            flashMessage("An error occurred while updating the subscription", "danger");
            redirect('manage-users.php');
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        // Prevent admin from deleting themselves
        if ($user_id == $_SESSION['user_id']) {
            flashMessage("You cannot delete your own account", "danger");
            redirect('manage-users.php');
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            flashMessage("User deleted successfully", "success");
            redirect('manage-users.php');
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            flashMessage("An error occurred while deleting the user", "danger");
            redirect('manage-users.php');
        }
    }
}

// Get user details for subscription edit if user_id is provided
$edit_user = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $edit_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $edit_user = $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Error fetching user for edit: " . $e->getMessage());
    }
}

// Get all users
try {
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}

// Format date
function formatDate($date) {
    return $date ? date('M j, Y', strtotime($date)) : 'Not set';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/updated-styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Users</h1>
            <p>Verify accounts and manage user subscriptions</p>
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
                    
                    <?php if ($edit_user): ?>
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-calendar-alt"></i> Manage Subscription for <?php echo htmlspecialchars($edit_user['first_name'] . ' ' . $edit_user['last_name']); ?></h2>
                            <a href="manage-users.php" class="btn-secondary">Back to Users</a>
                        </div>
                        
                        <div class="content-body">
                            <form action="manage-users.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="subscription_start">Subscription Start Date</label>
                                        <input type="date" id="subscription_start" name="subscription_start" class="form-control" value="<?php echo $edit_user['subscription_start'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="subscription_end">Subscription End Date</label>
                                        <input type="date" id="subscription_end" name="subscription_end" class="form-control" value="<?php echo $edit_user['subscription_end'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <p><i class="fas fa-info-circle"></i> Leave both fields empty to remove subscription dates (unlimited access).</p>
                                </div>
                                
                                <button type="submit" name="update_subscription" class="submit-btn">Update Subscription</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-users"></i> All Users</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($users)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>No users found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email/Username</th>
                                                <th>Role</th>
                                                <th>Company</th>
                                                <th>Verification</th>
                                                <th>Subscription Period</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($user['email']); ?><br>
                                                        <small><?php echo htmlspecialchars($user['username']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="role-badge <?php echo $user['role']; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['company_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo $user['is_verified'] ? 'active' : 'inactive'; ?>">
                                                            <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['subscription_start'] && $user['subscription_end']): ?>
                                                            <?php echo formatDate($user['subscription_start']); ?> to 
                                                            <?php echo formatDate($user['subscription_end']); ?>
                                                            
                                                            <?php
                                                            // Check if subscription is active
                                                            $today = new DateTime();
                                                            $end_date = new DateTime($user['subscription_end']);
                                                            
                                                            if ($today > $end_date) {
                                                                echo '<br><span class="status-badge inactive">Expired</span>';
                                                            } else {
                                                                echo '<br><span class="status-badge active">Active</span>';
                                                            }
                                                            ?>
                                                        <?php else: ?>
                                                            <?php if ($user['role'] === 'admin'): ?>
                                                                <span class="status-badge active">Unlimited</span>
                                                            <?php else: ?>
                                                                <span class="status-badge inactive">Not set</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="actions">
                                                        <?php if ($user['role'] !== 'admin' || $_SESSION['user_id'] !== $user['id']): ?>
                                                            <a href="manage-users.php?edit=<?php echo $user['id']; ?>" class="action-btn edit" title="Edit Subscription">
                                                                <i class="fas fa-calendar-alt"></i>
                                                            </a>
                                                            
                                                            <?php if ($user['role'] !== 'admin'): ?>
                                                                <form method="POST" class="action-form">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <input type="hidden" name="is_verified" value="<?php echo $user['is_verified']; ?>">
                                                                    <button type="submit" name="toggle_verification" class="action-btn <?php echo $user['is_verified'] ? 'deactivate' : 'activate'; ?>" 
                                                                            title="<?php echo $user['is_verified'] ? 'Revoke Verification' : 'Verify User'; ?>">
                                                                        <i class="fas <?php echo $user['is_verified'] ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i>
                                                                    </button>
                                                                </form>
                                                                
                                                                <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" name="delete_user" class="action-btn delete" title="Delete User">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
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
</body>
</html>