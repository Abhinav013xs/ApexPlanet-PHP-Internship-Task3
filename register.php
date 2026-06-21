<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: register.php
// Description: Upgraded registration script containing both client-side and server-side validation checks and PDO prepared statements.

// Start the session
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once "config/database.php";

$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    // Server-Side Form Validation
    if (empty($username)) {
        $error = "Username is required.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (empty($password)) {
        $error = "Password is required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if username is already taken using a secure Prepared Statement
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $check_stmt->execute(['username' => $username]);
            
            if ($check_stmt->rowCount() > 0) {
                $error = "Username is already taken. Please choose another.";
            } else {
                // Securely hash the user password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert the new user (default role is 'editor' as set in the SQL schema)
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $insert_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password
                ]);
                
                $success = "Registration successful! You can now <a href='login.php' class='alert-link'>log in here</a>.";
            }
        } catch (PDOException $e) {
            // Avoid leaking system path/SQL errors
            error_log("Registration DB Error: " . $e->getMessage());
            $error = "An internal system error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blog Management System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Responsive Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-journal-code text-primary fs-3"></i>
                <span class="fw-bold">BlogSystem</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link active" href="register.php"><i class="bi bi-person-plus-fill"></i> Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-circle text-primary display-4"></i>
                            <h2 class="fw-bold mt-2">Create Account</h2>
                            <p class="text-muted">Register to start writing articles</p>
                        </div>

                        <!-- Error Alerts -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- JS Client-side Validation Alert placeholder -->
                        <div id="js-error-alert" class="alert alert-danger d-none align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div id="js-error-msg"></div>
                        </div>

                        <!-- Success Alerts -->
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                <div><?php echo $success; ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form id="register-form" action="register.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Choose Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Create Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-person-plus-fill"></i> Register
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none">Login here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-muted py-4 mt-auto border-top border-primary border-4">
        <div class="container text-center">
            <p class="mb-0">Task 4: Secure Blog System | Intern: <span class="text-white fw-bold">Abhinav</span></p>
        </div>
    </footer>

    <!-- Client-side Validation Handler -->
    <script>
    document.getElementById("register-form").addEventListener("submit", function(event) {
        const usernameInput = document.getElementById("username").value.trim();
        const passwordInput = document.getElementById("password").value;
        const errorAlert = document.getElementById("js-error-alert");
        const errorMsg = document.getElementById("js-error-msg");
        
        let clientError = "";

        // Reset errors state
        errorAlert.classList.add("d-none");

        // Validate fields length and format
        if (usernameInput === "") {
            clientError = "Username is required.";
        } else if (usernameInput.length < 3) {
            clientError = "Username must be at least 3 characters long.";
        } else if (passwordInput === "") {
            clientError = "Password is required.";
        } else if (passwordInput.length < 6) {
            clientError = "Password must be at least 6 characters long.";
        }

        if (clientError !== "") {
            event.preventDefault(); // Stop form submission
            errorMsg.textContent = clientError;
            errorAlert.classList.remove("d-none");
            errorAlert.classList.add("d-flex");
            window.scrollTo(0, 0); // Scroll to error display
        }
    });
    </script>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
