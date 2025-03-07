<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // Change this to your MySQL username
$db_pass = ''; // Change this to your MySQL password

try {
    // Connect to the MySQL server without selecting a database
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS bh_employment");
    
    // Switch to the bh_employment database
    $pdo->exec("USE bh_employment");
    
    echo "Connected to the database successfully.<br>";
    
    // Read and execute the SQL file
    $sql_file = file_get_contents('db_setup.sql');
    
    // Split into individual SQL statements
    $sql_statements = explode(';', $sql_file);
    
    foreach ($sql_statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                echo "Error executing SQL statement: " . $e->getMessage() . "<br>";
                echo "Statement: " . $statement . "<br><br>";
            }
        }
    }
    
    echo "Database setup completed successfully!<br>";
    echo "You can now <a href='index.php'>visit the homepage</a> or <a href='login.php'>login</a> with username: admin, password: admin123.";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>