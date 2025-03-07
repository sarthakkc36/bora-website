<?php
// Database configuration
require_once 'config.php';

// Define the admin credentials
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_email = 'admin@bh.com';
$admin_first_name = 'Admin';
$admin_last_name = 'User';
$admin_role = 'admin';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $admin_username);
    $stmt->bindParam(':email', $admin_email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Admin exists, update password
        $admin = $stmt->fetch();
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $update = $pdo->prepare("UPDATE users SET password = :password, is_verified = 1 WHERE id = :id");
        $update->bindParam(':password', $hashed_password);
        $update->bindParam(':id', $admin['id']);
        $update->execute();
        
        echo "<p>Admin password has been reset to 'admin123'.</p>";
    } else {
        // Admin doesn't exist, create admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $insert = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, is_verified) 
                               VALUES (:username, :email, :password, :first_name, :last_name, :role, 1)");
        $insert->bindParam(':username', $admin_username);
        $insert->bindParam(':email', $admin_email);
        $insert->bindParam(':password', $hashed_password);
        $insert->bindParam(':first_name', $admin_first_name);
        $insert->bindParam(':last_name', $admin_last_name);
        $insert->bindParam(':role', $admin_role);
        $insert->execute();
        
        echo "<p>Admin user has been created with username 'admin' and password 'admin123'.</p>";
    }
    
    // Verify the password hash to ensure it works
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username");
    $stmt->bindParam(':username', $admin_username);
    $stmt->execute();
    $stored_hash = $stmt->fetch()['password'];
    
    if (password_verify($admin_password, $stored_hash)) {
        echo "<p style='color: green;'>✓ Password verification successful. You can now <a href='login.php'>login</a> with username 'admin' and password 'admin123'.</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification failed. There may be an issue with the password hashing.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>

<p>This script ensures that admin credentials are correctly set up in the database.</p>
<p>If you see a success message above, you should be able to login as admin with:</p>
<ul>
    <li><strong>Username:</strong> admin</li>
    <li><strong>Password:</strong> admin123</li>
</ul>