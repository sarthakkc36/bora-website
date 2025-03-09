<?php
require_once '../config.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isJobSeeker()) {
    flashMessage("You must be logged in as a job seeker to access this page", "danger");
    redirect('../login.php');
}

// Initialize error and success messages
$errors = [];
$success = '';

// Get user info
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    flashMessage("An error occurred while retrieving your profile", "danger");
    $user = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $zip_code = sanitizeInput($_POST['zip_code']);
    $bio = sanitizeInput($_POST['bio']);
    $skills = sanitizeInput($_POST['skills']);
    
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
    
    // Check if email exists (if changed)
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND id != :user_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    // Check for profile image upload
    $profile_image = $user['profile_image'] ?? null;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['profile_image']['size'] > $max_size) {
            $errors[] = "File size is too large. Maximum size is 2MB.";
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/profile_images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = 'uploads/profile_images/' . $new_filename;
            } else {
                $errors[] = "Failed to upload profile image. Please try again.";
            }
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET 
                first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                phone = :phone, 
                address = :address, 
                city = :city, 
                state = :state, 
                zip_code = :zip_code, 
                bio = :bio, 
                skills = :skills, 
                profile_image = :profile_image 
                WHERE id = :user_id");
                
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':zip_code', $zip_code);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':skills', $skills);
            $stmt->bindParam(':profile_image', $profile_image);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            // Update session variables
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $email;
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $errors[] = "An error occurred while updating your profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - B&H Employment & Consultancy Inc</title>
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
.profile-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 30px;
    gap: 30px;
}

.profile-image-container {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 3px solid #fff;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.profile-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-image-placeholder {
    font-size: 60px;
    color: #aaa;
}

.profile-image-upload {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: #0066cc;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-image-upload:hover {
    background-color: #0052a3;
}

.profile-image-upload input {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.profile-details {
    flex: 1;
    min-width: 300px;
}

.profile-name {
    font-size: 28px;
    margin-bottom: 5px;
    color: #333;
}

.profile-title {
    font-size: 18px;
    color: #0066cc;
    margin-bottom: 15px;
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.profile-meta span {
    display: flex;
    align-items: center;
    color: #666;
}

.profile-meta i {
    margin-right: 5px;
    color: #0066cc;
}

.tabs-container {
    margin-bottom: 30px;
}

.tab-buttons {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    color: #666;
}

.tab-button.active {
    border-bottom-color: #0066cc;
    color: #0066cc;
    font-weight: 600;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.skills-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.skill-tag {
    background-color: #f0f7ff;
    color: #0066cc;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
}

.form-section {
    margin-bottom: 30px;
}

.form-section-title {
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    color: #333;
}
</style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>My Profile</h1>
            <p>Manage your personal and professional information</p>
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
                    
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>'s profile">
                            <?php else: ?>
                                <div class="profile-image-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <label class="profile-image-upload" title="Upload Profile Picture">
                                <i class="fas fa-camera"></i>
                                <form id="profile-image-form" enctype="multipart/form-data" method="POST" action="profile.php">
                                    <input type="file" name="profile_image" id="profile-image-input" accept="image/*" onchange="document.getElementById('profile-image-form').submit();">
                                    <input type="hidden" name="update_profile" value="1">
                                    <!-- Include current values as hidden fields to avoid losing data -->
                                    <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                    <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    <input type="hidden" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                    <input type="hidden" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                    <input type="hidden" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                    <input type="hidden" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                                    <input type="hidden" name="bio" value="<?php echo htmlspecialchars($user['bio'] ?? ''); ?>">
                                    <input type="hidden" name="skills" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>">
                                </form>
                            </label>
                        </div>
                        
                        <div class="profile-details">
                            <h2 class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                            <p class="profile-title">Job Seeker</p>
                            
                            <div class="profile-meta">
                                <?php if (!empty($user['email'])): ?>
                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($user['phone'])): ?>
                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($user['city']) && !empty($user['state'])): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['city'] . ', ' . $user['state']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tabs-container">
                        <div class="tab-buttons">
                            <div class="tab-button active" data-tab="profile">Profile Information</div>
                            <div class="tab-button" data-tab="skills">Skills & Experience</div>
                        </div>
                        
                        <div class="tab-content active" id="profile-tab">
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <div class="form-section">
                                    <h3 class="form-section-title">Personal Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="first_name">First Name</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="email">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Address Information</h3>
                                    
                                    <div class="form-group">
                                        <label for="address">Street Address</label>
                                        <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="state">State</label>
                                            <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="zip_code">ZIP Code</label>
                                        <input type="text" id="zip_code" name="zip_code" class="form-control" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">About Me</h3>
                                    
                                    <div class="form-group">
                                        <label for="bio">Professional Summary</label>
                                        <textarea id="bio" name="bio" class="form-control" rows="5"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                        <small class="form-text">Brief summary of your professional background and career goals</small>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Skills</h3>
                                    
                                    <div class="form-group">
                                        <label for="skills">Skills (comma separated)</label>
                                        <input type="text" id="skills" name="skills" class="form-control" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>">
                                        <small class="form-text">Add your skills separated by commas (e.g., Project Management, Microsoft Excel, Customer Service)</small>
                                    </div>
                                    
                                    <?php if (!empty($user['skills'])): ?>
                                        <div class="skills-container">
                                            <?php foreach (explode(',', $user['skills']) as $skill): ?>
                                                <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" name="update_profile" class="submit-btn">Save Changes</button>
                            </form>
                        </div>
                        
                        <div class="tab-content" id="skills-tab">
                            <div class="content-box">
                                <div class="content-header">
                                    <h2>Professional Experience</h2>
                                    <a href="resume.php" class="btn-primary">Manage Resume</a>
                                </div>
                                
                                <div class="content-body">
                                    <p>Update your work experience, education, and skills by managing your resume.</p>
                                    <div class="empty-state">
                                        <i class="fas fa-file-alt"></i>
                                        <h3>Resume Management</h3>
                                        <p>Add your professional experience, education, and certifications through our resume builder.</p>
                                        <a href="resume.php" class="btn-primary">Go to Resume Builder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    document.getElementById(this.getAttribute('data-tab') + '-tab').classList.add('active');
                });
            });
        });
    </script>
    <script src="../js/script.js"></script>
</body>
</html>