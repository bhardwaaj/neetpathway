<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Log admin activity
function logAdminActivity($action, $description = '') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (:admin_id, :action, :description, :ip)");
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':action' => $action,
        ':description' => $description,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Check admin permissions
function checkAdminPermission($required_role = 'admin') {
    if ($_SESSION['admin_role'] !== $required_role) {
        header("Location: index.php?error=permission");
        exit();
    }
} 