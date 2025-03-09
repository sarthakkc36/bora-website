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

// Check if this is a verification submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_verification'])) {
    
    // Check if verification documents were uploaded
    if (isset($_FILES['verification_documents']) && $_FILES['verification_documents']['error'] == 0) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['verification_documents']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only PDF, JPG, and PNG files are allowed.";
        } elseif ($_FILES['verification_documents']['size'] > $max_size) {
            $errors[] = "File size is too large. Maximum size is 5MB.";
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/verification_documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['verification_documents']['name'], PATHINFO_EXTENSION);
            $new_filename = 'verify_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['verification_documents']['tmp_name'], $upload_path)) {
                $verification_documents = $upload_path;
                
                // Update user verification info
                try {
                    $stmt = $pdo->prepare("UPDATE users SET 
                        verification_documents = :verification_documents,
                        verification_request_date = NOW() 
                        WHERE id = :user_id");
                        
                    $stmt->bindParam(':verification_documents', $verification_documents);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    $success = "Your verification documents have been submitted successfully. Our team will review them and verify your account as soon as possible.";
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    $user = $stmt->fetch();
                    
                } catch (PDOException $e) {
                    error_log("Error updating user verification info: " . $e->getMessage());
                    $errors[] = "An error occurred while submitting your verification documents. Please try again.";
                }
                
            } else {
                $errors[] = "Failed to upload verification documents. Please try again.";
            }
        }
    } else {
        $errors[] = "Please upload verification documents.";
    }
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

.verification-docs {
    margin-top: 20px;
}

.file-input-container {
    position: relative;
    overflow: hidden;
    display: inline-block;
    cursor: pointer;
}

.custom-file-input {
    position: absolute;
    font-size: 100px;
    right: 0;
    top: 0;
    opacity: 0;
    cursor: pointer;
}

.file-input-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #0066cc;
    color: white;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.file-input-container:hover .file-input-btn {
    background-color: #0052a3;
}

.file-input-text {
    margin-left: 10px;
}

.doc-preview {
    margin-top: 15px;
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

.requirements {
    background-color: #f0f7ff;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
}

.requirements h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #333;
}

.requirements ul {
    list-style-type: none;
    padding-left: 0;
}

.requirements li {
    margin-bottom: 10px;
    padding-left: 25px;
    position: relative;
}

.requirements li:before {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #0066cc;
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
            
            <div class="verification-container">
                <div class="verification-header">
                    <i class="fas fa-user-clock"></i>
                    <h2>Account Verification Required</h2>
                    <p>Your account needs to be verified by our admin team before you can access all features.</p>
                </div>
                
                <div class="verification-status pending">
                    <h3>
                        <?php if (!empty($user['verification_documents'])): ?>
                            <i class="fas fa-hourglass-half"></i> Verification Pending
                        <?php elseif (isset($user['verification_notes']) && !empty($user['verification_notes']) && $user['is_verified'] == 0): ?>
                            <i class="fas fa-times-circle"></i> Verification Rejected
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle"></i> Verification Required
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (!empty($user['verification_documents'])): ?>
                        <p>Your verification documents have been submitted and are pending review. This usually takes 1-2 business days. You'll receive an email once your account is verified.</p>
                        <p><strong>Submitted on:</strong> <?php echo date('F j, Y', strtotime($user['verification_request_date'])); ?></p>
                    <?php elseif (isset($user['verification_notes']) && !empty($user['verification_notes']) && $user['is_verified'] == 0): ?>
                        <div class="alert alert-danger">
                            <strong>Verification declined:</strong> <?php echo htmlspecialchars($user['verification_notes']); ?>
                        </div>
                        <p>Please submit new verification documents addressing the issues mentioned above.</p>
                    <?php else: ?>
                        <p>Please upload the required documents to verify your account. This is a one-time process to ensure the security of our platform.</p>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($user['verification_documents']) || (isset($user['verification_notes']) && !empty($user['verification_notes']) && $user['is_verified'] == 0)): ?>
                    <form action="verification-pending.php" method="POST" enctype="multipart/form-data">
                        <div class="verification-docs">
                            <h3>Upload Verification Documents</h3>
                            <p>Please upload a clear photo or scan of your government-issued ID (passport, driver's license, etc.).</p>
                            
                            <div class="file-input-container">
                                <div class="file-input-btn">Choose File</div>
                                <input type="file" name="verification_documents" class="custom-file-input" accept=".pdf,.jpg,.jpeg,.png">
                                <span class="file-input-text">No file chosen</span>
                            </div>
                            
                            <div class="doc-preview"></div>
                        </div>
                        
                        <div class="requirements">
                            <h3>Document Requirements</h3>
                            <ul>
                                <li>Government-issued ID (passport, driver's license, national ID)</li>
                                <li>Clear, readable image or PDF</li>
                                <li>All corners of the document must be visible</li>
                                <li>File size must be under 5MB</li>
                                <li>Acceptable formats: PDF, JPG, PNG</li>
                            </ul>
                        </div>
                        
                        <button type="submit" name="submit_verification" class="submit-btn">Submit Verification Documents</button>
                    </form>
                <?php endif; ?>
                
                <div class="verification-steps">
                    <h3>Verification Process</h3>
                    
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-title">Submit Documents</div>
                        <div class="step-desc">Upload the required verification documents.</div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">Admin Review</div>
                        <div class="step-desc">Our admin team will review your documents within 1-2 business days.</div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">Account Verification</div>
                        <div class="step-desc">Once verified, you'll receive an email and can access all features.</div>
                    </div>
                </div>
                
                <div class="verification-footer">
                    <p>If you have any questions or need assistance, please contact our support team.</p>
                    <div class="contact-info">
                        <i class="fas fa-envelope"></i> support@bhemployment.com
                        <i class="fas fa-phone ml-3"></i> (123) 456-7890
                    </div>
                </div>
                
                <div class="logout-link">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // File input preview
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('.custom-file-input');
            const fileInputText = document.querySelector('.file-input-text');
            const docPreview = document.querySelector('.doc-preview');
            
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const fileName = this.files[0].name;
                        fileInputText.textContent = fileName;
                        
                        // Show file preview for images
                        if (this.files[0].type.match('image.*')) {
                            const reader = new FileReader();
                            
                            reader.onload = function(e) {
                                docPreview.innerHTML = `
                                    <div style="margin-top: 15px;">
                                        <p><strong>Preview:</strong></p>
                                        <img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>
                                `;
                            }
                            
                            reader.readAsDataURL(this.files[0]);
                        } else {
                            docPreview.innerHTML = `
                                <div style="margin-top: 15px;">
                                    <p><strong>Selected file:</strong> ${fileName}</p>
                                </div>
                            `;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>