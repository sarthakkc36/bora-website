<?php
require_once 'config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Get form data
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$subject = sanitizeInput($_POST['subject'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// Validate form data
$errors = [];

if (empty($name)) {
    $errors[] = "Full name is required";
}

if (empty($email)) {
    $errors[] = "Email address is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if (empty($subject)) {
    $errors[] = "Subject is required";
}

if (empty($message)) {
    $errors[] = "Message is required";
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $error_message = implode("<br>", $errors);
    flashMessage($error_message, "danger");
    redirect('index.php#contact');
}

// Process the contact form submission
try {
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) 
                          VALUES (:name, :email, :phone, :subject, :message)");
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    
    $stmt->execute();
    
    // In a real application, you would also send an email notification here
    // For example:
    // mail('admin@example.com', 'Contact Form: ' . $subject, $message, 'From: ' . $email);
    
    // Set success message and redirect
    flashMessage("Your message has been sent successfully! We'll get back to you shortly.", "success");
    redirect('index.php#contact');
    
} catch (PDOException $e) {
    error_log("Error processing contact form: " . $e->getMessage());
    flashMessage("An error occurred while processing your request. Please try again later.", "danger");
    redirect('index.php#contact');
}
?>