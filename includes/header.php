<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEET Pathway - Every Step Matters</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <?php
    session_start();
    require_once 'config/database.php';
    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.PNG" style="border-radius: 10px;" alt="NEET Pathway">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo !isset($_GET['page']) || $_GET['page'] == 'home' ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] == 'counselling' ? 'active' : ''; ?>" href="index.php?page=counselling">Counselling</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] == 'mentorship' ? 'active' : ''; ?>" href="index.php?page=mentorship">Mentorship</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] == 'about' ? 'active' : ''; ?>" href="index.php?page=about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] == 'contact' ? 'active' : ''; ?>" href="index.php?page=contact">Contact</a>
                    </li>
                </ul>
                <div class="nav-buttons">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="index.php?page=orders"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="index.php?page=feedback"><i class="fas fa-star me-2"></i>My Feedback</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="index.php?page=login" class="btn btn-outline-light nav-btn">Login</a>
                        <a href="index.php?page=register" class="btn btn-primary nav-btn">Register</a>
                    <?php endif; ?>
                    <a href="https://wa.me/7340409636" class="btn btn-connect nav-btn" target="_blank">
                        <i class="fab fa-whatsapp"></i>Connect Now
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <!-- Main Content Container -->
    <main class="main-content">
</body>
</html> 