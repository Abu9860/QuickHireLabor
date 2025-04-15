<?php
require_once '../config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: ../login.php");
    exit();
}

// Handle POST request for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $name = sanitize_input($_POST['fullName']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $user_id = $_SESSION['user_id'];
        
        // Handle profile picture upload if provided
        $profile_pic = '';
        if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
            $profile_pic = handle_file_upload($_FILES['profilePic'], 'profile_pics');
        }
        
        $sql = "UPDATE users SET name = ?, phone = ?, address = ?";
        $params = [$name, $phone, $address];
        $types = "sss";
        
        if ($profile_pic) {
            $sql .= ", profile_pic = ?";
            $params[] = $profile_pic;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully';
        } else {
            throw new Exception('Failed to update profile');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Handle GET request for fetching profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT name, email, phone, address, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    
    echo json_encode($profile);
    exit();
}
?>
