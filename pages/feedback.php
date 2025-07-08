<?php
requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // Validate input
    $errors = [];
    
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $errors[] = "Please select a valid rating";
    }
    
    if (empty($comment)) {
        $errors[] = "Please provide feedback comment";
    }
    
    // Check if order exists and belongs to user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = :id AND user_id = :user_id AND status = 'completed'");
    $stmt->execute([
        ':id' => $order_id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$stmt->fetch()) {
        $errors[] = "Invalid order selected";
    }
    
    // Check if feedback already exists
    $stmt = $conn->prepare("SELECT id FROM feedback WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    
    if ($stmt->fetch()) {
        $errors[] = "Feedback already submitted for this order";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO feedback (user_id, order_id, rating, comment) VALUES (:user_id, :order_id, :rating, :comment)";
        $stmt = $conn->prepare($query);
        
        try {
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':order_id' => $order_id,
                ':rating' => $rating,
                ':comment' => $comment
            ]);
            
            $success = "Thank you for your feedback!";
        } catch (PDOException $e) {
            $errors[] = "Failed to submit feedback. Please try again.";
        }
    }
}

// Get user's feedback with pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total feedback count
$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$total_feedback = $stmt->fetchColumn();
$total_pages = ceil($total_feedback / $per_page);

// Get feedback for current page
$query = "SELECT f.*, o.plan_type 
          FROM feedback f 
          JOIN orders o ON f.order_id = o.id 
          WHERE f.user_id = :user_id 
          ORDER BY f.created_at DESC 
          LIMIT :offset, :per_page";
$stmt = $conn->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
$stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);
$stmt->execute();
$feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get completed orders without feedback
$query = "SELECT o.* FROM orders o 
          LEFT JOIN feedback f ON o.id = f.order_id 
          WHERE o.user_id = :user_id 
          AND o.status = 'completed' 
          AND f.id IS NULL";
$stmt = $conn->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$pending_feedback_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                    <hr>
                    <div class="list-group list-group-flush">
                        <a href="index.php?page=dashboard" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="index.php?page=profile" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a href="index.php?page=orders" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i> My Orders
                        </a>
                        <a href="index.php?page=feedback" class="list-group-item list-group-item-action active">
                            <i class="fas fa-star me-2"></i> My Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if (!empty($pending_feedback_orders)): ?>
                <!-- Submit Feedback Form -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Submit Feedback</h5>
                    </div>
                    <div class="card-body">
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
                            <div class="mb-3">
                                <label class="form-label">Select Order</label>
                                <select name="order_id" class="form-select" required>
                                    <option value="">Choose an order...</option>
                                    <?php foreach ($pending_feedback_orders as $order): ?>
                                        <option value="<?php echo $order['id']; ?>" <?php echo (isset($_GET['order_id']) && $_GET['order_id'] == $order['id']) ? 'selected' : ''; ?>>
                                            #<?php echo $order['id']; ?> - <?php echo $order['plan_type']; ?> (<?php echo date('d M Y', strtotime($order['created_at'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Your Feedback</label>
                                <textarea name="comment" class="form-control" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Feedback List -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">My Feedback History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($feedback_list)): ?>
                        <p class="text-center py-4 mb-0">No feedback submitted yet</p>
                    <?php else: ?>
                        <?php foreach ($feedback_list as $feedback): ?>
                            <div class="border-bottom pb-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        <?php echo $feedback['plan_type']; ?> 
                                        <small class="text-muted">(Order #<?php echo $feedback['order_id']; ?>)</small>
                                    </h6>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($feedback['comment']); ?></p>
                                <small class="text-muted">Submitted on <?php echo date('d M Y', strtotime($feedback['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="index.php?page=feedback&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    padding: 0 0.2em;
    font-size: 2rem;
    color: #ddd;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
}
</style> 