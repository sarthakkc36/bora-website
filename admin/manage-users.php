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
        $verification_notes = sanitizeInput($_POST['verification_notes'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET 
                                 is_verified = :is_verified, 
                                 verification_notes = :verification_notes,
                                 verification_date = :verification_date
                                 WHERE id = :user_id");
            $stmt->bindParam(':is_verified', $is_verified);
            $stmt->bindParam(':verification_notes', $verification_notes);
            
            // If verifying, set current date as verification date
            $verification_date = $is_verified ? date('Y-m-d H:i:s') : null;
            $stmt->bindParam(':verification_date', $verification_date);
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            // Get user details for sending email
            $user_stmt = $pdo->prepare("SELECT email, first_name, last_name FROM users WHERE id = :user_id");
            $user_stmt->bindParam(':user_id', $user_id);
            $user_stmt->execute();
            $user = $user_stmt->fetch();
            
            // Send verification email to user
            if ($is_verified && !empty($user['email'])) {
                $subject = "Your Account Has Been Verified - B&H Employment & Consultancy";
                $message = "<p>Dear " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . ",</p>";
                $message .= "<p>Congratulations! Your account has been successfully verified.</p>";
                $message .= "<p>You now have full access to all features of our platform.</p>";
                $message .= "<p>Thank you for choosing B&H Employment & Consultancy Inc.</p>";
                $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                
                sendEmail($user['email'], $subject, $message);
            }
            
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

// Get user details for verification modal if user_id is provided
$verification_user = null;
if (isset($_GET['verify']) && !empty($_GET['verify'])) {
    $verify_id = (int)$_GET['verify'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $verify_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $verification_user = $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Error fetching user for verification: " . $e->getMessage());
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
    /* Modal Styles */
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
        max-width: 600px;
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
    
    .payment-info {
        background-color: #f0f7ff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 3px solid #0066cc;
    }
    
    .payment-info h4 {
        margin-top: 0;
        margin-bottom: 5px;
        color: #0066cc;
    }
    
    .payment-info p {
        margin-bottom: 0;
    }
</style>
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
                    
                    <?php if ($verification_user): ?>
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-user-check"></i> Verify User Account</h2>
                            <a href="manage-users.php" class="btn-secondary">Back to Users</a>
                        </div>
                        
                        <div class="content-body">
                            <div class="user-info-summary">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($verification_user['first_name'] . ' ' . $verification_user['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($verification_user['email']); ?></p>
                                <p><strong>Role:</strong> <?php echo ucfirst(str_replace('_', ' ', $verification_user['role'])); ?></p>
                                <p><strong>Registered:</strong> <?php echo formatDate($verification_user['created_at']); ?></p>
                            </div>
                            
                            <div class="payment-info">
                                <h4>Payment Verification</h4>
                                <p>Confirm that the user has completed the required payment at the office before verifying the account.</p>
                            </div>
                            
                            <form action="manage-users.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $verification_user['id']; ?>">
                                <input type="hidden" name="is_verified" value="<?php echo $verification_user['is_verified']; ?>">
                                
                                <div class="form-group">
                                    <label for="verification_notes">Verification Notes (Optional)</label>
                                    <textarea id="verification_notes" name="verification_notes" class="form-control" rows="3"><?php echo htmlspecialchars($verification_user['verification_notes'] ?? ''); ?></textarea>
                                    <small class="form-text">Add any notes about the verification (payment details, special instructions, etc.)</small>
                                </div>
                                
                                <button type="submit" name="toggle_verification" class="submit-btn">
                                    <?php echo $verification_user['is_verified'] ? 'Unverify Account' : 'Verify Account'; ?>
                                </button>
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
                                                        <?php if ($user['is_verified'] && !empty($user['verification_date'])): ?>
                                                            <br><small>on <?php echo date('M j, Y', strtotime($user['verification_date'])); ?></small>
                                                        <?php endif; ?>
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
                                                                <a href="manage-users.php?verify=<?php echo $user['id']; ?>" class="action-btn <?php echo $user['is_verified'] ? 'deactivate' : 'activate'; ?>" title="<?php echo $user['is_verified'] ? 'Unverify User' : 'Verify User'; ?>">
                                                                    <i class="fas <?php echo $user['is_verified'] ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i>
                                                                </a>
                                                                
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

    <!-- Verification Modal -->
    <div id="verificationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Verify User</h2>
            
            <div class="user-info-summary" id="modalUserInfo">
                <!-- User info will be populated via JavaScript -->
            </div>
            
            <div class="payment-info">
                <h4>Payment Verification</h4>
                <p>Confirm that the user has completed the required payment at the office before verifying the account.</p>
            </div>
            
            <form id="verificationForm" action="manage-users.php" method="POST">
                <input type="hidden" id="modalUserId" name="user_id" value="">
                <input type="hidden" id="modalIsVerified" name="is_verified" value="">
                
                <div class="form-group">
                    <label for="modalNotes">Verification Notes (Optional)</label>
                    <textarea id="modalNotes" name="verification_notes" class="form-control" rows="3"></textarea>
                    <small class="form-text">Add any notes about the verification (payment details, special instructions, etc.)</small>
                </div>
                
                <button type="submit" name="toggle_verification" class="submit-btn" id="modalSubmitBtn">Verify Account</button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        // Handle the verification modal
        function showVerificationModal(userId, userName, userEmail, isVerified) {
            const modal = document.getElementById('verificationModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalUserInfo = document.getElementById('modalUserInfo');
            const modalUserId = document.getElementById('modalUserId');
            const modalIsVerified = document.getElementById('modalIsVerified');
            const modalSubmitBtn = document.getElementById('modalSubmitBtn');
            
            // Set the modal title based on current verification status
            modalTitle.textContent = isVerified ? 'Unverify User Account' : 'Verify User Account';
            
            // Set the user info
            modalUserInfo.innerHTML = `
                <p><strong>Name:</strong> ${userName}</p>
                <p><strong>Email:</strong> ${userEmail}</p>
            `;
            
            // Set the form values
            modalUserId.value = userId;
            modalIsVerified.value = isVerified;
            
            // Set the button text
            modalSubmitBtn.textContent = isVerified ? 'Unverify Account' : 'Verify Account';
            
            // Show the modal
            modal.style.display = 'block';
        }
        
        // Close the modal
        function closeModal() {
            document.getElementById('verificationModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('verificationModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>