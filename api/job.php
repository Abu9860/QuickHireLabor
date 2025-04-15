<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        if (!isCustomer()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $service_id = (int)$_POST['service_id'];
        $price = (float)$_POST['price'];
        
        if (createJob($title, $description, $_SESSION['user_id'], $service_id, $price)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to create job']);
        }
        break;
        
    case 'update_status':
        $job_id = (int)$_POST['job_id'];
        $status = sanitize_input($_POST['status']);
        
        if (updateJobStatus($job_id, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update status']);
        }
        break;
        
    case 'assign':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        
        $job_id = (int)$_POST['job_id'];
        $laborer_id = (int)$_POST['laborer_id'];
        
        if (assignLaborer($job_id, $laborer_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to assign laborer']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
