<?php
session_start();

// MySQL Database connection settings
$host = 'localhost';
$dbname = 'neetpathway';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

// Error handling function
function handleError($error) {
    error_log($error);
    return ['success' => false, 'message' => 'An error occurred. Please try again later.'];
}

class Database {
    private $host = "localhost";
    private $db_name = "neetpathway";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

// Helper functions for authentication
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php?page=login");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserData() {
    if (!isLoggedIn()) return null;
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT u.*, up.* FROM users u 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              WHERE u.id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":id", $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to log user activity
function logUserActivity($user_id, $action, $description = '') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, action, description) VALUES (:user_id, :action, :description)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':action' => $action,
        ':description' => $description
    ]);
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number
function isValidPhone($phone) {
    return preg_match("/^[0-9]{10}$/", $phone);
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?> 
