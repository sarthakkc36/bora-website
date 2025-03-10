<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    flashMessage("No job ID provided", "danger");
    redirect('manage-jobs.php');
}

$job_id = (int)$_GET['id'];

// Handle form submission for editing job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_job'])) {
    // Get form data
    $title = sanitizeInput($_POST['title']);
    $company_name = sanitizeInput($_POST['company_name']);
    $location = sanitizeInput($_POST['location']);
    $job_type = sanitizeInput($_POST['job_type']);
    $experience_level = sanitizeInput($_POST['experience_level']);
    $salary_min = !empty($_POST['salary_min']) ? (int)$_POST['salary_min'] : null;
    $salary_max = !empty($_POST['salary_max']) ? (int)$_POST['salary_max'] : null;
    $description = sanitizeInput($_POST['description']);
    $requirements = sanitizeInput($_POST['requirements']);
    $application_instructions = sanitizeInput($_POST['application_instructions'] ?? '');
    $contact_email = sanitizeInput($_POST['contact_email'] ?? '');
    $contact_phone = sanitizeInput($_POST['contact_phone'] ?? '');
    $admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
    
    // Validate form data
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Job title is required";
    }
    
    if (empty($company_name)) {
        $errors[] = "Company name is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($description)) {
        $errors[] = "Job description is required";
    }
    
    if (empty($requirements)) {
        $errors[] = "Job requirements are required";
    }
    
    // Process the job update if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE jobs SET 
                                  title = :title,
                                  company_name = :company_name,
                                  location = :location,
                                  job_type = :job_type,
                                  experience_level = :experience_level,
                                  salary_min = :salary_min,
                                  salary_max = :salary_max,
                                  description = :description,
                                  requirements = :requirements,
                                  application_instructions = :application_instructions,
                                  contact_email = :contact_email,
                                  contact_phone = :contact_phone,
                                  admin_notes = :admin_notes
                                  WHERE id = :job_id");
                                  
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
            $stmt->bindParam(':admin_notes', $admin_notes);
            $stmt->bindParam(':job_id', $job_id);
            $stmt->execute();
            
            flashMessage("Job updated successfully", "success");
            
            // After update, decide whether to approve immediately or go back to job list
            if (isset($_POST['approve_after_edit'])) {
                // Update job status to approved
                $stmt = $pdo->prepare("UPDATE jobs SET approval_status = 'approved' WHERE id = :job_id");
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                
                // Get job details for email notification
                $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
                $stmt->bindParam(':job_id', $job_id);
                $stmt->execute();
                $job = $stmt->fetch();
                
                // Send email notification to submitter
                if (!empty($job['submitter_email'])) {
                    $subject = "Job Posting Approved: " . $job['title'];
                    $message = "<p>Dear " . htmlspecialchars($job['submitter_name']) . ",</p>";
                    $message .= "<p>Good news! Your job posting for <strong>" . htmlspecialchars($job['title']) . "</strong> at <strong>" . htmlspecialchars($job['company_name']) . "</strong> has been approved and is now live on our website.</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Admin Notes:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>Note: Some details may have been edited or formatted for clarity.</p>";
                    $message .= "<p>Thank you for using our services!</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($job['submitter_email'], $subject, $message);
                }
                
                flashMessage("Job approved and published successfully", "success");
            }
            
            redirect('manage-jobs.php');
            
        } catch (PDOException $e) {
            error_log("Error updating job: " . $e->getMessage());
            $errors[] = "An error occurred while updating the job. Please try again.";
        }
    }
}

// Get job details
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :job_id");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        flashMessage("Job not found", "danger");
        redirect('manage-jobs.php');
    }
    
    $job = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching job details: " . $e->getMessage());
    flashMessage("An error occurred while fetching job details", "danger");
    redirect('manage-jobs.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - B&H Employment & Consultancy Inc</title>
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
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Edit Job Details</h1>
            <p>Review and modify job posting before approval</p>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <?php include 'sidebar.php'; ?>
                </div>
                
                <div class="dashboard-content">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php displayFlashMessage(); ?>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-edit"></i> Edit Job</h2>
                            <a href="manage-jobs.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
                        </div>
                        
                        <div class="content-body">
                            <form action="edit-job.php?id=<?php echo $job_id; ?>" method="POST">
                                <div class="form-section">
                                    <h3 class="form-section-title">Basic Information</h3>
                                    
                                    <div class="form-group">
                                        <label for="title">Job Title</label>
                                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="company_name">Company Name</label>
                                            <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($job['company_name']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="location">Location</label>
                                            <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="job_type">Job Type</label>
                                            <select id="job_type" name="job_type" class="form-control" required>
                                                <option value="full-time" <?php echo $job['job_type'] === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                                                <option value="part-time" <?php echo $job['job_type'] === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                                                <option value="contract" <?php echo $job['job_type'] === 'contract' ? 'selected' : ''; ?>>Contract</option>
                                                <option value="temporary" <?php echo $job['job_type'] === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                                                <option value="internship" <?php echo $job['job_type'] === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                                <option value="remote" <?php echo $job['job_type'] === 'remote' ? 'selected' : ''; ?>>Remote</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="experience_level">Experience Level</label>
                                            <select id="experience_level" name="experience_level" class="form-control" required>
                                                <option value="entry" <?php echo $job['experience_level'] === 'entry' ? 'selected' : ''; ?>>Entry Level</option>
                                                <option value="mid" <?php echo $job['experience_level'] === 'mid' ? 'selected' : ''; ?>>Mid Level</option>
                                                <option value="senior" <?php echo $job['experience_level'] === 'senior' ? 'selected' : ''; ?>>Senior Level</option>
                                                <option value="executive" <?php echo $job['experience_level'] === 'executive' ? 'selected' : ''; ?>>Executive Level</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="salary_min">Salary Minimum (Optional)</label>
                                            <input type="number" id="salary_min" name="salary_min" class="form-control" value="<?php echo $job['salary_min']; ?>">
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="salary_max">Salary Maximum (Optional)</label>
                                            <input type="number" id="salary_max" name="salary_max" class="form-control" value="<?php echo $job['salary_max']; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Job Details</h3>
                                    
                                    <div class="form-group">
                                        <label for="description">Job Description</label>
                                        <textarea id="description" name="description" class="form-control" rows="8" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="requirements">Job Requirements</label>
                                        <textarea id="requirements" name="requirements" class="form-control" rows="8" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="application_instructions">Application Instructions (Optional)</label>
                                        <textarea id="application_instructions" name="application_instructions" class="form-control" rows="4"><?php echo htmlspecialchars($job['application_instructions']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Contact Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="contact_email">Contact Email (Optional)</label>
                                            <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($job['contact_email']); ?>">
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label for="contact_phone">Contact Phone (Optional)</label>
                                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($job['contact_phone']); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Admin Notes</h3>
                                    
                                    <div class="form-group">
                                        <label for="admin_notes">Notes (Visible to admin only)</label>
                                        <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4"><?php echo htmlspecialchars($job['admin_notes']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Submitter Information (Not Editable)</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label>Submitter Name</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($job['submitter_name']); ?>" disabled>
                                        </div>
                                        
                                        <div class="form-group half">
                                            <label>Submitter Email</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($job['submitter_email']); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="edit_job" class="submit-btn">Save Changes</button>
                                    <button type="submit" name="edit_job" value="1" class="btn-primary" onclick="this.form.approve_after_edit.value='1';">Save and Approve</button>
                                    <input type="hidden" name="approve_after_edit" value="0">
                                    <a href="manage-jobs.php" class="cancel-btn">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/script.js"></script>
</body>
</html>