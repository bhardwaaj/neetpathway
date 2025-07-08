<?php
require_once '../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['user_id']) || !isset($_POST['name']) || !isset($_POST['email'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$_POST['email'], $_POST['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Email already taken']);
        exit;
    }
    
    // Update user
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, 
            email = ?, 
            phone = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $success = $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'] ?? null,
        $_POST['user_id']
    ]);
    
    if ($success) {
        // Log activity
        $admin_id = $_SESSION['admin_id'];
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'update_user', ?)");
        $stmt->execute([$admin_id, "Updated user ID: " . $_POST['user_id']]);
    }
    
    echo json_encode(['success' => $success]);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 