<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'message'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
}

// Validate email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        INSERT INTO contacts (name, email, phone, message, is_read, created_at) 
        VALUES (?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
    ");
    
    $success = $stmt->execute([
        trim($_POST['name']),
        trim($_POST['email']),
        trim($_POST['phone']),
        trim($_POST['message'])
    ]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your message. We will get back to you soon!']);
    } else {
        throw new Exception('Failed to save message');
    }
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again later.']);
}
?> 