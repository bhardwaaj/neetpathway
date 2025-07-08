<?php
requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get user's orders with pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total orders count
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Get orders for current page
$stmt = $conn->prepare("
    SELECT o.*, 
           COALESCE(f.rating, 0) as has_feedback 
    FROM orders o 
    LEFT JOIN feedback f ON o.id = f.order_id 
    WHERE o.user_id = :user_id 
    ORDER BY o.created_at DESC 
    LIMIT :offset, :per_page
");

$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
$stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update order status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'cancelled' 
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ");
        
        $stmt->execute([
            ':id' => $order_id,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $stmt = $conn->prepare("
                INSERT INTO user_activity_log (user_id, action, description, ip_address) 
                VALUES (:user_id, :action, :description, :ip)
            ");
            
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':action' => 'cancel_order',
                ':description' => "Cancelled order #{$order_id}",
                ':ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            // Commit transaction
            $conn->commit();
            
            $success = "Order cancelled successfully!";
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Order cancellation error: " . $e->getMessage());
        $error = "Failed to cancel order. Please try again later.";
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="user-avatar mb-3">
                        <div class="avatar-circle">
                            <span class="avatar-text"><?php echo substr($_SESSION['user_name'], 0, 1); ?></span>
                        </div>
                    </div>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                    <hr>
                    <div class="list-group list-group-flush">
                        <a href="index.php?page=dashboard" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line me-2"></i> Dashboard
                        </a>
                        <a href="index.php?page=profile" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-circle me-2"></i> Profile
                        </a>
                        <a href="index.php?page=orders" class="list-group-item list-group-item-action active">
                            <i class="fas fa-shopping-cart me-2"></i> My Orders
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
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i>My Orders</h5>
                        <a href="index.php?page=counselling" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Book New Service
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        Payment completed successfully! We will process your order shortly.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card-body p-0">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag empty-state-icon"></i>
                                <h4 class="mt-3">No Orders Found</h4>
                                <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                                <a href="index.php?page=counselling" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Book a Service
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>Order ID</th>
                                        <th><i class="fas fa-tag me-2"></i>Service</th>
                                        <th><i class="fas fa-rupee-sign me-2"></i>Amount</th>
                                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                                        <th><i class="fas fa-credit-card me-2"></i>Payment</th>
                                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                                        <th><i class="fas fa-cog me-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="fw-bold">#<?php echo $order['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas <?php 
                                                        echo match($order['plan_type']) {
                                                            'MBBS Counselling' => 'fa-user-md',
                                                            'BDS Counselling' => 'fa-tooth',
                                                            'AYUSH Counselling' => 'fa-leaf',
                                                            'BVSC Counselling' => 'fa-paw',
                                                            default => 'fa-graduation-cap'
                                                        };
                                                    ?> text-primary me-2"></i>
                                                    <?php echo $order['plan_type']; ?>
                                                </div>
                                            </td>
                                            <td>₹<?php echo number_format($order['amount']); ?></td>
                                            <td>
                                                <div class="status-badge status-<?php echo $order['status']; ?>">
                                                    <i class="fas <?php 
                                                        echo match($order['status']) {
                                                            'pending' => 'fa-clock',
                                                            'processing' => 'fa-spinner fa-spin',
                                                            'completed' => 'fa-check-circle',
                                                            'cancelled' => 'fa-times-circle',
                                                            default => 'fa-info-circle'
                                                        };
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst($order['status']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="status-badge payment-<?php echo $order['payment_status']; ?>">
                                                    <i class="fas <?php 
                                                        echo match($order['payment_status']) {
                                                            'pending' => 'fa-clock',
                                                            'paid' => 'fa-check-circle',
                                                            'failed' => 'fa-times-circle',
                                                            default => 'fa-info-circle'
                                                        };
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($order['status'] === 'completed' && !$order['has_feedback']): ?>
                                                        <a href="index.php?page=feedback&order_id=<?php echo $order['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Give Feedback">
                                                            <i class="fas fa-star"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($order['payment_status'] === 'pending'): ?>
                                                        <a href="index.php?page=payment&order_id=<?php echo $order['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success"
                                                           title="Complete Payment">
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($order['status'] === 'pending'): ?>
                                                        <form method="POST" action="" style="display: inline;">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <button type="submit" name="cancel_order" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Cancel Order"
                                                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info"
                                                            title="View Details"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#orderDetails<?php echo $order['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer bg-white">
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="index.php?page=orders&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* User Avatar */
.user-avatar {
    margin: 0 auto;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: #1a75ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.avatar-text {
    color: white;
    font-size: 2rem;
    font-weight: 600;
    text-transform: uppercase;
}

/* Empty State */
.empty-state {
    padding: 2rem;
    text-align: center;
}

.empty-state-icon {
    font-size: 4rem;
    color: #1a75ff;
    opacity: 0.5;
}

/* Status Badges */
.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.payment-pending {
    background: #fff3cd;
    color: #856404;
}

.payment-paid {
    background: #d4edda;
    color: #155724;
}

.payment-failed {
    background: #f8d7da;
    color: #721c24;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Table Improvements */
.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    color: #6c757d;
}

.table td {
    vertical-align: middle;
}

/* Responsive Design */
@media (max-width: 768px) {
    .action-buttons {
        flex-wrap: wrap;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
    }
}
</style> 