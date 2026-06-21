<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: admin/roles.php
// Description: Allows Administrators to edit users' roles securely.

// Enforce admin middleware
require_once __DIR__ . "/../middleware/admin.php";

// Include database setup
require_once __DIR__ . "/../config/database.php";

$error = "";
$user_id = $_GET["id"] ?? "";

// Check if user ID is empty
if (empty($user_id)) {
    $_SESSION["error"] = "No user ID specified.";
    header("Location: users.php");
    exit;
}

// Fetch user information
try {
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION["error"] = "User account not found.";
        header("Location: users.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION["error"] = "System Error: Could not retrieve account details.";
    header("Location: users.php");
    exit;
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_role = $_POST["role"] ?? "";

    // Validate enum roles
    if ($new_role !== "admin" && $new_role !== "editor") {
        $error = "Invalid role value selected.";
    } else {
        try {
            // Update using secure prepared statement
            $update_stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
            $update_stmt->execute([
                'role' => $new_role,
                'id' => $user_id
            ]);

            // Save success flag and redirect
            $_SESSION["success"] = "Role for user " . htmlspecialchars($user['username']) . " updated successfully to " . $new_role . "!";
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $error = "System Error: Could not update user role.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role - Admin Panel</title>
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
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people-fill"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link logout-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3 bg-white">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock text-primary display-4"></i>
                            <h2 class="fw-bold mt-2">Change User Role</h2>
                            <p class="text-muted">Modify role settings for <strong><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        </div>

                        <!-- Error Alerts -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form action="roles.php?id=<?php echo $user['id']; ?>" method="POST">
                            <div class="mb-4">
                                <label for="role" class="form-label fw-semibold">Select Role</label>
                                <select name="role" id="role" class="form-select form-select-lg">
                                    <option value="editor" <?php echo ($user['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                                <i class="bi bi-save-fill"></i> Save Changes
                            </button>
                            <a href="users.php" class="btn btn-outline-secondary w-100 py-2 fw-semibold">Cancel</a>
                        </form>
                    </div>
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
