<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

$errors = [];
$success = '';

// Handle favicon upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_favicon'])) {
    // Check if a file was uploaded
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
        $allowed_types = ['image/x-icon', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Validate file type and size
        if (!in_array($_FILES['favicon']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only ICO, PNG, JPG, GIF, and SVG files are allowed.";
        } elseif ($_FILES['favicon']['size'] > $max_size) {
            $errors[] = "File size is too large. Maximum size is 2MB.";
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/favicon/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            $new_filename = 'favicon_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $upload_path)) {
                // Update favicon path in database
                $favicon_path = 'uploads/favicon/' . $new_filename;
                
                try {
                    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = 'favicon'");
                    $stmt->bindParam(':value', $favicon_path);
                    $stmt->execute();
                    
                    // If the setting doesn't exist, create it
                    if ($stmt->rowCount() === 0) {
                        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('favicon', :value)");
                        $stmt->bindParam(':value', $favicon_path);
                        $stmt->execute();
                    }
                    
                    $success = "Favicon uploaded and updated successfully!";
                } catch (PDOException $e) {
                    error_log("Error updating favicon: " . $e->getMessage());
                    $errors[] = "An error occurred while updating the favicon. Please try again.";
                }
            } else {
                $errors[] = "Failed to upload file. Please try again.";
            }
        }
    } else {
        $errors[] = "Please select a file to upload.";
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    // Get form data
    $site_title = sanitizeInput($_POST['site_title']);
    $site_description = sanitizeInput($_POST['site_description']);
    $contact_email = sanitizeInput($_POST['contact_email']);
    $contact_phone = sanitizeInput($_POST['contact_phone']);
    $contact_address = sanitizeInput($_POST['contact_address']);
    $social_facebook = sanitizeInput($_POST['social_facebook']);
    $social_twitter = sanitizeInput($_POST['social_twitter']);
    $social_linkedin = sanitizeInput($_POST['social_linkedin']);
    $social_instagram = sanitizeInput($_POST['social_instagram']);
    
    // Validate inputs
    if (empty($site_title)) {
        $errors[] = "Site title is required";
    }
    
    if (empty($site_description)) {
        $errors[] = "Site description is required";
    }
    
    if (empty($contact_email)) {
        $errors[] = "Contact email is required";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid contact email format";
    }
    
    if (empty($contact_phone)) {
        $errors[] = "Contact phone is required";
    }
    
    if (empty($contact_address)) {
        $errors[] = "Contact address is required";
    }
    
    // If no errors, update settings
    if (empty($errors)) {
        try {
            // Update settings
            $settings = [
                'site_title' => $site_title,
                'site_description' => $site_description,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'contact_address' => $contact_address,
                'social_facebook' => $social_facebook,
                'social_twitter' => $social_twitter,
                'social_linkedin' => $social_linkedin,
                'social_instagram' => $social_instagram
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = :key");
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':key', $key);
                $stmt->execute();
            }
            
            $success = "Settings updated successfully!";
        } catch (PDOException $e) {
            error_log("Error updating settings: " . $e->getMessage());
            $errors[] = "An error occurred while updating settings. Please try again.";
        }
    }
}

// Handle legal documents update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_legal_documents'])) {
    $terms_of_service = $_POST['terms_of_service'];
    $privacy_policy = $_POST['privacy_policy'];
    
    try {
        // Update terms of service
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = 'terms_of_service'");
        $stmt->bindParam(':value', $terms_of_service);
        $stmt->execute();
        
        // If the setting doesn't exist, create it
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('terms_of_service', :value)");
            $stmt->bindParam(':value', $terms_of_service);
            $stmt->execute();
        }
        
        // Update privacy policy
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = 'privacy_policy'");
        $stmt->bindParam(':value', $privacy_policy);
        $stmt->execute();
        
        // If the setting doesn't exist, create it
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('privacy_policy', :value)");
            $stmt->bindParam(':value', $privacy_policy);
            $stmt->execute();
        }
        
        $success = "Legal documents updated successfully!";
    } catch (PDOException $e) {
        error_log("Error updating legal documents: " . $e->getMessage());
        $errors[] = "An error occurred while updating legal documents. Please try again.";
    }
}

