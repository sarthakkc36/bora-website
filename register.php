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
$company_name = '';
$role = 'job_seeker'; // Default selected role

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
    $role = sanitizeInput($_POST['role']);
    $company_name = sanitizeInput($_POST['company_name'] ?? '');
    
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
    
    if ($role === 'employer' && empty($company_name)) {
        $errors[] = "Company name is required for employer accounts";
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
            
            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, username, password, role, company_name) 
                                   VALUES (:first_name, :last_name, :email, :phone, :username, :password, :role, :company_name)");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->execute();
            
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            
            // Reset form fields
            $first_name = $last_name = $email = $phone = $username = $company_name = '';
            $role = 'job_seeker';
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
    <style>
        .role-selector {
            display: flex;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            border: 1px solid #ddd;
        }
        
        .role-option {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }
        
        .role-option.active {
            color: white;
        }
        
        .role-option i {
            display: block;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .role-option-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background-color: #0066cc;
            transition: transform 0.3s ease;
            z-index: 1;
        }
        
        .role-option-slider.employer {
            transform: translateX(100%);
        }
        
        .company-fields {
            display: none;
            transition: opacity 0.3s ease;
        }
        
        .company-fields.active {
            display: block;
            animation: fadeIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Create an Account</h1>
            <p>Join our platform to find job opportunities or post job listings</p>
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
                    <div class="role-selector">
                        <div class="role-option-slider <?php echo $role === 'employer' ? 'employer' : 'job_seeker'; ?>"></div>
                        <label class="role-option <?php echo $role === 'job_seeker' ? 'active' : ''; ?>" for="role_job_seeker">
                            <i class="fas fa-user"></i>
                            Job Seeker
                        </label>
                        <label class="role-option <?php echo $role === 'employer' ? 'active' : ''; ?>" for="role_employer">
                            <i class="fas fa-building"></i>
                            Employer
                        </label>
                    </div>
                    
                    <input type="radio" id="role_job_seeker" name="role" value="job_seeker" <?php echo $role === 'job_seeker' ? 'checked' : ''; ?> style="display: none;">
                    <input type="radio" id="role_employer" name="role" value="employer" <?php echo $role === 'employer' ? 'checked' : ''; ?> style="display: none;">
                    
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
                    
                    <div class="company-fields <?php echo $role === 'employer' ? 'active' : ''; ?>">
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_name); ?>">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jobSeekerOption = document.getElementById('role_job_seeker');
            const employerOption = document.getElementById('role_employer');
            const roleSlider = document.querySelector('.role-option-slider');
            const companyFields = document.querySelector('.company-fields');
            const jobSeekerLabel = document.querySelector('label[for="role_job_seeker"]');
            const employerLabel = document.querySelector('label[for="role_employer"]');
            
            // Toggle role selection
            jobSeekerLabel.addEventListener('click', function() {
                jobSeekerOption.checked = true;
                roleSlider.classList.remove('employer');
                companyFields.classList.remove('active');
                
                jobSeekerLabel.classList.add('active');
                employerLabel.classList.remove('active');
                
                // Make company name not required for job seekers
                document.getElementById('company_name').required = false;
            });
            
            employerLabel.addEventListener('click', function() {
                employerOption.checked = true;
                roleSlider.classList.add('employer');
                companyFields.classList.add('active');
                
                employerLabel.classList.add('active');
                jobSeekerLabel.classList.remove('active');
                
                // Make company name required for employers
                document.getElementById('company_name').required = true;
            });
        });
    </script>
</body>
</html>