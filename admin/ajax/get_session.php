<?php
require_once '../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing session ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT cs.*, u.name as user_name, u.email as user_email, u.phone as user_phone
        FROM counselling_sessions cs 
        JOIN users u ON cs.user_id = u.id
        WHERE cs.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        // Format dates
        $session['session_date'] = date('Y-m-d', strtotime($session['session_date']));
        $session['created_at'] = date('Y-m-d H:i', strtotime($session['created_at']));
        if ($session['updated_at']) {
            $session['updated_at'] = date('Y-m-d H:i', strtotime($session['updated_at']));
        }
        
        echo json_encode(['success' => true, 'session' => $session]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Session not found']);
    }
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 