// Get current settings
try {
    $stmt = $pdo->prepare("SELECT * FROM site_settings");
    $stmt->execute();
    $settings_rows = $stmt->fetchAll();
    
    // Convert to associative array
    $settings = [];
    foreach ($settings_rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $settings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - B&H Employment & Consultancy Inc</title>
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
            <h1>Site Settings</h1>
            <p>Manage website information and configuration</p>
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
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-image"></i> Site Favicon</h2>
                        </div>
                        
                        <div class="content-body">
                            <div class="current-favicon">
                                <h3>Current Favicon</h3>
                                <div class="favicon-preview">
                                    <?php if (!empty($settings['favicon'])): ?>
                                        <img src="../<?php echo htmlspecialchars($settings['favicon']); ?>?v=<?php echo time(); ?>" alt="Current Favicon">
                                    <?php else: ?>
                                        <div class="no-favicon">No favicon set</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <form action="site-settings.php" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="favicon">Upload New Favicon</label>
                                    <input type="file" id="favicon" name="favicon" class="form-control" accept=".ico,.png,.jpg,.jpeg,.gif,.svg">
                                    <small class="form-text">Recommended formats: ICO, PNG, or SVG. Maximum size: 2MB.</small>
                                </div>
                                
                                <button type="submit" name="upload_favicon" class="submit-btn">Upload Favicon</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-sliders-h"></i> General Settings</h2>
                        </div>
                        
                        <div class="content-body">
                            <form action="site-settings.php" method="POST">
                                <div class="form-group">
                                    <label for="site_title">Site Title</label>
                                    <input type="text" id="site_title" name="site_title" class="form-control" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_description">Site Description</label>
                                    <textarea id="site_description" name="site_description" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <hr>
                                <h3>Contact Information</h3>
                                
                                <div class="form-group">
                                    <label for="contact_email">Email Address</label>
                                    <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_phone">Phone Number</label>
                                    <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_address">Office Address</label>
                                    <textarea id="contact_address" name="contact_address" class="form-control" rows="2" required><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                                </div>
                                
                                <hr>
                                <h3>Social Media Links</h3>
                                
                                <div class="form-group">
                                    <label for="social_facebook">Facebook</label>
                                    <div class="input-with-icon">
                                        <i class="fab fa-facebook-f"></i>
                                        <input type="url" id="social_facebook" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_twitter">Twitter</label>
                                    <div class="input-with-icon">
                                        <i class="fab fa-twitter"></i>
                                        <input type="url" id="social_twitter" name="social_twitter" class="form-control" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_linkedin">LinkedIn</label>
                                    <div class="input-with-icon">
                                        <i class="fab fa-linkedin-in"></i>
                                        <input type="url" id="social_linkedin" name="social_linkedin" class="form-control" value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_instagram">Instagram</label>
                                    <div class="input-with-icon">
                                        <i class="fab fa-instagram"></i>
                                        <input type="url" id="social_instagram" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_settings" class="submit-btn">Save Settings</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-file-contract"></i> Legal Documents</h2>
                        </div>
                        
                        <div class="content-body">
                            <form action="site-settings.php" method="POST">
                                <div class="form-group">
                                    <label for="terms_of_service">Terms of Service</label>
                                    <textarea id="terms_of_service" name="terms_of_service" class="form-control" rows="15"><?php echo htmlspecialchars($settings['terms_of_service'] ?? ''); ?></textarea>
                                    <small class="form-text">HTML formatting is supported. This content will be displayed on the Terms of Service page.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="privacy_policy">Privacy Policy</label>
                                    <textarea id="privacy_policy" name="privacy_policy" class="form-control" rows="15"><?php echo htmlspecialchars($settings['privacy_policy'] ?? ''); ?></textarea>
                                    <small class="form-text">HTML formatting is supported. This content will be displayed on the Privacy Policy page.</small>
                                </div>
                                
                                <button type="submit" name="update_legal_documents" class="submit-btn">Save Legal Documents</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    
    <style>
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #0066cc;
    }
    
    .input-with-icon input {
        padding-left: 40px;
    }
    
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
    
    .current-favicon {
        margin-bottom: 25px;
    }
    
    .favicon-preview {
        display: inline-block;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background-color: #f9f9f9;
        min-width: 100px;
        text-align: center;
    }
    
    .favicon-preview img {
        max-width: 64px;
        max-height: 64px;
    }
    
    .no-favicon {
        color: #888;
        font-style: italic;
    }
    </style>
</body>
</html>