<?php
require_once '../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['receiver_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing receiver_id']);
    exit;
}

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get messages where current user is either sender or receiver
    $stmt = $pdo->prepare("
        SELECT id, sender_id, receiver_id, message, created_at 
        FROM admin_chat_messages 
        WHERE id > ? AND (
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?)
        )
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([
        $last_id,
        $_SESSION['admin_id'],
        $_GET['receiver_id'],
        $_GET['receiver_id'],
        $_SESSION['admin_id']
    ]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark received messages as read
    if (!empty($messages)) {
        $stmt = $pdo->prepare("
            UPDATE admin_chat_messages 
            SET is_read = true 
            WHERE receiver_id = ? AND sender_id = ? AND is_read = false
        ");
        $stmt->execute([$_SESSION['admin_id'], $_GET['receiver_id']]);
    }
    
    echo json_encode([
        'success' => true,
        'messages' => array_map(function($msg) {
            $msg['created_at'] = date('M j, Y g:i A', strtotime($msg['created_at']));
            return $msg;
        }, $messages)
    ]);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 