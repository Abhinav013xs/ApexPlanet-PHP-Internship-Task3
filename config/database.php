<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: config/database.php
// Description: Establishes a secure connection to the MySQL database using PDO, with generic error handling.

// Database configuration settings
$host = "localhost";
$db_name = "blog";
$username = "root";
$password = ""; // Default XAMPP password is empty

try {
    // Create a new PDO instance to connect to the database
    // Setting connection charset to utf8mb4 for secure handling of special characters
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to Exception so SQL errors throw catchable exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable emulation of prepared statements to prevent SQL Injection exploits
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $exception) {
    // SECURITY BEST PRACTICE: Avoid exposing SQL error details, server names, or database credentials.
    // Instead of using $exception->getMessage(), output a generic, professional message.
    error_log("Database connection failure: " . $exception->getMessage()); // Logs the details privately to the server log
    die("A secure connection to the database could not be established. Please try again later.");
}
?>
