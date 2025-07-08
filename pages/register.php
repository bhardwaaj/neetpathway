<?php
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $errors[] = "Please fill in all fields";
    }
    
    if (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long";
    }
    
    if (!isValidEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (!isValidPhone($phone)) {
        $errors[] = "Please enter a valid 10-digit phone number";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $errors[] = "Email already registered";
            } else {
                // Begin transaction
                $conn->beginTransaction();
                
                try {
                    // Create new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)");
                    $stmt->execute([
                        ":name" => $name,
                        ":email" => $email,
                        ":phone" => $phone,
                        ":password" => $hashed_password
                    ]);
                    
                    $user_id = $conn->lastInsertId();
                    
                    // Create user profile
                    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (:user_id)");
                    $stmt->execute([":user_id" => $user_id]);
                    
                    // Log activity
                    $stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, action, description, ip_address) VALUES (:user_id, :action, :description, :ip)");
                    $stmt->execute([
                        ':user_id' => $user_id,
                        ':action' => 'register',
                        ':description' => 'New user registration',
                        ':ip' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Log the user in
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    
                    // Redirect to profile completion
                    header("Location: index.php?page=profile&welcome=1");
                    exit();
                    
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $conn->rollBack();
                    error_log("Registration error: " . $e->getMessage());
                    $errors[] = "Registration failed. Please try again later.";
                }
            }
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $errors[] = "Unable to connect to database. Please try again later.";
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="images/logo.PNG" alt="NEET Pathway" class="img-fluid mb-4" style="max-height: 100px;">
                        <h2 class="mb-0">Create Account</h2>
                        <p class="text-muted">Join NEET Pathway today</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                       required minlength="3">
                                <div class="invalid-feedback">Please enter your full name (minimum 3 characters)</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                       required pattern="[0-9]{10}">
                                <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password must be at least 6 characters long</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required>
                                <div class="invalid-feedback">Passwords must match</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">You must agree to the terms and conditions</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-4">
                            <i class="fas fa-user-plus me-2"></i> Create Account
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="index.php?page=login">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
                
                // Check if passwords match
                var password = form.querySelector('#password')
                var confirm = form.querySelector('#confirm_password')
                
                if (password.value !== confirm.value) {
                    confirm.setCustomValidity('Passwords must match')
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    confirm.setCustomValidity('')
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
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

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) value = value.slice(0, 10);
    e.target.value = value;
});
</script> 