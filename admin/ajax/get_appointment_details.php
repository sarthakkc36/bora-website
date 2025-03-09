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
?>

<div class="appointment-details-view">
    <h3><?php echo htmlspecialchars($appointment['purpose']); ?></h3>
    
    <div class="detail-section">
        <div class="detail-group">
            <span class="detail-label">Requester:</span>
            <span class="detail-value"><?php echo htmlspecialchars($appointment['name']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($appointment['email']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Phone:</span>
            <span class="detail-value"><?php echo htmlspecialchars($appointment['phone'] ?? 'N/A'); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Status:</span>
            <span class="detail-value"><?php echo getAppointmentStatusLabel($appointment['status']); ?></span>
        </div>
    </div>
    
    <div class="detail-section">
        <div class="detail-group">
            <span class="detail-label">Preferred Date:</span>
            <span class="detail-value"><?php echo formatDate($appointment['preferred_date']); ?></span>
        </div>
        
        <div class="detail-group">
            <span class="detail-label">Preferred Time:</span>
            <span class="detail-value"><?php echo formatTime($appointment['preferred_time']); ?></span>
        </div>
        
        <?php if (!empty($appointment['admin_scheduled_date']) && !empty($appointment['admin_scheduled_time'])): ?>
            <div class="detail-group">
                <span class="detail-label">Scheduled Date:</span>
                <span class="detail-value"><?php echo formatDate($appointment['admin_scheduled_date']); ?></span>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Scheduled Time:</span>
                <span class="detail-value"><?php echo formatTime($appointment['admin_scheduled_time']); ?></span>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($appointment['message'])): ?>
        <div class="content-section">
            <h4>Message from Requester</h4>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($appointment['message'])); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($appointment['admin_notes'])): ?>
        <div class="content-section">
            <h4>Admin Notes</h4>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($appointment['admin_notes'])); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .appointment-details-view {
        font-size: 15px;
    }
    
    .appointment-details-view h3 {
        margin-bottom: 15px;
        color: #333;
    }
    
    .detail-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }
    
    .detail-group {
        margin-bottom: 8px;
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
    
    .content-section {
        margin-top: 20px;
    }
    
    .content-section h4 {
        font-size: 18px;
        margin-bottom: 10px;
        color: #333;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
    
    .content-text {
        line-height: 1.6;
        color: #444;
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
    }
</style>