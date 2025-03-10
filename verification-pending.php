<?php
require_once 'config.php';

// Check if user is logged in 
if (!isLoggedIn()) {
    redirect('login.php');
}

// If user is admin or employer, redirect to appropriate dashboard
if (isAdmin()) {
    redirect('admin/dashboard.php');
} elseif (isEmployer()) {
    redirect('employer/dashboard.php');
} elseif (isJobSeeker() && isVerified()) {
    redirect('job-seeker/dashboard.php');
}

// Get user info
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $user = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
.verification-container {
    max-width: 800px;
    margin: 0 auto;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    padding: 40px;
}

.verification-header {
    text-align: center;
    margin-bottom: 30px;
}

.verification-header i {
    font-size: 64px;
    color: #f8bb86;
    margin-bottom: 20px;
}

.verification-header h2 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #333;
}

.verification-status {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.verification-status.pending {
    border-left: 4px solid #f8bb86;
}

.verification-status.rejected {
    border-left: 4px solid #f27474;
}

.verification-status h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #333;
    display: flex;
    align-items: center;
}

.verification-status h3 i {
    margin-right: 10px;
    color: #f8bb86;
}

.verification-status.rejected h3 i {
    color: #f27474;
}

.verification-steps {
    margin-top: 30px;
}

.step {
    margin-bottom: 15px;
    padding-left: 30px;
    position: relative;
}

.step-number {
    position: absolute;
    left: 0;
    top: 0;
    width: 24px;
    height: 24px;
    background-color: #0066cc;
    color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 14px;
    font-weight: bold;
}

.step-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.step-desc {
    color: #666;
}

.payment-info {
    background-color: #f0f7ff;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
    border-left: 3px solid #0066cc;
}

.payment-info h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #0066cc;
    font-size: 18px;
}

.payment-info p {
    margin-bottom: 10px;
}

.payment-info ul {
    list-style-type: none;
    padding-left: 0;
    margin-top: 15px;
}

.payment-info li {
    margin-bottom: 10px;
    padding-left: 25px;
    position: relative;
}

.payment-info li:before {
    content: "\f3d1";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #0066cc;
}

.payment-info .address {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #cce5ff;
}

.logout-link {
    text-align: center;
    margin-top: 20px;
}

.verification-footer {
    text-align: center;
    margin-top: 30px;
    color: #666;
}

.verification-footer .contact-info {
    margin-top: 10px;
}
</style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Account Verification</h1>
            <p>Your account requires verification to access all features</p>
        </div>
    </section>

    <section class="verification-section">
        <div class="container">
            <?php displayFlashMessage(); ?>
            
            <div class="verification-container">
                <div class="verification-header">
                    <i class="fas fa-user-clock"></i>
                    <h2>Account Verification Required</h2>
                    <p>Your account needs to be verified before you can access all features.</p>
                </div>
                
                <div class="verification-status pending">
                    <h3><i class="fas fa-hourglass-half"></i> Verification Pending</h3>
                    <p>Your account is currently pending verification. Please visit our office to complete the verification process with a payment.</p>
                    <?php if (isset($user['verification_notes']) && !empty($user['verification_notes']) && $user['is_verified'] == 0): ?>
                        <div class="alert alert-info">
                            <strong>Note:</strong> <?php echo htmlspecialchars($user['verification_notes']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-info">
                    <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
                    <p>To complete your account verification, please visit our office during business hours to make a payment.</p>
                    
                    <div class="address">
                        <strong>Office Address:</strong><br>
                        B&H Employment & Consultancy Inc<br>
                        37-51 75th St.1A<br>
                        Jackson Heights, NY 11372<br><br>
                        <strong>Business Hours:</strong><br>
                        Monday to Friday: 9:00 AM - 5:00 PM<br>
                        Saturday: 10:00 AM - 2:00 PM<br>
                        Sunday: Closed
                    </div>
                </div>
                
                <div class="verification-steps">
                    <h3>Verification Process</h3>
                    
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-title">Visit Our Office</div>
                        <div class="step-desc">Come to our office during business hours.</div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">Make Payment</div>
                        <div class="step-desc">Complete the verification fee payment at our office.</div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">Account Activation</div>
                        <div class="step-desc">We'll activate your account immediately after payment confirmation.</div>
                    </div>
                </div>
                
                <div class="verification-footer">
                    <p>If you have any questions or need assistance, please contact our support team.</p>
                    <div class="contact-info">
                        <i class="fas fa-envelope"></i> bh.jobagency@gmail.com
                        <i class="fas fa-phone ml-3"></i> (1)347-680-2869
                    </div>
                </div>
                
                <div class="logout-link">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>