<?php
require_once '../config.php';

// Check if user is logged in and is an employer
if (!isLoggedIn() || !isEmployer()) {
    flashMessage("You must be logged in as an employer to access this page", "danger");
    redirect('../login.php');
}

$errors = [];
$success = '';

// Get employer information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $employer = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching employer information: " . $e->getMessage());
    $employer = [];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $company_name = sanitizeInput($_POST['company_name']);
    
    // Validate inputs
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($company_name)) {
        $errors[] = "Company name is required";
    }
    
    // Check if email exists (if changed)
    if ($email !== $employer['email']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND id != :user_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, company_name = :company_name WHERE id = :user_id");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            // Update session information
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $employer = $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $errors[] = "An error occurred while updating your profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Get form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    }
    
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match";
    }
    
    // Check if current password is correct
    if (!password_verify($current_password, $employer['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    // If no errors, update password
    if (empty($errors)) {
        try {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            $success = "Password changed successfully!";
            
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            $errors[] = "An error occurred while changing your password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Profile - B&H Employment & Consultancy Inc</title>
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
            <h1>My Profile</h1>
            <p>Manage your account and company information</p>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <?php include 'sidebar.php'; ?>
                </div>
                
                <div class="dashboard-content">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php displayFlashMessage(); ?>
                    
                    <!-- Account Status Information -->
                    <div class="account-status">
                        <?php if (!isVerified()): ?>
                            <div class="alert alert-warning">
                                <h3><i class="fas fa-exclamation-circle"></i> Account Verification Pending</h3>
                                <p>Your account is awaiting verification by our administrators. Once verified, you'll be able to post jobs.</p>
                                <p>This typically takes 1-2 business days. If you have any questions, please contact us.</p>
                            </div>
                        <?php elseif (!hasValidSubscription()): ?>
                            <div class="alert alert-warning">
                                <h3><i class="fas fa-calendar-times"></i> Subscription Required</h3>
                                <p>You don't currently have an active subscription to post jobs. Please contact our administrator to activate your subscription.</p>
                                <p><strong>Contact:</strong> <?php echo $site_settings['contact_email'] ?? 'bh.jobagency@gmail.com'; ?> | <?php echo $site_settings['contact_phone'] ?? '(1)347680-2869'; ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <h3><i class="fas fa-check-circle"></i> Account Active</h3>
                                <p>Your account is verified and your subscription is active. You can post jobs and manage applications.</p>
                                <?php if (!empty($_SESSION['subscription_end'])): ?>
                                    <p><strong>Subscription valid until:</strong> <?php echo date('F j, Y', strtotime($_SESSION['subscription_end'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-user"></i> Personal Information</h2>
                        </div>
                        
                        <div class="content-body">
                            <form action="profile.php" method="POST">
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($employer['first_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($employer['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employer['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($employer['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($employer['username']); ?>" disabled>
                                    <small class="form-text">Username cannot be changed</small>
                                </div>
                                
                                <hr>
                                <h3>Company Information</h3>
                                
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($employer['company_name'] ?? ''); ?>" required>
                                </div>
                                
                                <button type="submit" name="update_profile" class="submit-btn">Save Changes</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                        </div>
                        
                        <div class="content-body">
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    <small class="form-text">Password must be at least 6 characters long</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="submit-btn">Change Password</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-chart-line"></i> Account Overview</h2>
                        </div>
                        
                        <div class="content-body">
                            <div class="account-overview">
                                <div class="overview-item">
                                    <div class="overview-label">Account Type</div>
                                    <div class="overview-value">Employer</div>
                                </div>
                                
                                <div class="overview-item">
                                    <div class="overview-label">Member Since</div>
                                    <div class="overview-value"><?php echo date('F j, Y', strtotime($employer['created_at'])); ?></div>
                                </div>
                                
                                <div class="overview-item">
                                    <div class="overview-label">Verification Status</div>
                                    <div class="overview-value">
                                        <span class="status-badge <?php echo $employer['is_verified'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $employer['is_verified'] ? 'Verified' : 'Pending Verification'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="overview-item">
                                    <div class="overview-label">Subscription Status</div>
                                    <div class="overview-value">
                                        <?php if (hasValidSubscription()): ?>
                                            <span class="status-badge active">Active</span>
                                            <?php if (!empty($employer['subscription_end'])): ?>
                                                <small>Valid until: <?php echo date('F j, Y', strtotime($employer['subscription_end'])); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-badge inactive">Inactive</span>
                                            <small>Please contact administration to activate</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-admin">
                                <p>Need help with your account? Contact our administration team:</p>
                                <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $site_settings['contact_email'] ?? 'bh.jobagency@gmail.com'; ?>"><?php echo $site_settings['contact_email'] ?? 'bh.jobagency@gmail.com'; ?></a></p>
                                <p><i class="fas fa-phone"></i> <?php echo $site_settings['contact_phone'] ?? '(1)347680-2869'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    
    <style>
    hr {
        margin: 25px 0;
        border: 0;
        border-top: 1px solid #eee;
    }
    
    h3 {
        margin-bottom: 20px;
        color: #333;
        font-size: 20px;
    }
    
    .account-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .overview-item {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }
    
    .overview-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    
    .overview-value {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    
    .overview-value small {
        display: block;
        font-size: 12px;
        font-weight: normal;
        color: #666;
        margin-top: 5px;
    }
    
    .contact-admin {
        background-color: #f0f7ff;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #0066cc;
    }
    
    .contact-admin p {
        margin-bottom: 10px;
    }
    
    .contact-admin i {
        color: #0066cc;
        width: 20px;
        text-align: center;
        margin-right: 5px;
    }
    
    .form-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    </style>
</body>
</html>