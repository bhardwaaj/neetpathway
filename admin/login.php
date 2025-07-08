<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            $query = "SELECT * FROM admin_users WHERE username = :username AND is_active = 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            if ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // For the default admin account with password 'admin123'
                if ($username === 'admin' && $password === 'admin123' && $admin['password'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') {
                    // Update password to new hashed version
                    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
                    $stmt->execute([':password' => $new_hash, ':id' => $admin['id']]);
                    
                    // Set session and proceed with login
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    // Update last login
                    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
                    $stmt->execute([':id' => $admin['id']]);
                    
                    // Log activity
                    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (:admin_id, 'login', 'Admin logged in', :ip)");
                    $stmt->execute([
                        ':admin_id' => $admin['id'],
                        ':ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    header("Location: dashboard.php");
                    exit();
                }
                // For regular password verification
                elseif (password_verify($password, $admin['password'])) {
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    // Update last login
                    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
                    $stmt->execute([':id' => $admin['id']]);
                    
                    // Log activity
                    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (:admin_id, 'login', 'Admin logged in', :ip)");
                    $stmt->execute([
                        ':admin_id' => $admin['id'],
                        ':ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    header("Location: dashboard.php");
                    exit();
                }
            }
            
            $error = "Invalid username or password";
            
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Add sanitizeInput function if not defined
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NEET Pathway</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
            max-width: 150px;
            margin-bottom: 1rem;
        }
        .form-control {
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 0.6rem 1rem;
        }
        .btn-primary {
            padding: 0.6rem 1.2rem;
            font-size: 0.95rem;
            font-weight: 500;
            background: #1976d2;
            border: none;
            box-shadow: 0 2px 6px rgba(25, 118, 210, 0.2);
        }
        .btn-primary:hover {
            background: #1565c0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
        }
        .alert-danger {
            background-color: #fff2f2;
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-body p-4">
                <div class="login-header">
                    <img src="../images/logo.PNG" alt="NEET Pathway" class="img-fluid">
                    <h4 class="mb-1">Admin Login</h4>
                    <p class="text-muted">Sign in to manage NEET Pathway</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" name="username" required>
                            <div class="invalid-feedback">Please enter your username</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">Please enter your password</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Back to Website
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Form validation
    (function () {
        'use strict'
        
        var forms = document.querySelectorAll('.needs-validation')
        
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = this.previousElementSibling;
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
</body>
</html> 