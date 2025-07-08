<?php
session_start();
require_once 'config/database.php';

// Get the requested page
$page = $_GET['page'] ?? 'home';

// List of valid pages
$valid_pages = [
    'home',
    'about',
    'counselling',
    'mentorship',
    'contact',
    'login',
    'register',
    'profile',
    'orders',
    'feedback',
    'logout'
];

// Validate the page
if (!in_array($page, $valid_pages)) {
    $page = 'home';
}

// Check if the page requires authentication
$auth_required_pages = ['profile', 'orders', 'feedback'];
if (in_array($page, $auth_required_pages) && !isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: index.php?page=login");
    exit();
}

// Include header
require_once 'includes/header.php';

// Include the requested page
$page_path = "pages/{$page}.php";
if (file_exists($page_path)) {
    require_once $page_path;
} else {
    // Show 404 error if page doesn't exist
    echo '<div class="container py-5">
            <div class="text-center">
                <h1>404 - Page Not Found</h1>
                <p>The page you are looking for does not exist.</p>
                <a href="index.php" class="btn btn-primary">Go Home</a>
            </div>
          </div>';
}

// Include footer
require_once 'includes/footer.php';
?> 