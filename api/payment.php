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
        
        $job_id = (int)$_POST['job_id'];
        $amount = (float)$_POST['amount'];
        
        if (createPayment($job_id, $amount)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to create payment']);
        }
        break;
        
    case 'update_status':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        
        $payment_id = (int)$_POST['payment_id'];
        $status = sanitize_input($_POST['status']);
        
        if (updatePaymentStatus($payment_id, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update payment status']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
