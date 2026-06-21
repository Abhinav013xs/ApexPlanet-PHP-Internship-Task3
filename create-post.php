<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: create-post.php
// Description: Upgraded post creator including JS client validation, server validation, and session user associations.

// Enforce auth session middleware
require_once "middleware/auth.php";

// Include database connection
require_once "config/database.php";

$error = "";
$title = "";
$content = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $user_id = $_SESSION["user_id"];

    // Server-Side Form Validation
    if (empty($title)) {
        $error = "Post title is required.";
    } elseif (empty($content)) {
        $error = "Post content is required.";
    } elseif (strlen($content) < 10) {
        $error = "Post content must be at least 10 characters long.";
    } else {
        try {
            // Prepare insert statement, binding user_id from session for authorship tracking
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'user_id' => $user_id
            ]);

            // Save success flag and redirect
            $_SESSION["success"] = "Post created successfully!";
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            error_log("Post Create Query Error: " . $e->getMessage());
            $error = "An error occurred writing to the database. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Blog Management System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navigation Bar -->
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item">
                        <a class="nav-link logout-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout (<?php echo htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8'); ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-arrow-left"></i></a>
                            <div>
                                <h2 class="fw-bold mb-0">Create New Post</h2>
                                <p class="text-muted mb-0">Publish a new article to the public homepage</p>
                            </div>
                        </div>

                        <!-- Error Alerts -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Client-Side JS Alert Placeholder -->
                        <div id="js-error-alert" class="alert alert-danger d-none align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div id="js-error-msg"></div>
                        </div>

                        <!-- Post Creation Form -->
                        <form id="create-post-form" action="create-post.php" method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Post Title</label>
                                <!-- XSS Protection: sanitizing title value on display -->
                                <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Enter an engaging title" required value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-semibold">Content</label>
                                <!-- XSS Protection: sanitizing content value on display -->
                                <textarea name="content" id="content" class="form-control" placeholder="Write your post content here..." rows="8" required><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                    <i class="bi bi-send-fill"></i> Publish Post
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary px-4 py-2 fw-semibold">Cancel</a>
                            </div>
                        </form>
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
    document.getElementById("create-post-form").addEventListener("submit", function(event) {
        const titleInput = document.getElementById("title").value.trim();
        const contentInput = document.getElementById("content").value.trim();
        const errorAlert = document.getElementById("js-error-alert");
        const errorMsg = document.getElementById("js-error-msg");
        
        let clientError = "";

        // Reset errors state
        errorAlert.classList.add("d-none");

        // Validate lengths
        if (titleInput === "") {
            clientError = "Post title is required.";
        } else if (contentInput === "") {
            clientError = "Post content is required.";
        } else if (contentInput.length < 10) {
            clientError = "Post content must be at least 10 characters long.";
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
