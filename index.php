<?php
// Project: PHP & MySQL Blog Management System (Task 4)
// File: index.php
// Description: Public homepage showing posts, protected against XSS and SQL Injection via prepared statements.

// Start the session securely
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Include database configurations
require_once "config/database.php";

// Set up pagination configurations
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 5; // Display only 5 posts per page
$offset = ($page - 1) * $limit;

$posts = [];
$total_posts = 0;
$total_pages = 0;
$error_message = "";

try {
    // 1. Get the total count of all blog articles
    $count_stmt = $conn->query("SELECT COUNT(*) FROM posts");
    $total_posts = (int)$count_stmt->fetchColumn();
    
    // Calculate total pages
    $total_pages = ceil($total_posts / $limit);

    // 2. Fetch the paginated articles joined with authors (using PDO prepared statements)
    $stmt = $conn->prepare("
        SELECT posts.*, users.username 
        FROM posts 
        LEFT JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    // Bind parameters explicitly as integers to avoid SQL Injection in LIMIT/OFFSET clauses
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Index Retrieve Error: " . $e->getMessage());
    $error_message = "Could not retrieve articles due to an internal error.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Simple Blog System</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
                    </li>
                    
                    <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link logout-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout (<?php echo htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8'); ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="bi bi-person-plus-fill"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-5">
        
        <!-- Header Section and Search Bar -->
        <div class="row align-items-center mb-5 border-bottom pb-4">
            <div class="col-md-6 col-lg-7">
                <h1 class="display-5 fw-bold text-dark mb-1">Welcome to BlogSystem</h1>
                <p class="text-muted fs-5 mb-0">A secure blog site built using PHP, MySQL & Bootstrap 5.</p>
            </div>
            
            <!-- Public Search Form (Redirects to search.php) -->
            <div class="col-md-6 col-lg-5 mt-3 mt-md-0">
                <form action="search.php" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search blog posts..." required>
                        <button type="submit" class="btn btn-primary fw-semibold px-3">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Error Alerts -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?php echo htmlspecialchars($error_message); ?></div>
            </div>
        <?php endif; ?>

        <!-- Post Listings Cards Grid -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card shadow-sm border-0 rounded-3 mb-4 hover-card">
                            <div class="card-body p-4">
                                <!-- XSS Protection: sanitizing title on output -->
                                <h2 class="card-title h3 fw-bold mb-2"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                
                                <p class="card-subtitle text-muted mb-3 fs-7">
                                    <!-- XSS Protection: sanitizing username on output -->
                                    <i class="bi bi-person"></i> By: <strong><?php echo htmlspecialchars($post['username'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-calendar3"></i> Published on: <?php echo date('F d, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                                </p>
                                
                                <!-- XSS Protection: sanitizing content on output -->
                                <div class="card-text post-content-preview">
                                    <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination Navigation -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-5">
                            <ul class="pagination justify-content-center">
                                
                                <!-- Previous Button -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                </li>

                                <!-- Page Number Links -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Button -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5 bg-white border rounded-3 shadow-sm">
                        <i class="bi bi-journal-x text-muted display-1"></i>
                        <h3 class="fw-bold mt-3">No posts available</h3>
                        <p class="text-muted">There are no published articles to display right now.</p>
                        <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                            <a href="create-post.php" class="btn btn-primary fw-semibold px-4 mt-2">Create the first post!</a>
                        <?php else: ?>
                            <p class="mb-0">Please <a href="login.php" class="text-primary text-decoration-none fw-semibold">login</a> to publish the first article!</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-muted py-4 mt-auto border-top border-primary border-4">
        <div class="container text-center">
            <p class="mb-0">Task 4: Secure Blog System | Intern: <span class="text-white fw-bold">Abhinav</span></p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
