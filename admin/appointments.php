<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

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

// Get filter
// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query based on filters
$query = "SELECT * FROM appointments";
$params = [];

if (!empty($status_filter)) {
    $query .= " WHERE status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY created_at DESC";

// Get all appointments
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

// Format date and time
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
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
    <style>
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
        
        .appointment-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .appointment-status.confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .appointment-status.rescheduled {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .appointment-status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
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
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-calendar-check"></i> Appointments</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($appointments)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-check"></i>
                                    <p>No appointments found for the selected filter.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Purpose</th>
                                                <th>Preferred Date/Time</th>
                                                <th>Scheduled Date/Time</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointments as $appointment): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo htmlspecialchars($appointment['name']); ?>
                                                        <br>
                                                        <small><?php echo htmlspecialchars($appointment['email']); ?></small>
                                                        <?php if (!empty($appointment['phone'])): ?>
                                                            <br>
                                                            <small><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($appointment['purpose']); ?></td>
                                                    <td>
                                                        <?php echo formatDate($appointment['preferred_date']); ?>
                                                        <br>
                                                        <small><?php echo formatTime($appointment['preferred_time']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($appointment['admin_scheduled_date']) && !empty($appointment['admin_scheduled_time'])): ?>
                                                            <?php echo formatDate($appointment['admin_scheduled_date']); ?>
                                                            <br>
                                                            <small><?php echo formatTime($appointment['admin_scheduled_time']); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not scheduled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="appointment-status <?php echo $appointment['status']; ?>">
                                                            <?php echo ucfirst($appointment['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="actions">
                                                        <a href="#" class="action-btn view" title="View Details" onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if ($appointment['status'] === 'pending'): ?>
                                                            <a href="#" class="action-btn activate" title="Confirm Appointment" onclick="showConfirmModal(<?php echo $appointment['id']; ?>)">
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
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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
                        <input type="time" id="confirmTime" name="admin_scheduled_time" class="form-control" required>
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
                        <input type="time" id="rescheduleTime" name="admin_scheduled_time" class="form-control" required>
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
    </script>
    <script src="../js/script.js"></script>
</body>
</html>