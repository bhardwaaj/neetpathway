<?php
require_once 'config/database.php';  // adjust path if needed
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "Connected!";
} else {
    echo "Failed to connect.";
}
?>
