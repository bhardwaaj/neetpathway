<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
    // Log the logout activity
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address) VALUES (:admin_id, 'logout', 'Admin logged out', :ip)");
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit(); 