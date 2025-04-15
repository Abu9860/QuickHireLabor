<?php
require_once '../config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customer_id = $_SESSION['user_id'];
    
    $sql = "SELECT j.*, u.name as laborer_name, u.phone as laborer_phone 
            FROM jobs j 
            LEFT JOIN users u ON j.laborer_id = u.id 
            WHERE j.customer_id = ? 
            ORDER BY j.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    
    echo json_encode($jobs);
    exit();
}
?>
