<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: logout.php
// Description: Secure logout script clearing session variables and cookie identifiers.

// Start the session to gain access
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session cookie in browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session on the server
session_destroy();

// Redirect back to the public homepage
header("Location: index.php");
exit;
?>
