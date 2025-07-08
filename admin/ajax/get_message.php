<?php
require_once '../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing message ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($message) {
        // Format date
        $message['created_at'] = date('M j, Y g:i A', strtotime($message['created_at']));
        
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
    }
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 