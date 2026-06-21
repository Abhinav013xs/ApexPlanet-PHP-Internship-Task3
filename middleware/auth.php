<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: middleware/auth.php
// Description: Authentication middleware to ensure only logged-in users access protected views.

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Hardening session cookie policies
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie expires when browser closes
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']), // Secure flag only on HTTPS
        'httponly' => true, // Guard against XSS reading session cookie
        'samesite' => 'Strict' // Mitigate CSRF
    ]);
    session_start();
}

// If session is empty or logged_in is not set, redirect to login page
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit;
}
?>
