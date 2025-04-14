<?php
require_once '../config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $job_id = (int)$_POST['job_id'];
        $amount = floatval($_POST['amount']);
        
        // Start transaction
        $conn->begin_transaction();
        
        // Create payment record
        $stmt = $conn->prepare("INSERT INTO payments (job_id, amount, status) VALUES (?, ?, 'completed')");
        $stmt->bind_param("id", $job_id, $amount);
        $stmt->execute();
        
        // Update job status
        $stmt = $conn->prepare("UPDATE jobs SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Payment processed successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// GET method to fetch payment history
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customer_id = $_SESSION['user_id'];
    
    $sql = "SELECT p.*, j.title as job_title 
            FROM payments p 
            JOIN jobs j ON p.job_id = j.id 
            WHERE j.customer_id = ? 
            ORDER BY p.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    echo json_encode($payments);
    exit();
}
?>
