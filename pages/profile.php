<?php
requireLogin();

$user = getUserData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $pincode = $_POST['pincode'] ?? '';
    $education_level = $_POST['education_level'] ?? '';
    $neet_attempt_year = $_POST['neet_attempt_year'] ?? '';
    
    $errors = [];
    
    if (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long";
    }
    
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Please enter a valid 10-digit phone number";
    }
    
    if (empty($errors)) {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Update user table
        $query = "UPDATE users SET name = :name, phone = :phone WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ":name" => $name,
            ":phone" => $phone,
            ":id" => $_SESSION['user_id']
        ]);
        
        // Update user profile
        $query = "UPDATE user_profiles SET 
                 address = :address,
                 city = :city,
                 state = :state,
                 pincode = :pincode,
                 education_level = :education_level,
                 neet_attempt_year = :neet_attempt_year
                 WHERE user_id = :user_id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ":address" => $address,
            ":city" => $city,
            ":state" => $state,
            ":pincode" => $pincode,
            ":education_level" => $education_level,
            ":neet_attempt_year" => $neet_attempt_year,
            ":user_id" => $_SESSION['user_id']
        ]);
        
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
        
        // Refresh user data
        $user = getUserData();
    }
}
?>

<div class="container py-5">
    <?php if (isset($_GET['welcome']) && $_GET['welcome'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Welcome to NEET Pathway! Please complete your profile to get personalized guidance.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                    <hr>
                    <div class="list-group list-group-flush">
                        <a href="index.php?page=profile" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a href="index.php?page=orders" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i> My Orders
                        </a>
                        <a href="index.php?page=feedback" class="list-group-item list-group-item-action">
                            <i class="fas fa-star me-2"></i> My Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Edit Profile</h4>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Education Level</label>
                                <select class="form-select" name="education_level">
                                    <option value="">Select Education Level</option>
                                    <option value="12th" <?php echo $user['education_level'] == '12th' ? 'selected' : ''; ?>>12th</option>
                                    <option value="Completed 12th" <?php echo $user['education_level'] == 'Completed 12th' ? 'selected' : ''; ?>>Completed 12th</option>
                                    <option value="Drop Year" <?php echo $user['education_level'] == 'Drop Year' ? 'selected' : ''; ?>>Drop Year</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">NEET Attempt Year</label>
                                <select class="form-select" name="neet_attempt_year">
                                    <option value="">Select Year</option>
                                    <?php 
                                    $current_year = date('Y');
                                    for ($year = $current_year; $year <= $current_year + 2; $year++) {
                                        $selected = $user['neet_attempt_year'] == $year ? 'selected' : '';
                                        echo "<option value=\"$year\" $selected>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($user['state']); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" class="form-control" name="pincode" value="<?php echo htmlspecialchars($user['pincode']); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 