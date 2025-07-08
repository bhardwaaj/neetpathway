<?php
require_once '../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing order ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
               p.transaction_id, p.payment_method
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments p ON o.id = p.service_id AND p.service_type = 'mentorship'
        WHERE o.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order notes
        $stmt = $pdo->prepare("
            SELECT on.*, au.name as admin_name
            FROM order_notes on
            JOIN admin_users au ON on.admin_id = au.id
            WHERE on.order_id = ?
            ORDER BY on.created_at DESC
        ");
        $stmt->execute([$_GET['id']]);
        $order['notes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format dates
        $order['created_at'] = date('M j, Y g:i A', strtotime($order['created_at']));
        $order['updated_at'] = date('M j, Y g:i A', strtotime($order['updated_at']));
        
        foreach ($order['notes'] as &$note) {
            $note['created_at'] = date('M j, Y g:i A', strtotime($note['created_at']));
        }
        
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
    }
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 