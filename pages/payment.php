<?php
requireLogin();

if (!isset($_GET['order_id'])) {
    header("Location: index.php?page=orders");
    exit();
}

$order_id = $_GET['order_id'];

$db = new Database();
$conn = $db->getConnection();

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = :id AND o.user_id = :user_id
");

$stmt->execute([
    ':id' => $order_id,
    ':user_id' => $_SESSION['user_id']
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || $order['payment_status'] !== 'pending') {
    header("Location: index.php?page=orders");
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update order status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = 'paid', status = 'processing' 
            WHERE id = :id AND user_id = :user_id
        ");
        
        $stmt->execute([
            ':id' => $order_id,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        // Log activity
        $stmt = $conn->prepare("
            INSERT INTO user_activity_log (user_id, action, description, ip_address) 
            VALUES (:user_id, :action, :description, :ip)
        ");
        
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':action' => 'payment',
            ':description' => "Payment completed for order #{$order_id}",
            ':ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to success page
        header("Location: index.php?page=orders&payment=success");
        exit();
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Payment processing error: " . $e->getMessage());
        $error = "Payment processing failed. Please try again later.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Order Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Order Summary</h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-2">Order Number</p>
                            <p class="h5 mb-0">#<?php echo $order['id']; ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-2">Order Date</p>
                            <p class="h5 mb-0"><?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="border-top pt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-2">Plan Type</p>
                                <p class="h5 mb-0"><?php echo $order['plan_type']; ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="text-muted mb-2">Amount</p>
                                <p class="h5 mb-0">₹<?php echo number_format($order['amount']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Payment Details</h4>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="payment-form" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label">Card Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-credit-card"></i>
                                </span>
                                <input type="text" class="form-control" id="card_number" required 
                                       pattern="[0-9]{16}" placeholder="1234 5678 9012 3456">
                                <div class="invalid-feedback">Please enter a valid 16-digit card number</div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control" id="expiry_date" required 
                                           pattern="(0[1-9]|1[0-2])\/[0-9]{2}" placeholder="MM/YY">
                                    <div class="invalid-feedback">Please enter a valid expiry date (MM/YY)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="text" class="form-control" id="cvv" required 
                                           pattern="[0-9]{3,4}" placeholder="123">
                                    <div class="invalid-feedback">Please enter a valid CVV</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Card Holder Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="card_holder" required>
                                <div class="invalid-feedback">Please enter the card holder name</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1">Total Amount</h5>
                                <h3 class="mb-0 text-primary">₹<?php echo number_format($order['amount']); ?></h3>
                            </div>
                            <button type="submit" name="process_payment" class="btn btn-primary btn-lg">
                                Pay Now
                            </button>
                        </div>
                        
                        <div class="text-center text-muted">
                            <small>
                                <i class="fas fa-lock me-1"></i>
                                Your payment is secured with SSL encryption
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Other Payment Methods -->
            <div class="card shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="mb-4">Other Payment Methods</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100">
                                <i class="fab fa-google-pay me-2"></i> Google Pay
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100">
                                <i class="fas fa-qrcode me-2"></i> UPI
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100">
                                <i class="fas fa-university me-2"></i> Net Banking
                            </button>
                        </div>
                    </div>
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
                
                form.classList.add('was-validated')
            }, false)
        })
})()

// Format card number input
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 16) value = value.slice(0, 16);
    e.target.value = value.replace(/(\d{4})/g, '$1 ').trim();
});

// Format expiry date input
document.getElementById('expiry_date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) value = value.slice(0, 4);
    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2);
    }
    e.target.value = value;
});

// Format CVV input
document.getElementById('cvv').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) value = value.slice(0, 4);
    e.target.value = value;
});
</script> 