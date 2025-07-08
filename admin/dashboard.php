<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

// Get statistics
$stats = [
    'total_users' => 0,
    'total_orders' => 0,
    'pending_counselling' => 0,
    'total_revenue' => 0
];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    // Get total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();

    // Get pending counselling sessions
    $stmt = $pdo->query("SELECT COUNT(*) FROM counselling_sessions WHERE status = 'pending'");
    $stats['pending_counselling'] = $stmt->fetchColumn();

    // Get total revenue
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>
<div class="page-header">
    <h1 class="page-title">Dashboard Overview</h1>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
        <i class="fas fa-print me-1"></i>Print Report
    </button>
</div>
<!-- Statistics Cards -->
<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-primary fw-bold mb-1">Total Users</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                    <div class="small text-muted">Registered users</div>
                </div>
                <div class="ms-3">
                    <span class="bg-primary bg-opacity-10 p-2 rounded">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-success fw-bold mb-1">Total Orders</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total_orders']); ?></h3>
                    <div class="small text-muted">All time orders</div>
                </div>
                <div class="ms-3">
                    <span class="bg-success bg-opacity-10 p-2 rounded">
                        <i class="fas fa-shopping-cart fa-2x text-success"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-info fw-bold mb-1">Pending Sessions</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['pending_counselling']); ?></h3>
                    <div class="small text-muted">Awaiting counselling</div>
                </div>
                <div class="ms-3">
                    <span class="bg-info bg-opacity-10 p-2 rounded">
                        <i class="fas fa-calendar-check fa-2x text-info"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-warning fw-bold mb-1">Total Revenue</h6>
                    <h3 class="mb-0">₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <div class="small text-muted">All time earnings</div>
                </div>
                <div class="ms-3">
                    <span class="bg-warning bg-opacity-10 p-2 rounded">
                        <i class="fas fa-rupee-sign fa-2x text-warning"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Recent Activity and Quick Actions -->
<div class="row g-3 mt-0">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Activity</h5>
                <a href="activity_log.php" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT aal.*, au.username 
                                    FROM admin_activity_log aal
                                    JOIN admin_users au ON aal.admin_id = au.id
                                    ORDER BY aal.created_at DESC
                                    LIMIT 5
                                ");
                                while($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                error_log("Error: " . $e->getMessage());
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="users.php" class="quick-action-btn">
                        <i class="fas fa-user-plus fa-lg text-primary"></i>
                        <div>
                            <h6 class="mb-0">Manage Users</h6>
                            <small class="text-muted">Add, edit or remove users</small>
                        </div>
                    </a>
                    <a href="orders.php" class="quick-action-btn">
                        <i class="fas fa-shopping-cart fa-lg text-success"></i>
                        <div>
                            <h6 class="mb-0">View Orders</h6>
                            <small class="text-muted">Manage customer orders</small>
                        </div>
                    </a>
                    <a href="messages.php" class="quick-action-btn">
                        <i class="fas fa-envelope fa-lg text-info"></i>
                        <div>
                            <h6 class="mb-0">Check Messages</h6>
                            <small class="text-muted">View contact form messages</small>
                        </div>
                    </a>
                    <a href="chat.php" class="quick-action-btn">
                        <i class="fas fa-comments fa-lg text-warning"></i>
                        <div>
                            <h6 class="mb-0">Admin Chat</h6>
                            <small class="text-muted">Chat with other admins</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?> 