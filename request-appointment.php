<?php
require_once 'config.php';

$errors = [];
$success = '';

// Set default selected date to tomorrow
$default_date = date('Y-m-d', strtotime('+1 day'));
$selected_date = isset($_GET['date']) ? $_GET['date'] : $default_date;

// Define available time slots (24-hour format)
$available_times = [
    '09:00' => '9:00 AM',
    '10:00' => '10:00 AM',
    '11:00' => '11:00 AM',
    '12:00' => '12:00 PM',
    '13:00' => '1:00 PM',
    '14:00' => '2:00 PM',
    '15:00' => '3:00 PM',
    '16:00' => '4:00 PM',
    '17:00' => '5:00 PM'
];

// Get booked appointments for the selected date to show unavailable time slots
$booked_slots = [];
try {
    $stmt = $pdo->prepare("SELECT admin_scheduled_time, preferred_time FROM appointments 
                          WHERE (admin_scheduled_date = :selected_date OR preferred_date = :selected_date) 
                          AND status IN ('confirmed', 'pending')");
    $stmt->bindParam(':selected_date', $selected_date);
    $stmt->execute();
    
    while ($row = $stmt->fetch()) {
        // Convert time format if needed
        $time = !empty($row['admin_scheduled_time']) ? 
                date('H:i', strtotime($row['admin_scheduled_time'])) : 
                date('H:i', strtotime($row['preferred_time']));
        $booked_slots[] = $time;
    }
} catch (PDOException $e) {
    error_log("Error fetching booked appointments: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {
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
    
    // Check if the selected time slot is already booked
    if (in_array($preferred_time, $booked_slots)) {
        $errors[] = "The selected time slot is no longer available. Please select another time.";
    }
    
    // If no errors, save the appointment request
    if (empty($errors)) {
        try {
            // Format time properly for database storage
            $formatted_time = date('H:i:s', strtotime($preferred_time));
            
            $stmt = $pdo->prepare("INSERT INTO appointments (name, email, phone, purpose, preferred_date, preferred_time, message, status)
                               VALUES (:name, :email, :phone, :purpose, :preferred_date, :preferred_time, :message, 'pending')");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':purpose', $purpose);
            $stmt->bindParam(':preferred_date', $preferred_date);
            $stmt->bindParam(':preferred_time', $formatted_time);
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
            $message_email .= "<p><strong>Preferred Time:</strong> " . date('g:i A', strtotime($preferred_time)) . "</p>";
            $message_email .= "<p><strong>Message:</strong> " . nl2br(htmlspecialchars($message)) . "</p>";
            $message_email .= "<p>Please login to the admin panel to manage this appointment request.</p>";
            
            sendEmail($admin_email, $subject, $message_email);
            
            // Send confirmation email to user
            $user_subject = "Appointment Request Confirmation";
            $user_message = "<p>Dear " . htmlspecialchars($name) . ",</p>";
            $user_message .= "<p>Thank you for requesting an appointment with B&H Employment & Consultancy Inc.</p>";
            $user_message .= "<p>Your appointment details:</p>";
            $user_message .= "<p><strong>Purpose:</strong> " . htmlspecialchars($purpose) . "</p>";
            $user_message .= "<p><strong>Date:</strong> " . date('l, F j, Y', strtotime($preferred_date)) . "</p>";
            $user_message .= "<p><strong>Time:</strong> " . date('g:i A', strtotime($preferred_time)) . "</p>";
            $user_message .= "<p>Please note that your appointment is pending confirmation. We will contact you shortly to confirm or suggest alternative times if necessary.</p>";
            $user_message .= "<p>If you have any questions, please don't hesitate to contact us.</p>";
            $user_message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
            
            sendEmail($email, $user_subject, $user_message);
            
            $success = "Thank you for your appointment request! We will contact you soon to confirm the details.";
            
            // Reset form fields
            $name = $email = $phone = $purpose = $message = '';
            
        } catch (PDOException $e) {
            error_log("Error submitting appointment request: " . $e->getMessage());
            $errors[] = "An error occurred while submitting your request. Please try again.";
        }
    }
}

// Calculate next 14 days for the calendar
$calendar_days = [];
$start_date = new DateTime();
for ($i = 0; $i < 14; $i++) {
    $date = clone $start_date;
    $date->modify("+$i day");
    
    $calendar_days[] = [
        'date' => $date->format('Y-m-d'),
        'day' => $date->format('d'),
        'day_name' => $date->format('D'),
        'month' => $date->format('M'),
        'full_date' => $date->format('F j, Y'),
    ];
}

// Get current date info for the weekly calendar
$current_date = new DateTime($selected_date);
$week_start = clone $current_date;
$week_start->modify(-(($week_start->format('N') - 1) % 7) . ' days'); // Get to Sunday
$week_dates = [];
$weekday_names = [];

for ($i = 0; $i < 7; $i++) {
    $day = clone $week_start;
    $day->modify("+$i days");
    $week_dates[] = [
        'day' => $day->format('d'),
        'date' => $day->format('Y-m-d'),
        'is_selected' => ($day->format('Y-m-d') == $selected_date)
    ];
    $weekday_names[] = $day->format('D');
}

// Format time for display
function formatTimeDisplay($time_key, $time_value) {
    return $time_value;
}

// Get current month and year
$current_month_year = date('M Y', strtotime($selected_date));
$current_day_name = date('D', strtotime($selected_date));
$current_day = date('d', strtotime($selected_date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Appointment - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    :root {
        --primary-color: #40BFBF;
        --light-blue: #e6f7ff;
        --red: #FF6B6B;
        --orange: #FFA26B;
        --purple: #D183FF;
        --pink: #FF9EB7;
        --blue: #6BC5FF;
        --dark-text: #333;
        --light-text: #666;
        --border-color: #e0e0e0;
    }
    
    /* Main container styles */
    .appointment-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .appointment-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .appointment-success {
        text-align: center;
        padding: 40px 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .appointment-success i {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .appointment-success h3 {
        font-size: 24px;
        margin-bottom: 15px;
        color: #333;
    }

    /* Header styles */
    .app-header {
        background-color: var(--primary-color);
        color: white;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-title {
        font-size: 18px;
        font-weight: 500;
    }
    
    /* Calendar Header */
    .calendar-header {
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
    }
    
    .month-display {
        font-size: 16px;
        color: var(--light-text);
    }
    
    .day-navigation {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .nav-arrows {
        display: flex;
        gap: 10px;
        font-size: 16px;
        color: var(--primary-color);
    }
    
    .nav-arrows i {
        cursor: pointer;
    }
    
    .current-day {
        font-size: 16px;
        font-weight: 500;
    }
    
    .today-btn {
        background-color: #f5f5f5;
        border: none;
        padding: 6px 12px;
        border-radius: 15px;
        color: var(--primary-color);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .today-btn:hover {
        background-color: #e0e0e0;
    }
    
    /* Week Calendar */
    .week-calendar {
        padding: 15px;
    }
    
    .weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        margin-bottom: 15px;
    }
    
    .weekday {
        font-weight: 500;
        font-size: 14px;
        color: var(--light-text);
    }
    
    .dates {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        margin-bottom: 20px;
    }
    
    .date {
        font-size: 18px;
        padding: 8px 0;
        position: relative;
        cursor: pointer;
        text-decoration: none;
        color: var(--dark-text);
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .date:hover {
        background-color: #f0f0f0;
    }
    
    .date.selected {
        color: white;
        font-weight: bold;
        background-color: var(--primary-color);
    }
    
    /* Time slots styles */
    .time-slots-container {
        padding: 20px;
    }
    
    .time-slots-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .time-slots-instruction {
        color: #555;
        margin-bottom: 20px;
    }
    
    .time-slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
        margin-bottom: 30px;
    }
    
    .time-slot {
        padding: 12px 10px;
        text-align: center;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
        background-color: white;
    }
    
    .time-slot:hover {
        border-color: var(--primary-color);
        background-color: var(--light-blue);
    }
    
    .time-slot.selected {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
    }
    
    .time-slot.unavailable {
        background-color: #f5f5f5;
        color: #aaa;
        cursor: not-allowed;
        border-color: #e0e0e0;
    }
    
    .time-slot.unavailable:hover {
        background-color: #f5f5f5;
        border-color: #e0e0e0;
    }
    
    /* Form styles */
    .appointment-form {
        padding: 20px;
        border-top: 1px solid #eee;
    }
    
    .form-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }
    
    .selected-datetime {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 3px solid var(--primary-color);
    }
    
    .selected-datetime p {
        margin: 0 0 5px 0;
        color: #333;
    }
    
    .selected-datetime .datetime-label {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        flex: 1;
        min-width: 250px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.2s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(64, 191, 191, 0.1);
    }
    
    .submit-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 10px;
    }
    
    .submit-btn:hover {
        background-color: #35a3a3;
    }
    
    .submit-btn:disabled {
        background-color: #b3d1ff;
        cursor: not-allowed;
    }
    
    /* Alert styles */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert ul {
        margin: 10px 0 0 20px;
        padding: 0;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 15px;
        }
        
        .form-group {
            min-width: 100%;
        }
        
        .time-slots-grid {
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .date {
            font-size: 16px;
            width: 30px;
            height: 30px;
        }
        
        .weekday {
            font-size: 12px;
        }
        
        .calendar-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .day-navigation {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
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
                <div class="appointment-success">
                    <i class="fas fa-check-circle"></i>
                    <h3>Appointment Request Submitted!</h3>
                    <p><?php echo $success; ?></p>
                    <p>You will receive a confirmation email shortly.</p>
                    <a href="index.php" class="submit-btn" style="display: inline-block; margin-top: 20px;">Return to Home</a>
                </div>
            <?php else: ?>
                <div class="appointment-container">
                    <!-- Header -->
                    <div class="app-header">
                        <div class="header-title">Schedule an Appointment</div>
                    </div>
                    
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <div class="month-display"><?php echo $current_month_year; ?></div>
                        <div class="day-navigation">
                            <div class="nav-arrows">
                                <i class="fas fa-chevron-left" id="prev-day"></i>
                                <span class="current-day"><?php echo $current_day_name . ' ' . $current_day; ?></span>
                                <i class="fas fa-chevron-right" id="next-day"></i>
                            </div>
                            <a href="?date=<?php echo date('Y-m-d'); ?>" class="today-btn">Today</a>
                        </div>
                    </div>
                    
                    <!-- Week Calendar -->
                    <div class="week-calendar">
                        <div class="weekdays">
                            <?php foreach ($weekday_names as $weekday): ?>
                                <div class="weekday"><?php echo substr($weekday, 0, 1); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="dates">
                            <?php foreach ($week_dates as $date): ?>
                                <a href="?date=<?php echo $date['date']; ?>" class="date <?php echo $date['is_selected'] ? 'selected' : ''; ?>">
                                    <?php echo $date['day']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Time Slots -->
                    <div class="time-slots-container">
                        <h3 class="time-slots-title">Available Time Slots</h3>
                        <p class="time-slots-instruction">Select a time for your appointment on <?php echo date('l, F j, Y', strtotime($selected_date)); ?></p>
                        
                        <div class="time-slots-grid">
                            <?php foreach ($available_times as $time_key => $time_value): ?>
                                <?php 
                                    $is_unavailable = in_array($time_key, $booked_slots);
                                    $slot_class = $is_unavailable ? 'unavailable' : '';
                                ?>
                                <div class="time-slot <?php echo $slot_class; ?>" data-time="<?php echo $time_key; ?>">
                                    <?php echo formatTimeDisplay($time_key, $time_value); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Booking Form -->
                    <div class="appointment-form">
                        <h2 class="form-title">Complete Your Booking</h2>
                        
                        <div class="selected-datetime">
                            <p><i class="fas fa-calendar-alt"></i> <strong>Date:</strong> <span class="datetime-label"><?php echo date('l, F j, Y', strtotime($selected_date)); ?></span></p>
                            <p><i class="fas fa-clock"></i> <strong>Time:</strong> <span class="datetime-label" id="selected-time-display">Please select a time slot</span></p>
                        </div>
                        
                        <form action="request-appointment.php?date=<?php echo $selected_date; ?>" method="POST" id="appointment-form">
                            <input type="hidden" id="preferred_date" name="preferred_date" value="<?php echo $selected_date; ?>">
                            <input type="hidden" id="preferred_time" name="preferred_time" value="">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Your Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : (isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Your Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : (isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Your Phone Number</label>
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
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Additional Message (Optional)</label>
                                <textarea id="message" name="message" class="form-control" rows="4"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" name="submit_appointment" class="submit-btn" id="submit-btn" disabled>Book Appointment</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const timeSlots = document.querySelectorAll('.time-slot:not(.unavailable)');
        const selectedTimeDisplay = document.getElementById('selected-time-display');
        const preferredTimeInput = document.getElementById('preferred_time');
        const submitButton = document.getElementById('submit-btn');
        
        // Handle time slot selection
        timeSlots.forEach(slot => {
            slot.addEventListener('click', function() {
                // Remove selection from all slots
                timeSlots.forEach(s => s.classList.remove('selected'));
                
                // Add selection to clicked slot
                this.classList.add('selected');
                
                // Update displayed time and form value
                const time = this.getAttribute('data-time');
                selectedTimeDisplay.textContent = this.textContent.trim();
                preferredTimeInput.value = time;
                
                // Enable submit button
                submitButton.removeAttribute('disabled');
            });
        });
        
        // Handle date navigation
        const prevDayButton = document.getElementById('prev-day');
        const nextDayButton = document.getElementById('next-day');
        
        if (prevDayButton && nextDayButton) {
            // Get current date
            const currentDate = new Date('<?php echo $selected_date; ?>');
            
            prevDayButton.addEventListener('click', function() {
                currentDate.setDate(currentDate.getDate() - 1);
                window.location.href = '?date=' + formatDate(currentDate);
            });
            
            nextDayButton.addEventListener('click', function() {
                currentDate.setDate(currentDate.getDate() + 1);
                window.location.href = '?date=' + formatDate(currentDate);
            });
            
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
        }
        
        // If there's a pre-selected time in URL params, select it
        const urlParams = new URLSearchParams(window.location.search);
        const timeParam = urlParams.get('time');
        if (timeParam) {
            const timeSlot = document.querySelector(`.time-slot[data-time="${timeParam}"]`);
            if (timeSlot && !timeSlot.classList.contains('unavailable')) {
                timeSlot.click();
            }
        }
    });
</script>
    
<script src="js/script.js"></script>
</body>
</html>