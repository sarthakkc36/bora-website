<?php
require_once 'config.php';

// Initialize variables
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_job'])) {
    // Get form data
    $title = sanitizeInput($_POST['title'] ?? '');
    $company_name = sanitizeInput($_POST['company_name'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $job_type = sanitizeInput($_POST['job_type'] ?? '');
    $experience_level = sanitizeInput($_POST['experience_level'] ?? '');
    $salary_min = !empty($_POST['salary_min']) ? (int)$_POST['salary_min'] : null;
    $salary_max = !empty($_POST['salary_max']) ? (int)$_POST['salary_max'] : null;
    $description = sanitizeInput($_POST['description'] ?? '');
    $requirements = sanitizeInput($_POST['requirements'] ?? '');
    $application_instructions = sanitizeInput($_POST['application_instructions'] ?? '');
    $contact_email = sanitizeInput($_POST['contact_email'] ?? '');
    $contact_phone = sanitizeInput($_POST['contact_phone'] ?? '');
    
    // Get submitter information
    $submitter_name = sanitizeInput($_POST['submitter_name'] ?? '');
    $submitter_email = sanitizeInput($_POST['submitter_email'] ?? '');
    $submitter_phone = sanitizeInput($_POST['submitter_phone'] ?? '');
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = "Job title is required";
    }
    
    if (empty($company_name)) {
        $errors[] = "Company name is required";
    }
    
    if (empty($location)) {
        $errors[] = "Job location is required";
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
        $errors[] = "Please enter a valid email address";
    }
    
    // Process job submission if no errors
    if (empty($errors)) {
        try {
            // Always set initial approval status to 'pending'
            $approval_status = 'pending';
            $is_active = 1; // Default to active
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null; // Set user_id only if logged in
            
            // Insert into database - Fixed SQL query with properly bound parameters
            $stmt = $pdo->prepare("INSERT INTO jobs 
                (title, company_name, location, job_type, experience_level, 
                salary_min, salary_max, description, requirements, application_instructions, 
                contact_email, contact_phone, user_id, approval_status, is_active, 
                submitter_name, submitter_email, submitter_phone) 
                VALUES 
                (:title, :company_name, :location, :job_type, :experience_level, 
                :salary_min, :salary_max, :description, :requirements, :application_instructions, 
                :contact_email, :contact_phone, :user_id, :approval_status, :is_active, 
                :submitter_name, :submitter_email, :submitter_phone)");
                                  
            // Bind parameters 
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':job_type', $job_type);
            $stmt->bindParam(':experience_level', $experience_level);
            $stmt->bindParam(':salary_min', $salary_min);
            $stmt->bindParam(':salary_max', $salary_max);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':application_instructions', $application_instructions);
            $stmt->bindParam(':contact_email', $contact_email);
            $stmt->bindParam(':contact_phone', $contact_phone);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':approval_status', $approval_status);
            $stmt->bindParam(':is_active', $is_active);
            $stmt->bindParam(':submitter_name', $submitter_name);
            $stmt->bindParam(':submitter_email', $submitter_email);
            $stmt->bindParam(':submitter_phone', $submitter_phone);
            
            // Execute the query
            $stmt->execute();
            
            // Get the job ID
            $job_id = $pdo->lastInsertId();
            
            // Notify admin of new job posting
            // Get admin email(s)
            $admin_emails = [];
            
            try {
                $admin_stmt = $pdo->prepare("SELECT email FROM users WHERE role = 'admin'");
                $admin_stmt->execute();
                
                while ($admin = $admin_stmt->fetch()) {
                    $admin_emails[] = $admin['email'];
                }
            } catch (PDOException $e) {
                error_log("Error fetching admin emails: " . $e->getMessage());
            }
            
            // Send notification if we have admin emails
            if (!empty($admin_emails)) {
                $admin_subject = "New Job Posting Requires Approval: " . $title;
                $admin_message = "<p>A new job posting has been submitted and requires your approval:</p>";
                $admin_message .= "<p><strong>Job Title:</strong> " . htmlspecialchars($title) . "</p>";
                $admin_message .= "<p><strong>Company:</strong> " . htmlspecialchars($company_name) . "</p>";
                $admin_message .= "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>";
                $admin_message .= "<p><strong>Submitted By:</strong> " . htmlspecialchars($submitter_name) . " (" . htmlspecialchars($submitter_email) . ")</p>";
                $admin_message .= "<p><a href='" . $site_url . "admin/edit-job.php?id=" . $job_id . "'>Click here to review the job posting</a></p>";
                
                foreach ($admin_emails as $admin_email) {
                    sendEmail($admin_email, $admin_subject, $admin_message);
                }
            }
            
            // Send confirmation email to submitter
            $submitter_subject = "Job Posting Submitted Successfully: " . $title;
            $submitter_message = "<p>Dear " . htmlspecialchars($submitter_name) . ",</p>";
            $submitter_message .= "<p>Thank you for submitting your job posting for <strong>" . htmlspecialchars($title) . "</strong>.</p>";
            $submitter_message .= "<p>Your submission is currently being reviewed by our team. We will notify you once it has been approved.</p>";
            $submitter_message .= "<p>Job Posting Details:</p>";
            $submitter_message .= "<ul>";
            $submitter_message .= "<li><strong>Job Title:</strong> " . htmlspecialchars($title) . "</li>";
            $submitter_message .= "<li><strong>Company:</strong> " . htmlspecialchars($company_name) . "</li>";
            $submitter_message .= "<li><strong>Location:</strong> " . htmlspecialchars($location) . "</li>";
            $submitter_message .= "</ul>";
            $submitter_message .= "<p>If you have any questions, please contact our support team.</p>";
            $submitter_message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
            
            sendEmail($submitter_email, $submitter_subject, $submitter_message);
            
            // Set success message
            $success = "Thank you for submitting your job posting. It will be reviewed by our team and published soon.";
            
            // Reset form fields
            $title = $company_name = $location = $description = $requirements = $application_instructions = $contact_email = $contact_phone = '';
            $submitter_name = $submitter_email = $submitter_phone = '';
            $salary_min = $salary_max = null;
            $job_type = 'full-time';
            $experience_level = 'entry';
            
        } catch (PDOException $e) {
            error_log("Error submitting job: " . $e->getMessage());
            $errors[] = "An error occurred while submitting your job posting. Please try again.";
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
<style>
    .submit-job-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .form-section {
        margin-bottom: 30px;
    }
    
    .form-section-title {
        font-size: 20px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    
    .privacy-notice,
    .approval-notice {
        display: flex;
        background-color: #f0f7ff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        align-items: center;
        border-left: 3px solid #0066cc;
    }
    
    .approval-notice {
        background-color: #fff8e6;
        border-left-color: #f8bb86;
    }
    
    .privacy-notice i,
    .approval-notice i {
        font-size: 24px;
        margin-right: 15px;
    }
    
    .privacy-notice i {
        color: #0066cc;
    }
    
    .approval-notice i {
        color: #f8bb86;
    }
    
    .privacy-notice h3,
    .approval-notice h3 {
        margin-top: 0;
        margin-bottom: 5px;
    }
    
    .privacy-notice h3 {
        color: #0066cc;
    }
    
    .approval-notice h3 {
        color: #e69500;
    }
    
    .privacy-notice p,
    .approval-notice p {
        margin-bottom: 0;
        color: #333;
    }
    
    .form-text {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .form-note {
        font-style: italic;
        color: #666;
        margin-bottom: 15px;
    }
</style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Submit a Job</h1>
            <p>Post a new job opportunity on our platform</p>
        </div>
    </section>

    <section class="submit-job-section">
        <div class="container">
            <div class="submit-job-container">
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
                        <h2><i class="fas fa-plus-circle"></i> Post New Job</h2>
                    </div>
                    
                    <div class="content-body">
                        <div class="privacy-notice">
                            <i class="fas fa-lock"></i>
                            <div>
                                <h3>Confidential Hiring Process</h3>
                                <p>Company names and locations are hidden from job seekers to protect client privacy. Job seekers will only see your position details until they pass initial screening.</p>
                            </div>
                        </div>
                        
                        <div class="approval-notice">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <h3>Approval Required</h3>
                                <p>All job postings require approval by our team before they appear on the site. This helps maintain quality and legitimacy. The approval process typically takes 24-48 hours.</p>
                            </div>
                        </div>
                        
                        <form action="submit-job.php" method="POST">
                            <div class="form-section">
                                <h3 class="form-section-title">Your Contact Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="submitter_name">Your Name</label>
                                        <input type="text" id="submitter_name" name="submitter_name" class="form-control" value="<?php echo isset($submitter_name) ? htmlspecialchars($submitter_name) : (isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="submitter_email">Your Email</label>
                                        <input type="email" id="submitter_email" name="submitter_email" class="form-control" value="<?php echo isset($submitter_email) ? htmlspecialchars($submitter_email) : (isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="submitter_phone">Your Phone Number (Optional)</label>
                                    <input type="tel" id="submitter_phone" name="submitter_phone" class="form-control" value="<?php echo isset($submitter_phone) ? htmlspecialchars($submitter_phone) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Basic Job Information</h3>
                                
                                <div class="form-group">
                                    <label for="title">Job Title</label>
                                    <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo isset($company_name) ? htmlspecialchars($company_name) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="location">Location</label>
                                        <input type="text" id="location" name="location" class="form-control" value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="job_type">Job Type</label>
                                        <select id="job_type" name="job_type" class="form-control" required>
                                            <option value="full-time" <?php echo (isset($job_type) && $job_type === 'full-time') ? 'selected' : ''; ?>>Full Time</option>
                                            <option value="part-time" <?php echo (isset($job_type) && $job_type === 'part-time') ? 'selected' : ''; ?>>Part Time</option>
                                            <option value="contract" <?php echo (isset($job_type) && $job_type === 'contract') ? 'selected' : ''; ?>>Contract</option>
                                            <option value="temporary" <?php echo (isset($job_type) && $job_type === 'temporary') ? 'selected' : ''; ?>>Temporary</option>
                                            <option value="internship" <?php echo (isset($job_type) && $job_type === 'internship') ? 'selected' : ''; ?>>Internship</option>
                                            <option value="remote" <?php echo (isset($job_type) && $job_type === 'remote') ? 'selected' : ''; ?>>Remote</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="experience_level">Experience Level</label>
                                        <select id="experience_level" name="experience_level" class="form-control" required>
                                            <option value="entry" <?php echo (isset($experience_level) && $experience_level === 'entry') ? 'selected' : ''; ?>>Entry Level</option>
                                            <option value="mid" <?php echo (isset($experience_level) && $experience_level === 'mid') ? 'selected' : ''; ?>>Mid Level</option>
                                            <option value="senior" <?php echo (isset($experience_level) && $experience_level === 'senior') ? 'selected' : ''; ?>>Senior Level</option>
                                            <option value="executive" <?php echo (isset($experience_level) && $experience_level === 'executive') ? 'selected' : ''; ?>>Executive Level</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="salary_min">Salary Minimum (Optional)</label>
                                        <input type="number" id="salary_min" name="salary_min" class="form-control" value="<?php echo isset($salary_min) ? $salary_min : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="salary_max">Salary Maximum (Optional)</label>
                                        <input type="number" id="salary_max" name="salary_max" class="form-control" value="<?php echo isset($salary_max) ? $salary_max : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Job Details</h3>
                                
                                <div class="form-group">
                                    <label for="description">Job Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="8" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                    <small class="form-text">Provide a detailed description of the job, including responsibilities and day-to-day tasks.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="requirements">Job Requirements</label>
                                    <textarea id="requirements" name="requirements" class="form-control" rows="8" required><?php echo isset($requirements) ? htmlspecialchars($requirements) : ''; ?></textarea>
                                    <small class="form-text">List qualifications, skills, experience, and education requirements for the position.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="application_instructions">Application Instructions (Optional)</label>
                                    <textarea id="application_instructions" name="application_instructions" class="form-control" rows="4"><?php echo isset($application_instructions) ? htmlspecialchars($application_instructions) : ''; ?></textarea>
                                    <small class="form-text">Provide any specific instructions for applicants (e.g., documents to include, application process).</small>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Additional Contact Information (Optional)</h3>
                                <p class="form-note">These details can be different from your personal contact information and will be shown to job applicants after screening.</p>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="contact_email">Contact Email for Applicants</label>
                                        <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo isset($contact_email) ? htmlspecialchars($contact_email) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="contact_phone">Contact Phone for Applicants</label>
                                        <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo isset($contact_phone) ? htmlspecialchars($contact_phone) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group terms-check">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I confirm that all the information provided is accurate and I am authorized to post this job.</label>
                            </div>
                            
                            <button type="submit" name="submit_job" class="submit-btn">Submit Job Posting</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>