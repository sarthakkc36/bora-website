<?php
require_once 'config.php';

$errors = [];
$login_identifier = '';  // Store the username or email

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = sanitizeInput($_POST['login_identifier']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($login_identifier)) {
        $errors[] = "Username or Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        try {
            // Check if login_identifier is username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
            $stmt->bindParam(':identifier', $login_identifier);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // If employer, check verification status
                    if ($user['role'] === 'employer' && $user['is_verified'] != 1) {
                        $errors[] = "Your account is pending verification by an administrator. Please check back later or contact support.";
                    } else {
                        // Check subscription for employers
                        if ($user['role'] === 'employer') {
                            // If subscription has expired, note it but still allow login
                            $subscription_warning = '';
                            if (!empty($user['subscription_end']) && new DateTime() > new DateTime($user['subscription_end'])) {
                                $subscription_warning = "Your subscription has expired. Please contact an administrator to renew.";
                            }
                        }
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        
                        // Add verification and subscription data to session
                        $_SESSION['is_verified'] = $user['is_verified'];
                        $_SESSION['subscription_start'] = $user['subscription_start'] ?? null;
                        $_SESSION['subscription_end'] = $user['subscription_end'] ?? null;
                        
                        // Set subscription warning if any
                        if (!empty($subscription_warning)) {
                            flashMessage($subscription_warning, "warning");
                        }
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            redirect('admin/dashboard.php');
                        } elseif ($user['role'] === 'employer') {
                            redirect('employer/dashboard.php');
                        } else {
                            redirect('index.php');
                        }
                    }
                } else {
                    $errors[] = "Invalid username/email or password";
                }
            } else {
                $errors[] = "Invalid username/email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/updated-styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Login to Your Account</h1>
            <p>Access your personalized dashboard and manage your job search or recruitment process</p>
        </div>
    </section>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php displayFlashMessage(); ?>
                
                <form class="auth-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="login_identifier">Username or Email</label>
                        <input type="text" id="login_identifier" name="login_identifier" class="form-control" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group auth-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="submit-btn">Login</button>
                    
                    <div class="auth-links">
                        <p>Don't have an account? <a href="register.php">Register Now</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>