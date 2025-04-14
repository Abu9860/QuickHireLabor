<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();
    
    try {
        // Sanitize inputs
        $name = sanitize_input($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM newsletters WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already subscribed');
        }

        // Insert new subscription
        $stmt = $conn->prepare("INSERT INTO newsletters (name, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $email);
        
        if ($stmt->execute()) {
            // Send confirmation email
            $to = $email;
            $subject = "Newsletter Subscription Confirmation";
            $message = "Dear $name,\n\nThank you for subscribing to our newsletter!";
            $headers = "From: noreply@quickhirelabor.com";
            
            mail($to, $subject, $message, $headers);
            
            $response = array(
                'status' => 'success',
                'message' => 'Thank you for subscribing to our newsletter!'
            );
        } else {
            throw new Exception('Database error occurred');
        }
        
    } catch (Exception $e) {
        $response = array(
            'status' => 'error',
            'message' => $e->getMessage()
        );
    }
    
    echo json_encode($response);
    exit;
}
?>
