<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: delete-post.php
// Description: Upgraded post deletion controller enforcing access control and ownership restrictions.

// Enforce auth session middleware
require_once "middleware/auth.php";

// Include database connection
require_once "config/database.php";

$post_id = $_GET["id"] ?? "";

// If ID is missing, redirect back
if (empty($post_id)) {
    $_SESSION["error"] = "No post ID specified for deletion.";
    header("Location: dashboard.php");
    exit;
}

try {
    // 1. Fetch the post first to verify existence and check ownership constraints
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        $_SESSION["error"] = "Post not found or already deleted.";
        header("Location: dashboard.php");
        exit;
    }

    // 2. SECURITY CHECK (RBAC): Enforce ownership permissions
    // Admin can delete any post, Editors can ONLY delete their own posts
    if ($_SESSION["role"] !== "admin" && (int)$post["user_id"] !== (int)$_SESSION["user_id"]) {
        $_SESSION["error"] = "Access Denied: You do not have permission to delete this article.";
        header("Location: dashboard.php");
        exit;
    }

    // 3. Delete using prepared statement
    $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = :id");
    $delete_stmt->execute(['id' => $post_id]);

    $_SESSION["success"] = "Post deleted successfully!";
} catch (PDOException $e) {
    error_log("Post Delete Query Error: " . $e->getMessage());
    $_SESSION["error"] = "An error occurred deleting the post from the database.";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>
