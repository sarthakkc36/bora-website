<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$selected_date = isset($_GET['date']) ? sanitizeInput($_GET['date']) : date('Y-m-d');

// Handle appointment actions (confirm/reschedule/cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['action'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $action = $_POST['action'];
    $admin_notes = isset($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : '';
    
    try {
        // Verify that the appointment exists
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :appointment_id");
        $stmt->bindParam(':appointment_id', $appointment_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            flashMessage("Appointment not found", "danger");
            redirect('appointments.php');
        }
        
        $appointment = $stmt->fetch();
        
        // Perform the requested action
        switch ($action) {
            case 'confirm':
                $admin_scheduled_date = sanitizeInput($_POST['admin_scheduled_date']);
                $admin_scheduled_time = sanitizeInput($_POST['admin_scheduled_time']);
                
                $stmt = $pdo->prepare("UPDATE appointments SET 
                                      status = 'confirmed', 
                                      admin_notes = :admin_notes,
                                      admin_scheduled_date = :admin_scheduled_date,
                                      admin_scheduled_time = :admin_scheduled_time
                                      WHERE id = :appointment_id");
                $stmt->bindParam(':admin_notes', $admin_notes);
                $stmt->bindParam(':admin_scheduled_date', $admin_scheduled_date);
                $stmt->bindParam(':admin_scheduled_time', $admin_scheduled_time);
                $stmt->bindParam(':appointment_id', $appointment_id);
                $stmt->execute();
                
                // Send email notification to the requester
                if (!empty($appointment['email'])) {
                    $subject = "Appointment Confirmed: " . $appointment['purpose'];
                    $message = "<p>Dear " . htmlspecialchars($appointment['name']) . ",</p>";
                    $message .= "<p>Your appointment request for <strong>" . htmlspecialchars($appointment['purpose']) . "</strong> has been confirmed.</p>";
                    $message .= "<p><strong>Date:</strong> " . date('l, F j, Y', strtotime($admin_scheduled_date)) . "</p>";
                    $message .= "<p><strong>Time:</strong> " . date('g:i A', strtotime($admin_scheduled_time)) . "</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Additional Information:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>If you need to reschedule or cancel, please contact us as soon as possible.</p>";
                    $message .= "<p>We look forward to meeting with you!</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($appointment['email'], $subject, $message);
                }
                
                flashMessage("Appointment confirmed successfully", "success");
                break;
                
            case 'reschedule':
                $admin_scheduled_date = sanitizeInput($_POST['admin_scheduled_date']);
                $admin_scheduled_time = sanitizeInput($_POST['admin_scheduled_time']);
                
                $stmt = $pdo->prepare("UPDATE appointments SET 
                                      status = 'rescheduled', 
                                      admin_notes = :admin_notes,
                                      admin_scheduled_date = :admin_scheduled_date,
                                      admin_scheduled_time = :admin_scheduled_time
                                      WHERE id = :appointment_id");
                $stmt->bindParam(':admin_notes', $admin_notes);
                $stmt->bindParam(':admin_scheduled_date', $admin_scheduled_date);
                $stmt->bindParam(':admin_scheduled_time', $admin_scheduled_time);
                $stmt->bindParam(':appointment_id', $appointment_id);
                $stmt->execute();
                
                // Send email notification to the requester
                if (!empty($appointment['email'])) {
                    $subject = "Appointment Rescheduled: " . $appointment['purpose'];
                    $message = "<p>Dear " . htmlspecialchars($appointment['name']) . ",</p>";
                    $message .= "<p>We need to reschedule your appointment for <strong>" . htmlspecialchars($appointment['purpose']) . "</strong>.</p>";
                    $message .= "<p><strong>New Date:</strong> " . date('l, F j, Y', strtotime($admin_scheduled_date)) . "</p>";
                    $message .= "<p><strong>New Time:</strong> " . date('g:i A', strtotime($admin_scheduled_time)) . "</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Reason for Rescheduling:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>If this new time doesn't work for you, please contact us to arrange an alternative.</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($appointment['email'], $subject, $message);
                }
                
                flashMessage("Appointment rescheduled successfully", "success");
                break;
                
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE appointments SET 
                                      status = 'cancelled', 
                                      admin_notes = :admin_notes
                                      WHERE id = :appointment_id");
                $stmt->bindParam(':admin_notes', $admin_notes);
                $stmt->bindParam(':appointment_id', $appointment_id);
                $stmt->execute();
                
                // Send email notification to the requester
                if (!empty($appointment['email'])) {
                    $subject = "Appointment Cancelled: " . $appointment['purpose'];
                    $message = "<p>Dear " . htmlspecialchars($appointment['name']) . ",</p>";
                    $message .= "<p>We regret to inform you that your appointment for <strong>" . htmlspecialchars($appointment['purpose']) . "</strong> has been cancelled.</p>";
                    
                    if (!empty($admin_notes)) {
                        $message .= "<p><strong>Reason for Cancellation:</strong> " . nl2br(htmlspecialchars($admin_notes)) . "</p>";
                    }
                    
                    $message .= "<p>If you'd like to reschedule, please submit a new appointment request or contact us directly.</p>";
                    $message .= "<p>We apologize for any inconvenience this may cause.</p>";
                    $message .= "<p>Best regards,<br>B&H Employment & Consultancy Team</p>";
                    
                    sendEmail($appointment['email'], $subject, $message);
                }
                
                flashMessage("Appointment cancelled successfully", "success");
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :appointment_id");
                $stmt->bindParam(':appointment_id', $appointment_id);
                $stmt->execute();
                flashMessage("Appointment deleted successfully", "success");
                break;
                
            default:
                flashMessage("Invalid action", "danger");
        }
    } catch (PDOException $e) {
        error_log("Error performing appointment action: " . $e->getMessage());
        flashMessage("An error occurred. Please try again.", "danger");
    }
    
    redirect('appointments.php');
}

// Build query based on filters
$query = "SELECT * FROM appointments";
$params = [];
$where_conditions = [];

if (!empty($status_filter)) {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

// Add date filtering for calendar view
if (!empty($selected_date)) {
    $where_conditions[] = "(preferred_date = :selected_date OR admin_scheduled_date = :selected_date)";
    $params[':selected_date'] = $selected_date;
}

// Combine where conditions if any
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY COALESCE(admin_scheduled_time, preferred_time) ASC";

// Get all appointments based on filters
try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    $appointments = [];
}

// Generate calendar data (next 14 days)
$calendar_days = [];
$start_date = new DateTime();
for ($i = 0; $i < 14; $i++) {
    $date = clone $start_date;
    $date->modify("+$i day");
    
    // Count appointments for this day
    $day_count = 0;
    foreach ($appointments as $appointment) {
        $appt_date = !empty($appointment['admin_scheduled_date']) ? $appointment['admin_scheduled_date'] : $appointment['preferred_date'];
        if ($appt_date == $date->format('Y-m-d')) {
            $day_count++;
        }
    }
    
    $calendar_days[] = [
        'date' => $date->format('Y-m-d'),
        'day' => $date->format('d'),
        'day_name' => $date->format('D'),
        'month' => $date->format('M'),
        'count' => $day_count,
        'is_today' => $date->format('Y-m-d') == date('Y-m-d'),
    ];
}

// Get user details for scheduling modal if appointment_id is provided
$scheduling_appointment = null;
if (isset($_GET['schedule']) && !empty($_GET['schedule'])) {
    $schedule_id = (int)$_GET['schedule'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :appointment_id");
        $stmt->bindParam(':appointment_id', $schedule_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $scheduling_appointment = $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Error fetching appointment for scheduling: " . $e->getMessage());
    }
}

// Format date and time
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Get available time slots for scheduling
$time_slots = [
    '09:00:00' => '9:00 AM',
    '10:00:00' => '10:00 AM',
    '11:00:00' => '11:00 AM',
    '12:00:00' => '12:00 PM',
    '13:00:00' => '1:00 PM',
    '14:00:00' => '2:00 PM',
    '15:00:00' => '3:00 PM',
    '16:00:00' => '4:00 PM',
    '17:00:00' => '5:00 PM',
];

// Get booked time slots for the selected date
$booked_slots = [];
if (!empty($selected_date)) {
    try {
        $stmt = $pdo->prepare("SELECT admin_scheduled_time, preferred_time FROM appointments 
                              WHERE (admin_scheduled_date = :date OR preferred_date = :date) 
                              AND status IN ('confirmed', 'rescheduled', 'pending')");
        $stmt->bindParam(':date', $selected_date);
        $stmt->execute();
        
        while ($slot = $stmt->fetch()) {
            $booked_time = !empty($slot['admin_scheduled_time']) ? $slot['admin_scheduled_time'] : $slot['preferred_time'];
            $booked_slots[] = $booked_time;
        }
    } catch (PDOException $e) {
        error_log("Error fetching booked slots: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - B&H Employment & Consultancy Inc</title>
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
<style>
    /* Calendar Styles */
    .calendar-section {
        margin-bottom: 30px;
    }
    
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .calendar-scroll {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;
        scrollbar-width: thin;
        scrollbar-color: #0066cc #f0f0f0;
    }
    
    .calendar-scroll::-webkit-scrollbar {
        height: 6px;
    }
    
    .calendar-scroll::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 10px;
    }
    
    .calendar-scroll::-webkit-scrollbar-thumb {
        background-color: #0066cc;
        border-radius: 10px;
    }
    
    .calendar-day {
        min-width: 80px;
        height: 90px;
        margin-right: 10px;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #eee;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-decoration: none;
        color: #333;
        position: relative;
    }
    
    .calendar-day:hover {
        background-color: #f0f7ff;
        border-color: #0066cc;
    }
    
    .calendar-day.selected {
        background-color: #0066cc;
        border-color: #0066cc;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
    }
    
    .calendar-day.selected .day-name,
    .calendar-day.selected .month,
    .calendar-day.selected .appointment-count {
        color: white;
    }
    
    .calendar-day.today {
        border-color: #0066cc;
        background-color: #f0f7ff;
    }
    
    .day-number {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .day-name {
        font-size: 14px;
        color: #666;
        margin-bottom: 2px;
    }
    
    .month {
        font-size: 12px;
        color: #999;
    }
    
    .appointment-count {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: #0066cc;
        color: white;
        font-size: 12px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    /* Time grid styles */
    .time-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .time-slot {
        display: flex;
        padding: 15px;
        border-radius: 8px;
        background-color: #f9f9f9;
        transition: all 0.3s ease;
    }
    
    .time-slot:hover {
        background-color: #f0f7ff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .time-label {
        width: 80px;
        font-weight: 600;
        color: #0066cc;
    }
    
    .appointment-slot {
        flex: 1;
        min-height: 40px;
        border-radius: 6px;
        padding: 10px;
    }
    
    .appointment-slot.confirmed {
        background-color: #d4edda;
        border-left: 3px solid #28a745;
    }
    
    .appointment-slot.pending {
        background-color: #fff3cd;
        border-left: 3px solid #ffc107;
    }
    
    .appointment-slot.rescheduled {
        background-color: #cce5ff;
        border-left: 3px solid #0066cc;
    }
    
    .appointment-slot.cancelled {
        background-color: #f8d7da;
        border-left: 3px solid #dc3545;
    }
    
    .appointment-slot .client-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .appointment-slot .appointment-purpose {
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .appointment-slot .status-pill {
        display: inline-block;
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 12px;
        margin-right: 5px;
    }
    
    .status-pill.confirmed {
        background-color: #28a745;
        color: white;
    }
    
    .status-pill.pending {
        background-color: #ffc107;
        color: #212529;
    }
    
    .status-pill.rescheduled {
        background-color: #0066cc;
        color: white;
    }
    
    .status-pill.cancelled {
        background-color: #dc3545;
        color: white;
    }
    
    .appointment-slot .slot-actions {
        margin-top: 5px;
        display: flex;
        gap: 5px;
    }
    
    .slot-actions .action-btn {
        width: 28px;
        height: 28px;
    }
    
    .empty-time-slot {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-style: italic;
        height: 100%;
    }
    
    /* Filter tabs */
    .filter-tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .filter-tab {
        padding: 8px 16px;
        margin-right: 8px;
        background-color: #f5f5f5;
        border-radius: 20px;
        text-decoration: none;
        color: #666;
        transition: all 0.3s ease;
    }
    
    .filter-tab:hover {
        background-color: #e0e0e0;
        color: #333;
    }
    
    .filter-tab.active {
        background-color: #0066cc;
        color: white;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        max-width: 600px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        animation: modalFadeIn 0.3s;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: #333;
    }
    
    /* Time slots for scheduling */
    .scheduling-time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }
    
    .scheduling-time-slot {
        padding: 10px;
        text-align: center;
        border-radius: 8px;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .scheduling-time-slot:hover {
        background-color: #f0f7ff;
        border-color: #0066cc;
    }
    
    .scheduling-time-slot.selected {
        background-color: #0066cc;
        color: white;
        border-color: #0066cc;
    }
    
    .scheduling-time-slot.unavailable {
        background-color: #f5f5f5;
        color: #aaa;
        cursor: not-allowed;
        border-color: #ddd;
    }
    
    .scheduling-time-slot.unavailable:hover {
        background-color: #f5f5f5;
        border-color: #ddd;
    }
    
    /* Current date display */
    .current-date-display {
        background-color: #f0f7ff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 3px solid #0066cc;
        display: flex;
        align-items: center;
    }
    
    .current-date-display i {
        font-size: 24px;
        color: #0066cc;
        margin-right: 15px;
    }
    
    .current-date-display h3 {
        margin: 0;
        font-size: 18px;
    }
</style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Appointments</h1>
            <p>Review and respond to appointment requests</p>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <?php include 'sidebar.php'; ?>
                </div>
                
                <div class="dashboard-content">
                    <?php displayFlashMessage(); ?>
                    
                    <div class="filter-tabs">
                        <a href="appointments.php" class="filter-tab <?php echo empty($status_filter) ? 'active' : ''; ?>">All Appointments</a>
                        <a href="appointments.php?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="appointments.php?status=confirmed" class="filter-tab <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                        <a href="appointments.php?status=rescheduled" class="filter-tab <?php echo $status_filter === 'rescheduled' ? 'active' : ''; ?>">Rescheduled</a>
                        <a href="appointments.php?status=cancelled" class="filter-tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                    </div>
                    
                    <div class="calendar-section">
                        <div class="calendar-nav">
                            <h3><i class="fas fa-calendar-alt"></i> Appointment Calendar</h3>
                        </div>
                        
                        <div class="calendar-scroll">
                            <?php foreach ($calendar_days as $day): ?>
                                <a href="?date=<?php echo $day['date']; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?>" 
                                   class="calendar-day <?php echo ($day['date'] === $selected_date) ? 'selected' : ''; ?> <?php echo $day['is_today'] ? 'today' : ''; ?>">
                                    <span class="day-name"><?php echo $day['day_name']; ?></span>
                                    <span class="day-number"><?php echo $day['day']; ?></span>
                                    <span class="month"><?php echo $day['month']; ?></span>
                                    <?php if ($day['count'] > 0): ?>
                                        <span class="appointment-count"><?php echo $day['count']; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-calendar-check"></i> Appointments</h2>
                        </div>
                        
                        <div class="content-body">
                            <div class="current-date-display">
                                <i class="fas fa-calendar-day"></i>
                                <h3>Appointments for <?php echo date('l, F j, Y', strtotime($selected_date)); ?></h3>
                            </div>
                            
                            <?php if (empty($appointments)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-check"></i>
                                    <p>No appointments found for the selected date and filters.</p>
                                </div>
                            <?php else: ?>
                                <div class="time-grid">
                                    <?php 
                                    // Sort appointments by time
                                    $appointments_by_time = [];
                                    foreach ($time_slots as $time_slot => $display_time) {
                                        $appointments_by_time[$time_slot] = [];
                                        
                                        // Find appointments for this time slot
                                        foreach ($appointments as $appointment) {
                                            $appointment_time = !empty($appointment['admin_scheduled_time']) 
                                                ? $appointment['admin_scheduled_time'] 
                                                : $appointment['preferred_time'];
                                                
                                            if ($appointment_time == $time_slot) {
                                                $appointments_by_time[$time_slot][] = $appointment;
                                            }
                                        }
                                    }
                                    
                                    // Display time slots
                                    foreach ($time_slots as $time_slot => $display_time):
                                    ?>
                                        <div class="time-slot">
                                            <div class="time-label"><?php echo $display_time; ?></div>
                                            <div class="appointment-container" style="flex: 1;">
                                                <?php if (empty($appointments_by_time[$time_slot])): ?>
                                                    <div class="empty-time-slot">
                                                        <span>No appointments scheduled</span>
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($appointments_by_time[$time_slot] as $appointment): ?>
                                                        <div class="appointment-slot <?php echo $appointment['status']; ?>">
                                                            <div class="client-name"><?php echo htmlspecialchars($appointment['name']); ?></div>
                                                            <div class="appointment-purpose"><?php echo htmlspecialchars($appointment['purpose']); ?></div>
                                                            <div class="appointment-meta">
                                                                <span class="status-pill <?php echo $appointment['status']; ?>"><?php echo ucfirst($appointment['status']); ?></span>
                                                                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($appointment['email']); ?></span>
                                                                <?php if (!empty($appointment['phone'])): ?>
                                                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['phone']); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="slot-actions">
                                                                <a href="#" class="action-btn view" title="View Details" onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                
                                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                                    <a href="appointments.php?schedule=<?php echo $appointment['id']; ?>&date=<?php echo $selected_date; ?>" class="action-btn activate" title="Confirm Appointment">
                                                                        <i class="fas fa-check-circle"></i>
                                                                    </a>
                                                                    
                                                                    <a href="#" class="action-btn edit" title="Reschedule Appointment" onclick="showRescheduleModal(<?php echo $appointment['id']; ?>)">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                    </a>
                                                                    
                                                                    <a href="#" class="action-btn deactivate" title="Cancel Appointment" onclick="showCancelModal(<?php echo $appointment['id']; ?>)">
                                                                        <i class="fas fa-times-circle"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.');">
                                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <button type="submit" class="action-btn delete" title="Delete">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment Details Modal -->
    <div id="appointmentDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('appointmentDetailsModal')">&times;</span>
            <h2>Appointment Details</h2>
            <div id="appointmentDetailsContent">Loading...</div>
        </div>
    </div>
    
    <!-- Confirm Appointment Modal -->
    <div id="confirmAppointmentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('confirmAppointmentModal')">&times;</span>
            <h2>Confirm Appointment</h2>
            <form method="POST" id="confirmAppointmentForm">
                <input type="hidden" name="appointment_id" id="confirmAppointmentId">
                <input type="hidden" name="action" value="confirm">
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="confirmDate">Appointment Date</label>
                        <input type="date" id="confirmDate" name="admin_scheduled_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="confirmTime">Appointment Time</label>
                        <select id="confirmTime" name="admin_scheduled_time" class="form-control" required>
                            <option value="">Select Time</option>
                            <?php foreach ($time_slots as $slot => $display): ?>
                                <option value="<?php echo $slot; ?>"><?php echo $display; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmNotes">Additional Notes (Optional)</label>
                    <textarea id="confirmNotes" name="admin_notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="submit-btn">Confirm Appointment</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('confirmAppointmentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reschedule Appointment Modal -->
    <div id="rescheduleAppointmentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('rescheduleAppointmentModal')">&times;</span>
            <h2>Reschedule Appointment</h2>
            <form method="POST" id="rescheduleAppointmentForm">
                <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">
                <input type="hidden" name="action" value="reschedule">
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="rescheduleDate">New Date</label>
                        <input type="date" id="rescheduleDate" name="admin_scheduled_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="rescheduleTime">New Time</label>
                        <select id="rescheduleTime" name="admin_scheduled_time" class="form-control" required>
                            <option value="">Select Time</option>
                            <?php foreach ($time_slots as $slot => $display): ?>
                                <option value="<?php echo $slot; ?>"><?php echo $display; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="rescheduleNotes">Reason for Rescheduling</label>
                    <textarea id="rescheduleNotes" name="admin_notes" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="submit-btn">Reschedule Appointment</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('rescheduleAppointmentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Cancel Appointment Modal -->
    <div id="cancelAppointmentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('cancelAppointmentModal')">&times;</span>
            <h2>Cancel Appointment</h2>
            <p>Please provide a reason for cancelling this appointment. This information will be sent to the requester.</p>
            <form method="POST" id="cancelAppointmentForm">
                <input type="hidden" name="appointment_id" id="cancelAppointmentId">
                <input type="hidden" name="action" value="cancel">
                
                <div class="form-group">
                    <label for="cancelNotes">Reason for Cancellation</label>
                    <textarea id="cancelNotes" name="admin_notes" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="submit-btn">Cancel Appointment</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('cancelAppointmentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // View appointment details
        function viewAppointmentDetails(appointmentId) {
            document.getElementById('appointmentDetailsContent').innerHTML = 'Loading...';
            document.getElementById('appointmentDetailsModal').style.display = 'block';
            
            // Fetch appointment details with AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax/get_appointment_details.php?id=' + appointmentId, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('appointmentDetailsContent').innerHTML = xhr.responseText;
                } else {
                    document.getElementById('appointmentDetailsContent').innerHTML = 'Error loading appointment details.';
                }
            };
            
            xhr.onerror = function() {
                document.getElementById('appointmentDetailsContent').innerHTML = 'Error loading appointment details.';
            };
            
            xhr.send();
        }
        
        // Show confirm modal
        function showConfirmModal(appointmentId) {
            document.getElementById('confirmAppointmentId').value = appointmentId;
            
            // Fetch preferred date/time to pre-fill form
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax/get_appointment_dates.php?id=' + appointmentId, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        document.getElementById('confirmDate').value = data.preferred_date;
                        document.getElementById('confirmTime').value = data.preferred_time;
                    } catch (e) {
                        console.error('Error parsing appointment dates:', e);
                    }
                }
            };
            
            xhr.send();
            
            document.getElementById('confirmAppointmentModal').style.display = 'block';
        }
        
        // Show reschedule modal
        function showRescheduleModal(appointmentId) {
            document.getElementById('rescheduleAppointmentId').value = appointmentId;
            document.getElementById('rescheduleAppointmentModal').style.display = 'block';
        }
        
        // Show cancel modal
        function showCancelModal(appointmentId) {
            document.getElementById('cancelAppointmentId').value = appointmentId;
            document.getElementById('cancelAppointmentModal').style.display = 'block';
        }
        
        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Scroll to the selected date in the calendar
        document.addEventListener('DOMContentLoaded', function() {
            const selectedDay = document.querySelector('.calendar-day.selected');
            if (selectedDay) {
                const scrollContainer = document.querySelector('.calendar-scroll');
                scrollContainer.scrollLeft = selectedDay.offsetLeft - scrollContainer.offsetWidth / 2 + selectedDay.offsetWidth / 2;
            }
        });
    </script>
    
    <?php if ($scheduling_appointment): ?>
    <script>
        // Automatically open the confirm modal for scheduling
        document.addEventListener('DOMContentLoaded', function() {
            showConfirmModal(<?php echo $scheduling_appointment['id']; ?>);
        });
    </script>
    <?php endif; ?>

    <script src="../js/script.js"></script>
</body>
</html>