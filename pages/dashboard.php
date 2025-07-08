<?php
requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get user's orders
$query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's feedback
$query = "SELECT f.*, o.plan_type FROM feedback f 
          JOIN orders o ON f.order_id = o.id 
          WHERE f.user_id = :user_id 
          ORDER BY f.created_at DESC LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$recent_feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user data
$user = getUserData();
?>

<div class="container py-5">
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
                        <a href="index.php?page=dashboard" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="index.php?page=profile" class="list-group-item list-group-item-action">
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
            <!-- Welcome Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h4>
                            <p class="text-muted mb-0">Here's what's happening with your account.</p>
                        </div>
                        <a href="index.php?page=counselling" class="btn btn-primary">Book New Service</a>
                    </div>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Active Orders</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND status IN ('pending', 'processing')");
                                        $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Completed Orders</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND status = 'completed'");
                                        $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star fa-2x text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Average Rating</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $stmt = $conn->prepare("SELECT ROUND(AVG(rating), 1) FROM feedback WHERE user_id = :user_id");
                                        $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                        echo $stmt->fetchColumn() ?: 'N/A';
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="index.php?page=orders" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No orders found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['plan_type']; ?></td>
                                            <td>₹<?php echo number_format($order['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($order['status']) {
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Recent Feedback -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Feedback</h5>
                        <a href="index.php?page=feedback" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_feedback)): ?>
                        <p class="text-center py-4 mb-0">No feedback submitted yet</p>
                    <?php else: ?>
                        <?php foreach ($recent_feedback as $feedback): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0"><?php echo $feedback['plan_type']; ?></h6>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($feedback['comment']); ?></p>
                                <small class="text-muted"><?php echo date('d M Y', strtotime($feedback['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 