<?php
require_once 'config.php';

// Initialize variables
$errors = [];
$success = '';
$first_name = '';
$last_name = '';
$email = '';
$phone = '';
$username = '';
// Remove company_name variable since we're removing employer option
$role = 'job_seeker'; // Hardcode role to job_seeker only

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'job_seeker'; // Force role to be job_seeker
    
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
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }
    } catch (PDOException $e) {
        error_log("Error checking email: " . $e->getMessage());
        $errors[] = "An error occurred. Please try again.";
    }
    
    // Check if username already exists
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already exists";
        }
    } catch (PDOException $e) {
        error_log("Error checking username: " . $e->getMessage());
        $errors[] = "An error occurred. Please try again.";
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database - Note: company_name is null for job seekers
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, username, password, role) 
                                   VALUES (:first_name, :last_name, :email, :phone, :username, :password, :role)");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            
            // Reset form fields
            $first_name = $last_name = $email = $phone = $username = '';
        } catch (PDOException $e) {
            error_log("Error registering user: " . $e->getMessage());
            $errors[] = "An error occurred while registering. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - B&H Employment & Consultancy Inc</title>
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
            <h1>Create an Account</h1>
            <p>Join our platform to find job opportunities</p>
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
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="register.php" method="POST" class="auth-form">
                    <!-- Hidden input for role - always job_seeker -->
                    <input type="hidden" name="role" value="job_seeker">
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        
                        <div class="form-group half">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group half">
                            <label for="phone">Phone Number (Optional)</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <small class="form-text">Password must be at least 6 characters</small>
                        </div>
                        
                        <div class="form-group half">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group terms-check">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                    </div>
                    
                    <button type="submit" class="submit-btn">Create Account</button>
                </form>
                
                <div class="auth-links">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>