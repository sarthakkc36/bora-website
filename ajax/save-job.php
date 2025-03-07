<?php
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to save jobs']);
    exit;
}

// Check if required parameters are set
if (!isset($_POST['job_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$job_id = (int)$_POST['job_id'];
$action = $_POST['action'];
$user_id = (int)$_SESSION['user_id'];

try {
    // Check if job exists
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = :job_id");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
        exit;
    }
    
    if ($action === 'save') {
        // Check if already saved
        $stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Save the job
            $stmt = $pdo->prepare("INSERT INTO saved_jobs (job_id, user_id) VALUES (:job_id, :user_id)");
            $stmt->bindParam(':job_id', $job_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Job saved successfully']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Job already saved']);
        }
    } elseif ($action === 'unsave') {
        // Remove the saved job
        $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = :job_id AND user_id = :user_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Job removed from saved jobs']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}