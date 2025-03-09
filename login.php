<?php
require_once 'config.php';

// Initialize variables
$errors = [];
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email or username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, try to login
    if (empty($errors)) {
        try {
            // Check if input is email or username
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', $email);
            }
            
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['is_verified'] = (bool)$user['is_verified'];
                    $_SESSION['logged_in'] = true;
                    
                    // Update last login time
                    try {
                        $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :user_id");
                        $update_stmt->bindParam(':user_id', $user['id']);
                        $update_stmt->execute();
                    } catch (PDOException $e) {
                        // Just log the error but don't show it to user since login was successful
                        error_log("Error updating last login: " . $e->getMessage());
                    }
                    
                    // Redirect based on user role and verification status
                    if ($user['role'] === 'admin') {
                        // Admin users don't need verification
                        if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                            $redirect_url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            redirect($redirect_url);
                        } else {
                            redirect('admin/dashboard.php');
                        }
                    } elseif ($user['role'] === 'employer') {
                        // For now, employers don't need verification
                        if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                            $redirect_url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                            redirect($redirect_url);
                        } else {
                            redirect('employer/dashboard.php');
                        }
                    } elseif ($user['role'] === 'job_seeker') {
                        // Job seekers need verification
                        if ($user['is_verified']) {
                            // User is verified, proceed normally
                            if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                                $redirect_url = $_SESSION['redirect_after_login'];
                                unset($_SESSION['redirect_after_login']);
                                redirect($redirect_url);
                            } else {
                                redirect('job-seeker/dashboard.php');
                            }
                        } else {
                            // User is not verified, redirect to verification page
                            redirect('verification-pending.php');
                        }
                    } else {
                        // Unknown role
                        flashMessage("Invalid user role", "danger");
                        redirect('login.php');
                    }
                } else {
                    $errors[] = "Invalid password";
                }
            } else {
                $errors[] = "No account found with that email/username";
            }
        } catch (PDOException $e) {
            error_log("Error during login: " . $e->getMessage());
            $errors[] = "An error occurred during login. Please try again.";
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
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Login to Your Account</h1>
            <p>Access your dashboard and manage your profile</p>
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
                
                <form action="login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email or Username</label>
                        <input type="text" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="auth-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        
                        <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="submit-btn">Login</button>
                </form>
                
                <div class="auth-links">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
                
                <div class="verification-note">
                    <p><i class="fas fa-info-circle"></i> Job seekers will need to verify their account before accessing all features.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>