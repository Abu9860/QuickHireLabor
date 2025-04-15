<?php
require_once 'config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle application actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $application_id = (int)$_POST['application_id'];
        $action = $_POST['action'];
        
        // Validate application belongs to this customer's job
        $stmt = $conn->prepare("
            SELECT a.*, j.title as job_title, j.id as job_id, 
                   CONCAT(u.first_name, ' ', u.last_name) as laborer_name,
                   u.id as laborer_id
            FROM job_applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON a.laborer_id = u.id
            WHERE a.id = ? AND j.customer_id = ?
        ");
        $stmt->bind_param("ii", $application_id, $user_id);
        $stmt->execute();
        $app = $stmt->get_result()->fetch_assoc();
        
        if (!$app) {
            throw new Exception('Invalid application');
        }
        
        $conn->begin_transaction();
        
        if ($action === 'accept') {
            // Update application status
            $stmt = $conn->prepare("UPDATE job_applications SET status = 'accepted' WHERE id = ?");
            $stmt->bind_param("i", $application_id);
            $stmt->execute();
            
            // Assign laborer to job
            $stmt = $conn->prepare("UPDATE jobs SET laborer_id = ?, status = 'assigned' WHERE id = ?");
            $stmt->bind_param("ii", $app['laborer_id'], $app['job_id']);
            $stmt->execute();
            
            // Reject other applications for this job
            $stmt = $conn->prepare("UPDATE job_applications SET status = 'rejected' WHERE job_id = ? AND id != ?");
            $stmt->bind_param("ii", $app['job_id'], $application_id);
            $stmt->execute();
            
            // Create notification for the laborer
            createNotification(
                $app['laborer_id'],
                'Application Accepted',
                "Your application for job '{$app['job_title']}' has been accepted!",
                'application'
            );
            
            $response = ['success' => true, 'message' => 'Application accepted successfully!'];
        } 
        elseif ($action === 'reject') {
            // Update application status
            $stmt = $conn->prepare("UPDATE job_applications SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $application_id);
            $stmt->execute();
            
            // Create notification for the laborer
            createNotification(
                $app['laborer_id'],
                'Application Rejected',
                "Your application for job '{$app['job_title']}' was not selected.",
                'application'
            );
            
            $response = ['success' => true, 'message' => 'Application rejected'];
        }
        
        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

// Get all applications for customer's jobs
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, j.description as job_description, j.budget,
           CONCAT(u.first_name, ' ', u.last_name) as laborer_name,
           u.phone as laborer_phone,
           (SELECT AVG(rating) FROM ratings WHERE ratee_id = a.laborer_id) as avg_rating,
           (SELECT COUNT(*) FROM jobs WHERE laborer_id = a.laborer_id AND status = 'completed') as completed_jobs
    FROM job_applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.laborer_id = u.id
    WHERE j.customer_id = ?
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 900px;
            margin-left: 280px;
            padding: 20px;
        }
        
        .application-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .laborer-info {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .rating {
            color: #ffc107;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .application-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-accept { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/customer_sidebar.php'; ?>
    
    <div class="container">
        <h1>Job Applications</h1>
        
        <?php if (empty($applications)): ?>
            <div class="message-box">
                <p>You have no applications for your jobs yet.</p>
            </div>
        <?php endif; ?>
        
        <?php foreach ($applications as $app): ?>
            <div class="application-card">
                <div class="application-header">
                    <h3><?php echo htmlspecialchars($app['job_title']); ?></h3>
                    <span class="status-badge status-<?php echo $app['status']; ?>">
                        <?php echo ucfirst($app['status']); ?>
                    </span>
                </div>
                
                <div class="laborer-info">
                    <div>
                        <strong>Laborer:</strong> <?php echo htmlspecialchars($app['laborer_name']); ?>
                        <div class="rating">
                            <?php echo str_repeat('★', round($app['avg_rating'] ?? 0)); ?>
                            <?php echo str_repeat('☆', 5 - round($app['avg_rating'] ?? 0)); ?>
                        </div>
                        <div>Completed Jobs: <?php echo $app['completed_jobs']; ?></div>
                    </div>
                    <div>
                        <p><strong>Price Quote:</strong> Rs.<?php echo number_format($app['price_quote'], 2); ?></p>
                        <p><strong>Applied:</strong> <?php echo date('M j, Y', strtotime($app['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="cover-letter">
                    <strong>Cover Letter:</strong>
                    <p><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                </div>
                
                <?php if ($app['status'] === 'pending'): ?>
                    <div class="application-actions">
                        <button class="btn btn-accept" onclick="handleApplication(<?php echo $app['id']; ?>, 'accept')">
                            Accept Application
                        </button>
                        <button class="btn btn-reject" onclick="handleApplication(<?php echo $app['id']; ?>, 'reject')">
                            Decline
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script>
    function handleApplication(applicationId, action) {
        const confirmMsg = action === 'accept' 
            ? 'Are you sure you want to accept this application? This will assign the laborer to your job.'
            : 'Are you sure you want to reject this application?';
            
        if (confirm(confirmMsg)) {
            fetch('c_applications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${applicationId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        }
    }
    </script>
</body>
</html>
