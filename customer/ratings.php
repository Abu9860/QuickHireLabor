<?php
require_once '../config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: ../login.php");
    exit();
}

// Handle POST request for submitting ratings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $job_id = (int)$_POST['job_id'];
        $rating = (int)$_POST['rating'];
        $feedback = sanitize_input($_POST['feedback']);
        $customer_id = $_SESSION['user_id'];
        
        // Get laborer_id from job
        $stmt = $conn->prepare("SELECT laborer_id FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        
        if (!$job) {
            throw new Exception('Job not found');
        }
        
        $stmt = $conn->prepare("INSERT INTO ratings (job_id, customer_id, laborer_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $job_id, $customer_id, $job['laborer_id'], $rating, $feedback);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Rating submitted successfully';
        } else {
            throw new Exception('Failed to submit rating');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Handle GET request for fetching ratings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customer_id = $_SESSION['user_id'];
    
    $sql = "SELECT r.*, j.title as job_title, u.name as laborer_name 
            FROM ratings r 
            JOIN jobs j ON r.job_id = j.id 
            JOIN users u ON r.laborer_id = u.id 
            WHERE r.customer_id = ? 
            ORDER BY r.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }
    
    echo json_encode($ratings);
    exit();
}
?>
