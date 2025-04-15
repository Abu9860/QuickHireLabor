<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
<<<<<<< HEAD
define('DB_NAME', 'dbv01');
=======
define('DB_NAME', 'lastop');
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper functions
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLaborer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'laborer';
}

function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

// Helper function to create notifications
function createNotification($user_id, $title, $message, $type = 'general') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}
?>
