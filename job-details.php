<?php
require_once 'config.php';

// Check if job ID is provided
if (!isset($_GET['id'])) {
    redirect('jobs.php');
}

$job_id = (int)$_GET['id'];

// Fetch job details
try {
    $stmt = $pdo->prepare("SELECT j.*, u.email as employer_email, u.company_name as company
                          FROM jobs j
                          LEFT JOIN users u ON j.user_id = u.id
                          WHERE j.id = :job_id AND j.is_active = 1");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        flashMessage("Job not found or no longer active", "danger");
        redirect('jobs.php');
    }
    
    $job = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching job details: " . $e->getMessage());
    flashMessage("An error occurred while fetching the job details", "danger");
    redirect('jobs.php');
}

// Check if the job is saved by the current user
$is_saved = false;
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        $is_saved = ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error checking saved job: " . $e->getMessage());
    }
    
    // Check if the user has already applied for this job
    try {
        $stmt = $pdo->prepare("SELECT status FROM job_applications WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        $has_applied = ($stmt->rowCount() > 0);
        $application_status = $has_applied ? $stmt->fetch()['status'] : null;
    } catch (PDOException $e) {
        error_log("Error checking job application: " . $e->getMessage());
        $has_applied = false;
        $application_status = null;
    }
}

// Handle job application submission
$application_error = '';
$application_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_submit']) && isLoggedIn() && isJobSeeker()) {
    // Check if file was uploaded without errors
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed_ext = ['pdf', 'doc', 'docx'];
        $file_name = $_FILES['resume']['name'];
        $file_size = $_FILES['resume']['size'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (in_array($file_ext, $allowed_ext)) {
            // Validate file size (max 5MB)
            if ($file_size <= 5242880) {
                // Create unique filename
                $new_file_name = 'resume_' . $_SESSION['user_id'] . '_' . $job_id . '_' . time() . '.' . $file_ext;
                $upload_path = 'uploads/resumes/' . $new_file_name;
                
                // Create directory if it doesn't exist
                if (!file_exists('uploads/resumes')) {
                    mkdir('uploads/resumes', 0777, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $cover_letter = sanitizeInput($_POST['cover_letter']);
                    
                    try {
                        // Insert application into database
                        $stmt = $pdo->prepare("INSERT INTO job_applications (job_id, user_id, resume_path, cover_letter) 
                                              VALUES (:job_id, :user_id, :resume_path, :cover_letter)");
                        $stmt->bindParam(':job_id', $job_id);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->bindParam(':resume_path', $upload_path);
                        $stmt->bindParam(':cover_letter', $cover_letter);
                        $stmt->execute();
                        
                        // Update application count for the job
                        $stmt = $pdo->prepare("UPDATE jobs SET applications = applications + 1 WHERE id = :job_id");
                        $stmt->bindParam(':job_id', $job_id);
                        $stmt->execute();
                        
                        $application_success = "Your application has been submitted successfully!";
                        $has_applied = true;
                        $application_status = 'pending';
                    } catch (PDOException $e) {
                        error_log("Error submitting job application: " . $e->getMessage());
                        $application_error = "An error occurred while submitting your application. Please try again.";
                    }
                } else {
                    $application_error = "Failed to upload resume. Please try again.";
                }
            } else {
                $application_error = "Resume file is too large. Maximum size is 5MB.";
            }
        } else {
            $application_error = "Invalid file type. Allowed types: PDF, DOC, DOCX";
        }
    } else {
        $application_error = "Please upload your resume.";
    }
}

// Format currency
function formatSalary($min, $max) {
    if (!empty($min) && !empty($max)) {
        return '$' . number_format($min) . ' - $' . number_format($max);
    } elseif (!empty($min)) {
        return 'From $' . number_format($min);
    } elseif (!empty($max)) {
        return 'Up to $' . number_format($max);
    } else {
        return 'Not specified';
    }
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Get related jobs
try {
    $stmt = $pdo->prepare("SELECT * FROM jobs 
                          WHERE is_active = 1 
                          AND id != :job_id 
                          AND (title LIKE :title OR experience_level = :experience_level OR job_type = :job_type)
                          ORDER BY created_at DESC
                          LIMIT 3");
    $title_search = '%' . substr($job['title'], 0, 20) . '%';
    $stmt->bindParam(':job_id', $job_id);
    $stmt->bindParam(':title', $title_search);
    $stmt->bindParam(':experience_level', $job['experience_level']);
    $stmt->bindParam(':job_type', $job['job_type']);
    $stmt->execute();
    
    $related_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching related jobs: " . $e->getMessage());
    $related_jobs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Job Details Section -->
    <section class="job-details">
        <div class="container">
            <div class="job-details-header">
                <div class="job-title-container">
                    <h1><?php echo htmlspecialchars($job['title']); ?></h1>
                    <div class="job-company">
                        <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                        <span class="job-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                    </div>
                    <div class="job-tags">
                        <span class="job-tag"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                        <span class="job-tag"><?php echo ucfirst($job['experience_level']) . ' Level'; ?></span>
                    </div>
                </div>
                <div class="job-actions">
                    <?php if (isLoggedIn() && isJobSeeker() && !$has_applied): ?>
                        <a href="#apply-section" class="apply-btn"><i class="fas fa-paper-plane"></i> Apply Now</a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="job-save" data-job-id="<?php echo $job['id']; ?>">
                            <i class="<?php echo $is_saved ? 'fas' : 'far'; ?> fa-bookmark"></i>
                            <span><?php echo $is_saved ? 'Saved' : 'Save Job'; ?></span>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="job-save">
                            <i class="far fa-bookmark"></i> Save Job
                        </a>
                    <?php endif; ?>
                    
                    <button class="share-btn" onclick="toggleShareOptions()">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <div class="share-options" id="shareOptions">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($site_url . '/job-details.php?id=' . $job['id']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($site_url . '/job-details.php?id=' . $job['id']); ?>&text=<?php echo urlencode('Check out this job: ' . $job['title']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($site_url . '/job-details.php?id=' . $job['id']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <a href="mailto:?subject=<?php echo urlencode('Job Opportunity: ' . $job['title']); ?>&body=<?php echo urlencode('Check out this job posting: ' . $site_url . '/job-details.php?id=' . $job['id']); ?>"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="job-details-content">
                <div class="job-main-content">
                    <div class="job-overview">
                        <h2>Job Overview</h2>
                        <div class="overview-items">
                            <div class="overview-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <span class="label">Date Posted</span>
                                    <span class="value"><?php echo formatDate($job['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="overview-item">
                                <i class="fas fa-dollar-sign"></i>
                                <div>
                                    <span class="label">Salary</span>
                                    <span class="value"><?php echo formatSalary($job['salary_min'], $job['salary_max']); ?></span>
                                </div>
                            </div>
                            <div class="overview-item">
                                <i class="fas fa-briefcase"></i>
                                <div>
                                    <span class="label">Job Type</span>
                                    <span class="value"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                </div>
                            </div>
                            <div class="overview-item">
                                <i class="fas fa-user-graduate"></i>
                                <div>
                                    <span class="label">Experience</span>
                                    <span class="value"><?php echo ucfirst($job['experience_level']) . ' Level'; ?></span>
                                </div>
                            </div>
                            <div class="overview-item">
                                <i class="fas fa-eye"></i>
                                <div>
                                    <span class="label">Views</span>
                                    <span class="value"><?php echo number_format($job['views']); ?></span>
                                </div>
                            </div>
                            <div class="overview-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <span class="label">Applications</span>
                                    <span class="value"><?php echo number_format($job['applications']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="job-description">
                        <h2>Job Description</h2>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="job-requirements">
                        <h2>Requirements</h2>
                        <div class="requirements-content">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($job['application_instructions'])): ?>
                    <div class="application-instructions">
                        <h2>Application Instructions</h2>
                        <div class="instructions-content">
                            <?php echo nl2br(htmlspecialchars($job['application_instructions'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && isJobSeeker()): ?>
                        <?php if ($has_applied): ?>
                            <div class="application-status">
                                <h2>Your Application Status</h2>
                                <div class="status-content">
                                    <p>You have already applied for this job. Your application status is: <strong><?php echo ucfirst($application_status); ?></strong></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="job-application" id="apply-section">
                                <h2>Apply for this Job</h2>
                                
                                <?php if (!empty($application_error)): ?>
                                    <div class="alert alert-danger"><?php echo $application_error; ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application_success)): ?>
                                    <div class="alert alert-success"><?php echo $application_success; ?></div>
                                <?php endif; ?>
                                
                                <form action="job-details.php?id=<?php echo $job_id; ?>" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="resume">Upload Resume (PDF, DOC, DOCX)</label>
                                        <input type="file" id="resume" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cover_letter">Cover Letter (Optional)</label>
                                        <textarea id="cover_letter" name="cover_letter" class="form-control" rows="5"></textarea>
                                    </div>
                                    
                                    <button type="submit" name="apply_submit" class="submit-btn">Submit Application</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php elseif (!isLoggedIn()): ?>
                        <div class="login-to-apply">
                            <h2>Want to Apply?</h2>
                            <p>Please login or create an account to apply for this job.</p>
                            <div class="login-buttons">
                                <a href="login.php" class="login-btn">Login</a>
                                <a href="register.php" class="register-btn">Register</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="job-sidebar">
                    <div class="company-info">
                        <h3>About the Company</h3>
                        <div class="company-logo">
                            <img src="/api/placeholder/200/200" alt="<?php echo htmlspecialchars($job['company_name']); ?> Logo">
                        </div>
                        <h4><?php echo htmlspecialchars($job['company_name']); ?></h4>
                        <div class="company-details">
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></p>
                            <?php if (filter_var($job['contact_email'], FILTER_VALIDATE_EMAIL)): ?>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($job['contact_email']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($job['contact_phone'])): ?>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($job['contact_phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($related_jobs)): ?>
                    <div class="related-jobs">
                        <h3>Similar Jobs</h3>
                        <div class="related-jobs-list">
                            <?php foreach ($related_jobs as $related_job): ?>
                                <div class="related-job-item">
                                    <h4>
                                        <a href="job-details.php?id=<?php echo $related_job['id']; ?>"><?php echo htmlspecialchars($related_job['title']); ?></a>
                                    </h4>
                                    <p class="company-name"><?php echo htmlspecialchars($related_job['company_name']); ?></p>
                                    <div class="job-meta">
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($related_job['location']); ?></span>
                                        <span><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $related_job['job_type'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Toggle share options
        function toggleShareOptions() {
            const shareOptions = document.getElementById('shareOptions');
            shareOptions.classList.toggle('active');
        }
        
        // Job save functionality with AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const saveButton = document.querySelector('.job-save[data-job-id]');
            
            if (saveButton) {
                saveButton.addEventListener('click', function() {
                    const jobId = this.getAttribute('data-job-id');
                    const icon = this.querySelector('i');
                    const text = this.querySelector('span');
                    const isSaved = icon.classList.contains('fas');
                    
                    // Create AJAX request
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'ajax/save-job.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                if (isSaved) {
                                    icon.classList.remove('fas');
                                    icon.classList.add('far');
                                    text.textContent = 'Save Job';
                                } else {
                                    icon.classList.remove('far');
                                    icon.classList.add('fas');
                                    text.textContent = 'Saved';
                                    
                                    // Add heart animation
                                    icon.style.transform = 'scale(1.3)';
                                    setTimeout(() => {
                                        icon.style.transform = 'scale(1)';
                                    }, 300);
                                }
                            }
                        }
                    };
                    
                    xhr.send('job_id=' + jobId + '&action=' + (isSaved ? 'unsave' : 'save'));
                });
            }
        });
    </script>
</body>
</html>