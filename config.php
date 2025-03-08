<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'bh_employment'; // Change this to your database name
$db_user = 'root'; // Change this to your MySQL username
$db_pass = ''; // Change this to your MySQL password

// Create database connection
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Site URL configuration
$site_url = "http://localhost/"; // Change to your actual domain in production

// Session configuration
session_start();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isEmployer() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'employer';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        
        echo "<div class='alert alert-$type'>$message</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
function isJobSeeker() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'job_seeker';
}

function isVerified() {
    return isset($_SESSION['is_verified']) && $_SESSION['is_verified'] == 1;
}

function hasValidSubscription() {
    // Admin always has valid "subscription"
    if (isAdmin()) {
        return true;
    }
    
    // If no subscription end date is set but user is verified, consider it valid
    if (isVerified() && empty($_SESSION['subscription_end'])) {
        return true;
    }
    
    // If subscription end date is set, check if it's in the future
    if (isset($_SESSION['subscription_end']) && !empty($_SESSION['subscription_end'])) {
        $today = new DateTime();
        $end_date = new DateTime($_SESSION['subscription_end']);
        return $today <= $end_date;
    }
    
    return false;
}
/**
 * Handle favicon upload without requiring GD library
 * @param array $file The uploaded file ($_FILES['favicon'])
 * @param string $upload_dir Directory to save the favicon
 * @return string|false Path to the favicon or false on failure
 */
function handleFaviconUpload($file, $upload_dir = 'uploads/favicon/') {
    // Check if file was uploaded successfully
    if (!isset($file) || $file['error'] != 0) {
        return false;
    }
    
    // Validate file type
    $allowed_types = ['image/x-icon', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate a unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'favicon_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return false;
}
?>
