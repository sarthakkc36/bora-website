<?php
require_once '../config.php';

// Check if user is logged in and is an employer
if (!isLoggedIn() || !isEmployer()) {
    flashMessage("You must be logged in as an employer to post jobs", "danger");
    redirect('../login.php');
}

$errors = [];
$success = '';

// Get employer information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $employer = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching employer information: " . $e->getMessage());
    $employer = ['company_name' => ''];
}

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
    $application_instructions = sanitizeInput($_POST['application_instructions']);
    $contact_email = sanitizeInput($_POST['contact_email']);
    $contact_phone = sanitizeInput($_POST['contact_phone']);
    
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
    
    if (empty($contact_email)) {
        $errors[] = "Contact email is required";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid contact email format";
    }
    
    if (!empty($salary_min) && !empty($salary_max) && $salary_min > $salary_max) {
        $errors[] = "Minimum salary cannot be greater than maximum salary";
    }
    
    // If no errors, save the job posting
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO jobs (user_id, company_name, title, description, requirements, location, job_type, 
                                   salary_min, salary_max, experience_level, application_instructions, contact_email, contact_phone)
                                   VALUES (:user_id, :company_name, :title, :description, :requirements, :location, :job_type,
                                   :salary_min, :salary_max, :experience_level, :application_instructions, :contact_email, :contact_phone)");
            
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':job_type', $job_type);
            $stmt->bindParam(':salary_min', $salary_min);
            $stmt->bindParam(':salary_max', $salary_max);
            $stmt->bindParam(':experience_level', $experience_level);
            $stmt->bindParam(':application_instructions', $application_instructions);
            $stmt->bindParam(':contact_email', $contact_email);
            $stmt->bindParam(':contact_phone', $contact_phone);
            
            $stmt->execute();
            
            $success = "Job posted successfully!";
            
            // Reset form fields
            $title = $location = $job_type = $experience_level = $description = $requirements = $application_instructions = $contact_phone = '';
            $salary_min = $salary_max = null;
            $contact_email = $employer['email'] ?? '';
            
        } catch (PDOException $e) {
            error_log("Error posting job: " . $e->getMessage());
            $errors[] = "An error occurred while posting the job. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Post a Job</h1>
            <p>Create a job listing to attract qualified candidates</p>
        </div>
    </section>

    <section class="post-job-section">
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
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-plus-circle"></i> Create New Job Posting</h2>
                        </div>
                        
                        <div class="content-body">
                            <form action="post-job.php" method="POST">
                                <div class="form-group">
                                    <label for="title">Job Title</label>
                                    <input type="text" id="title" name="title" class="form-control" value="<?php echo $title ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo $company_name ?? $employer['company_name']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" value="<?php echo $location ?? ''; ?>" required>
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
                                        <input type="number" id="salary_min" name="salary_min" class="form-control" value="<?php echo $salary_min ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="salary_max">Maximum Salary (Optional)</label>
                                        <input type="number" id="salary_max" name="salary_max" class="form-control" value="<?php echo $salary_max ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Job Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="8" required><?php echo $description ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="requirements">Job Requirements</label>
                                    <textarea id="requirements" name="requirements" class="form-control" rows="6" required><?php echo $requirements ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="application_instructions">Application Instructions (Optional)</label>
                                    <textarea id="application_instructions" name="application_instructions" class="form-control" rows="4"><?php echo $application_instructions ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="contact_email">Contact Email</label>
                                        <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo $contact_email ?? $employer['email']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="contact_phone">Contact Phone (Optional)</label>
                                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo $contact_phone ?? $employer['phone']; ?>">
                                    </div>
                                </div>
                                
                                <button type="submit" class="submit-btn">Post Job</button>
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