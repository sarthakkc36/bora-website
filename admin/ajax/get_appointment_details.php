<?php
require_once '../../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "Unauthorized access";
    exit;
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    echo "No appointment ID provided";
    exit;
}

$appointment_id = (int)$_GET['id'];

// Get appointment details
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :appointment_id");
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "Appointment not found";
        exit;
    }
    
    $appointment = $stmt->fetch();
} catch (PDOException $e) {
    echo "Error retrieving appointment details";
    error_log("Error getting appointment details: " . $e->getMessage());
    exit;
}

// Format date and time
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Get status label
function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'success';
        case 'rescheduled':
            return 'info';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<div class="appointment-details-view">
    <div class="appointment-header">
        <h3 class="appointment-title"><?php echo htmlspecialchars($appointment['purpose']); ?></h3>
        <span class="status-badge <?php echo $appointment['status']; ?>"><?php echo ucfirst($appointment['status']); ?></span>
    </div>
    
    <div class="detail-section">
        <div class="detail-group">
            <span class="detail-label">Client Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($appointment['name']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Contact Details:</span>
            <div class="contact-details">
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($appointment['email']); ?></span>
                <?php if (!empty($appointment['phone'])): ?>
                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['phone']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Requested Date/Time:</span>
            <div class="detail-value">
                <span><i class="fas fa-calendar"></i> <?php echo formatDate($appointment['preferred_date']); ?></span>
                <span><i class="fas fa-clock"></i> <?php echo formatTime($appointment['preferred_time']); ?></span>
            </div>
        </div>
        
        <?php if (!empty($appointment['admin_scheduled_date']) && !empty($appointment['admin_scheduled_time'])): ?>
            <div class="detail-group">
                <span class="detail-label">Scheduled Date/Time:</span>
                <div class="detail-value">
                    <span><i class="fas fa-calendar-check"></i> <?php echo formatDate($appointment['admin_scheduled_date']); ?></span>
                    <span><i class="fas fa-clock"></i> <?php echo formatTime($appointment['admin_scheduled_time']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="detail-group">
            <span class="detail-label">Submission Date:</span>
            <span class="detail-value"><?php echo formatDate($appointment['created_at']); ?></span>
        </div>
    </div>
    
    <?php if (!empty($appointment['message'])): ?>
        <div class="message-section">
            <h4>Client Message</h4>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($appointment['message'])); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($appointment['admin_notes'])): ?>
        <div class="notes-section">
            <h4>Admin Notes</h4>
            <div class="notes-content">
                <?php echo nl2br(htmlspecialchars($appointment['admin_notes'])); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="modal-actions">
        <?php if ($appointment['status'] === 'pending'): ?>
            <a href="appointments.php?schedule=<?php echo $appointment['id']; ?>" class="btn-primary">
                <i class="fas fa-check-circle"></i> Confirm
            </a>
            
            <a href="#" onclick="closeModal('appointmentDetailsModal'); showRescheduleModal(<?php echo $appointment['id']; ?>);" class="btn-secondary">
                <i class="fas fa-calendar-alt"></i> Reschedule
            </a>
            
            <a href="#" onclick="closeModal('appointmentDetailsModal'); showCancelModal(<?php echo $appointment['id']; ?>);" class="btn-secondary">
                <i class="fas fa-times-circle"></i> Cancel
            </a>
        <?php endif; ?>
    </div>
</div>

<style>
    .appointment-details-view {
        font-size: 15px;
    }
    
    .appointment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .appointment-title {
        font-size: 20px;
        margin: 0;
        color: #333;
    }
    
    .detail-section {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .detail-group {
        margin-bottom: 12px;
    }
    
    .detail-label {
        font-weight: 600;
        color: #666;
        display: block;
        margin-bottom: 3px;
    }
    
    .detail-value {
        color: #333;
    }
    
    .contact-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .message-section, .notes-section {
        margin-top: 20px;
    }
    
    .message-section h4, .notes-section h4 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #333;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
    
    .message-content, .notes-content {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        line-height: 1.5;
    }
    
    .notes-content {
        background-color: #fff8e6;
        border-left: 3px solid #f8bb86;
    }
    
    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        justify-content: flex-end;
    }
</style>