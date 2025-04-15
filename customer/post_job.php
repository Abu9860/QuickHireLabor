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
        $title = sanitize_input($_POST['jobTitle']);
        $description = sanitize_input($_POST['jobDescription']);
        $location = sanitize_input($_POST['jobLocation']);
        $budget = floatval($_POST['budget']);
        $date = sanitize_input($_POST['jobDate']);
        $customer_id = $_SESSION['user_id'];
        $skills = isset($_POST['skills']) ? implode(',', $_POST['skills']) : '';

        $stmt = $conn->prepare("INSERT INTO jobs (title, description, location, price, scheduled_date, customer_id, required_skills) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdsss", $title, $description, $location, $budget, $date, $customer_id, $skills);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Job posted successfully';
        } else {
            throw new Exception('Failed to post job');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>
