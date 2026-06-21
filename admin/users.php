<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: admin/users.php
// Description: User administration panel, restricted to administrative roles.

// Enforce admin middleware
require_once __DIR__ . "/../middleware/admin.php";

// Include database
require_once __DIR__ . "/../config/database.php";

$error = "";
$success = "";
$users = [];

// Session alert check
if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"];
    unset($_SESSION["success"]);
}
if (isset($_SESSION["error"])) {
    $error = $_SESSION["error"];
    unset($_SESSION["error"]);
}

try {
    // Fetch all users using a prepared statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, username, role FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Do not leak detailed query messages in production
    $error = "System Error: Could not fetch user directories.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="../index.php">
                <i class="bi bi-journal-code text-primary fs-3"></i>
                <span class="fw-bold">BlogSystem</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item active"><a class="nav-link active" href="users.php"><i class="bi bi-people-fill"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link logout-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        
        <!-- Header -->
        <div class="bg-white p-4 rounded-3 shadow-sm border mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h2 fw-bold text-dark mb-1">User Directory Management</h1>
                <p class="text-muted mb-0">Modify user roles, promote editors, or configure permissions.</p>
            </div>
            <a href="../dashboard.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Success & Error Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card shadow-sm border-0 rounded-3 bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h3 class="h5 fw-bold mb-0 text-dark">
                    <i class="bi bi-people text-primary me-2"></i> Registered Accounts
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;" class="ps-4">User ID</th>
                                <th style="width: 45%;">Username</th>
                                <th style="width: 25%;">Active Role</th>
                                <th style="width: 15%;" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold">#<?php echo $user['id']; ?></td>
                                    <td>
                                        <!-- XSS Protection: sanitizing usernames on output -->
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($user['role'] === 'admin') ? 'bg-primary' : 'bg-secondary'; ?> text-capitalize px-3 py-2">
                                            <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="roles.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary fw-semibold" title="Edit User Role">
                                            <i class="bi bi-shield-lock"></i> Change Role
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-muted py-4 mt-auto border-top border-primary border-4">
        <div class="container text-center">
            <p class="mb-0">Task 4: Secure Blog System | Admin: <span class="text-white fw-bold">Abhinav</span></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
