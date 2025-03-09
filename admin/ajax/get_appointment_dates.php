<?php
require_once '../../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No appointment ID provided']);
    exit;
}

$appointment_id = (int)$_GET['id'];

// Get appointment details
try {
    $stmt = $pdo->prepare("SELECT preferred_date, preferred_time FROM appointments WHERE id = :appointment_id");
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Appointment not found']);
        exit;
    }
    
    $appointment = $stmt->fetch();
    
    // Format preferred_time to match the time input format (HH:MM)
    $preferred_time = date('H:i', strtotime($appointment['preferred_time']));
    
    echo json_encode([
        'preferred_date' => $appointment['preferred_date'],
        'preferred_time' => $preferred_time
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
    error_log("Error getting appointment dates: " . $e->getMessage());
    exit;
}