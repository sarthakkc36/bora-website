<?php
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = sanitizeInput($_POST['title']);
    $company_name = sanitizeInput($_POST['company_name']);
    $location = sanitizeInput($_POST['location']);
    $job_type = sanitizeInput($_POST['job_type']);
    $experience_level = sanitizeInput($_POST['experience_level']);
    $salary_min = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
    $salary_max = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
    $description = sanitizeInput($_POST['description']);
    $requirements = sanitizeInput($_POST['requirements']);
    $submitter_name = sanitizeInput($_POST['submitter_name']);
    $submitter_email = sanitizeInput($_POST['submitter_email']);
    $submitter_phone = sanitizeInput($_POST['submitter_phone']);
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = "Job title is required";
    }
    
    if (empty($company_name)) {
        $errors[] = "Company name is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($job_type)) {
        $errors[] = "Job type is required";
    }
    
    if (empty($experience_level)) {
        $errors[] = "Experience level is required";
    }
    
    if (empty($description)) {
        $errors[] = "Job description is required";
    }
    
    if (empty($requirements)) {
        $errors[] = "Job requirements are required";
    }
    
    if (empty($submitter_name)) {
        $errors[] = "Your name is required";
    }
    
    if (empty($submitter_email)) {
        $errors[] = "Your email is required";
    } elseif (!filter_var($submitter_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($salary_min) && !empty($salary_max) && $salary_min > $salary_max) {
        $errors[] = "Minimum salary cannot be greater than maximum salary";
    }
    
    // If no errors, save the job posting
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jobs (company_name, title, description, requirements, location, job_type, 
                               salary_min, salary_max, experience_level, submitter_name, submitter_email, submitter_phone, approval_status)
                               VALUES (:company_name, :title, :description, :requirements, :location, :job_type,
                               :salary_min, :salary_max, :experience_level, :submitter_name, :submitter_email, :submitter_phone, 'pending')");
            
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':job_type', $job_type);
            $stmt->bindParam(':salary_min', $salary_min);
            $stmt->bindParam(':salary_max', $salary_max);
            $stmt->bindParam(':experience_level', $experience_level);
            $stmt->bindParam(':submitter_name', $submitter_name);
            $stmt->bindParam(':submitter_email', $submitter_email);
            $stmt->bindParam(':submitter_phone', $submitter_phone);
            
            $stmt->execute();
            
            // Send notification email to admin
            $admin_email = "admin@bhemployment.com"; // Change to your admin email
            $subject = "New Job Submission: " . $title;
            $message = "<p>A new job has been submitted and is pending approval:</p>";
            $message .= "<p><strong>Job Title:</strong> " . htmlspecialchars($title) . "</p>";
            $message .= "<p><strong>Company:</strong> " . htmlspecialchars($company_name) . "</p>";
            $message .= "<p><strong>Submitted By:</strong> " . htmlspecialchars($submitter_name) . " (" . htmlspecialchars($submitter_email) . ")</p>";
            $message .= "<p>Please login to the admin panel to review this submission.</p>";
            
            sendEmail($admin_email, $subject, $message);
            
            $success = "Thank you for submitting your job! Our team will review it and it will be published once approved.";
            
            // Reset form fields
            $title = $company_name = $location = $job_type = $experience_level = $description = $requirements = $submitter_name = $submitter_email = $submitter_phone = '';
            $salary_min = $salary_max = null;
            
        } catch (PDOException $e) {
            error_log("Error posting job: " . $e->getMessage());
            $errors[] = "An error occurred while submitting the job. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Job - B&H Employment & Consultancy Inc</title>
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
            <h1>Submit a Job</h1>
            <p>Share your job opportunity with our network of qualified candidates</p>
        </div>
    </section>

    <section class="post-job-section">
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
            
            <div class="content-box">
                <div class="content-header">
                    <h2><i class="fas fa-plus-circle"></i> Submit Job Posting</h2>
                </div>
                
                <div class="content-body">
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> Job listings will be reviewed by our team before being published. Please ensure all information is accurate.</p>
                    </div>
                    
                    <form action="submit-job.php" method="POST">
                        <h3>Job Details</h3>
                        <div class="form-group">
                            <label for="title">Job Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo isset($company_name) ? htmlspecialchars($company_name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" class="form-control" value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="job_type">Job Type</label>
                                <select id="job_type" name="job_type" class="form-control" required>
                                    <option value="">Select Job Type</option>
                                    <option value="full-time" <?php echo isset($job_type) && $job_type === 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="part-time" <?php echo isset($job_type) && $job_type === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="contract" <?php echo isset($job_type) && $job_type === 'contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="temporary" <?php echo isset($job_type) && $job_type === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                                    <option value="internship" <?php echo isset($job_type) && $job_type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                            
                            <div class="form-group half">
                                <label for="experience_level">Experience Level</label>
                                <select id="experience_level" name="experience_level" class="form-control" required>
                                    <option value="">Select Experience Level</option>
                                    <option value="entry" <?php echo isset($experience_level) && $experience_level === 'entry' ? 'selected' : ''; ?>>Entry Level</option>
                                    <option value="mid" <?php echo isset($experience_level) && $experience_level === 'mid' ? 'selected' : ''; ?>>Mid Level</option>
                                    <option value="senior" <?php echo isset($experience_level) && $experience_level === 'senior' ? 'selected' : ''; ?>>Senior Level</option>
                                    <option value="executive" <?php echo isset($experience_level) && $experience_level === 'executive' ? 'selected' : ''; ?>>Executive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="salary_min">Minimum Salary (Optional)</label>
                                <input type="number" id="salary_min" name="salary_min" class="form-control" value="<?php echo isset($salary_min) ? htmlspecialchars($salary_min) : ''; ?>">
                            </div>
                            
                            <div class="form-group half">
                                <label for="salary_max">Maximum Salary (Optional)</label>
                                <input type="number" id="salary_max" name="salary_max" class="form-control" value="<?php echo isset($salary_max) ? htmlspecialchars($salary_max) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Job Description</label>
                            <textarea id="description" name="description" class="form-control" rows="8" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="requirements">Job Requirements</label>
                            <textarea id="requirements" name="requirements" class="form-control" rows="6" required><?php echo isset($requirements) ? htmlspecialchars($requirements) : ''; ?></textarea>
                        </div>
                        
                        <h3>Contact Information</h3>
                        <div class="form-group">
                            <label for="submitter_name">Your Name</label>
                            <input type="text" id="submitter_name" name="submitter_name" class="form-control" value="<?php echo isset($submitter_name) ? htmlspecialchars($submitter_name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="submitter_email">Your Email</label>
                                <input type="email" id="submitter_email" name="submitter_email" class="form-control" value="<?php echo isset($submitter_email) ? htmlspecialchars($submitter_email) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group half">
                                <label for="submitter_phone">Your Phone (Optional)</label>
                                <input type="tel" id="submitter_phone" name="submitter_phone" class="form-control" value="<?php echo isset($submitter_phone) ? htmlspecialchars($submitter_phone) : ''; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="submit-btn">Submit Job</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>