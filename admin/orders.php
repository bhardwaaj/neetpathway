<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        
        // Log activity
        $admin_id = $_SESSION['admin_id'];
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'update_order', ?)");
        $stmt->execute([$admin_id, "Updated order ID: " . $_POST['order_id'] . " status to: " . $_POST['status']]);
        
        $_SESSION['success'] = "Order status updated successfully";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating order status";
        error_log("Error: " . $e->getMessage());
    }
    header("Location: orders.php");
    exit();
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h3">Order Management</h1>
        <div class="btn-toolbar">
            <button type="button" class="btn btn-outline-secondary" onclick="exportOrders()">
                <i class="fas fa-download me-1"></i> Export Orders
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Orders Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Plan Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT o.*, u.name as user_name, u.email as user_email 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.id 
                                ORDER BY o.created_at DESC
                            ");
                            while($row = $stmt->fetch()) {
                                $statusClass = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$row['status']] ?? 'secondary';
                                
                                $paymentStatusClass = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger'
                                ][$row['payment_status']] ?? 'secondary';

                                echo "<tr>";
                                echo "<td>#" . str_pad($row['id'], 6, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td title='" . htmlspecialchars($row['user_email']) . "'>" . htmlspecialchars($row['user_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['plan_type']) . "</td>";
                                echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                                echo "<td><span class='badge bg-" . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                echo "<td><span class='badge bg-" . $paymentStatusClass . "'>" . ucfirst($row['payment_status']) . "</span></td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                                echo "<td>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<button type='button' class='btn btn-outline-primary' onclick='viewOrder(" . $row['id'] . ")'><i class='fas fa-eye'></i></button>";
                                echo "<button type='button' class='btn btn-outline-warning' onclick='updateStatus(" . $row['id'] . ")'><i class='fas fa-edit'></i></button>";
                                echo "</div>";
                                echo "</td>";
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

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Order Information</h6>
                        <p><strong>Order ID:</strong> <span id="order-id"></span></p>
                        <p><strong>Plan Type:</strong> <span id="plan-type"></span></p>
                        <p><strong>Amount:</strong> <span id="amount"></span></p>
                        <p><strong>Status:</strong> <span id="status"></span></p>
                        <p><strong>Payment Status:</strong> <span id="payment-status"></span></p>
                        <p><strong>Created:</strong> <span id="created-at"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Customer Information</h6>
                        <p><strong>Name:</strong> <span id="customer-name"></span></p>
                        <p><strong>Email:</strong> <span id="customer-email"></span></p>
                        <p><strong>Phone:</strong> <span id="customer-phone"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" id="updateOrderId" name="order_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#ordersTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function viewOrder(orderId) {
    fetch(`ajax/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.order) {
                const order = data.order;
                document.getElementById('order-id').textContent = `#${String(order.id).padStart(6, '0')}`;
                document.getElementById('plan-type').textContent = order.plan_type;
                document.getElementById('amount').textContent = `₹${parseFloat(order.amount).toFixed(2)}`;
                document.getElementById('status').textContent = order.status;
                document.getElementById('payment-status').textContent = order.payment_status;
                document.getElementById('created-at').textContent = order.created_at;
                document.getElementById('customer-name').textContent = order.user_name;
                document.getElementById('customer-email').textContent = order.user_email;
                document.getElementById('customer-phone').textContent = order.user_phone || 'Not provided';
                
                new bootstrap.Modal(document.getElementById('viewOrderModal')).show();
            } else {
                alert('Error loading order details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
        });
}

function updateStatus(orderId) {
    document.getElementById('updateOrderId').value = orderId;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

function exportOrders() {
    window.location.href = 'export_orders.php';
}
</script>

<?php require_once 'includes/footer.php'; ?> 