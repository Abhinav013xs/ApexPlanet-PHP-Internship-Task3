<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: middleware/admin.php
// Description: Admin middleware checking active role permissions.

// First, enforce active authentication
require_once __DIR__ . "/auth.php";

// Verify if the active user role is 'admin'
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    // Return HTTP 403 Status Code
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - 403</title>
        <!-- Bootstrap 5 CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-5">
                            <i class="bi bi-shield-slash text-danger display-1 mb-3"></i>
                            <h1 class="fw-bold text-dark mb-2">403 Forbidden</h1>
                            <h2 class="h5 text-muted mb-4">Access Denied</h2>
                            <p class="text-secondary mb-4">
                                You do not have the required administrative permissions to access this page.
                            </p>
                            <a href="../dashboard.php" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
