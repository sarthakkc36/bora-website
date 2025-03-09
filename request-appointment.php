<?php
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $purpose = sanitizeInput($_POST['purpose']);
    $preferred_date = sanitizeInput($_POST['preferred_date']);
    $preferred_time = sanitizeInput($_POST['preferred_time']);
    $message = sanitizeInput($_POST['message']);
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($purpose)) {
        $errors[] = "Purpose of appointment is required";
    }
    
    if (empty($preferred_date)) {
        $errors[] = "Preferred date is required";
    }
    
    if (empty($preferred_time)) {
        $errors[] = "Preferred time is required";
    }
    
    // If no errors, save the appointment request
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (name, email, phone, purpose, preferred_date, preferred_time, message, status)
                               VALUES (:name, :email, :phone, :purpose, :preferred_date, :preferred_time, :message, 'pending')");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':purpose', $purpose);
            $stmt->bindParam(':preferred_date', $preferred_date);
            $stmt->bindParam(':preferred_time', $preferred_time);
            $stmt->bindParam(':message', $message);
            
            $stmt->execute();
            
            // Send notification email to admin
            $admin_email = "admin@bhemployment.com"; // Change to your admin email
            $subject = "New Appointment Request: " . $purpose;
            $message_email = "<p>A new appointment request has been submitted:</p>";
            $message_email .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
            $message_email .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
            $message_email .= "<p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>";
            $message_email .= "<p><strong>Purpose:</strong> " . htmlspecialchars($purpose) . "</p>";
            $message_email .= "<p><strong>Preferred Date:</strong> " . htmlspecialchars($preferred_date) . "</p>";
            $message_email .= "<p><strong>Preferred Time:</strong> " . htmlspecialchars($preferred_time) . "</p>";
            $message_email .= "<p><strong>Message:</strong> " . nl2br(htmlspecialchars($message)) . "</p>";
            $message_email .= "<p>Please login to the admin panel to manage this appointment request.</p>";
            
            sendEmail($admin_email, $subject, $message_email);
            
            $success = "Thank you for your appointment request! We will contact you soon to confirm the details.";
            
            // Reset form fields
            $name = $email = $phone = $purpose = $preferred_date = $preferred_time = $message = '';
            
        } catch (PDOException $e) {
            error_log("Error submitting appointment request: " . $e->getMessage());
            $errors[] = "An error occurred while submitting your request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Appointment - B&H Employment & Consultancy Inc</title>
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
            <h1>Request an Appointment</h1>
            <p>Schedule a meeting with our consultants to discuss your employment needs</p>
        </div>
    </section>

    <section class="appointment-section">
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
                    <h2><i class="fas fa-calendar-check"></i> Schedule an Appointment</h2>
                </div>
                
                <div class="content-body">
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> Please provide your preferred date and time. Our team will confirm the appointment or suggest alternative times if necessary.</p>
                    </div>
                    
                    <form action="request-appointment.php" method="POST">
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="name">Your Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group half">
                                <label for="email">Your Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Your Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="purpose">Purpose of Appointment</label>
                            <select id="purpose" name="purpose" class="form-control" required>
                                <option value="">Select Purpose</option>
                                <option value="Job Assistance" <?php echo isset($purpose) && $purpose === 'Job Assistance' ? 'selected' : ''; ?>>Job Search Assistance</option>
                                <option value="Resume Review" <?php echo isset($purpose) && $purpose === 'Resume Review' ? 'selected' : ''; ?>>Resume Review</option>
                                <option value="Career Counseling" <?php echo isset($purpose) && $purpose === 'Career Counseling' ? 'selected' : ''; ?>>Career Counseling</option>
                                <option value="Recruitment Services" <?php echo isset($purpose) && $purpose === 'Recruitment Services' ? 'selected' : ''; ?>>Recruitment Services</option>
                                <option value="Other" <?php echo isset($purpose) && $purpose === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="preferred_date">Preferred Date</label>
                                <input type="date" id="preferred_date" name="preferred_date" class="form-control" value="<?php echo isset($preferred_date) ? htmlspecialchars($preferred_date) : ''; ?>" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            
                            <div class="form-group half">
                                <label for="preferred_time">Preferred Time</label>
                                <input type="time" id="preferred_time" name="preferred_time" class="form-control" value="<?php echo isset($preferred_time) ? htmlspecialchars($preferred_time) : ''; ?>" required>
                                <small class="form-text">Our office hours are 9:00 AM to 5:00 PM, Monday to Friday.</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Additional Message (Optional)</label>
                            <textarea id="message" name="message" class="form-control" rows="4"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">Request Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html>