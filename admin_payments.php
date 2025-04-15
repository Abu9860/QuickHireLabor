<?php
require_once 'config.php';

// Check if user is logged in and is a laborer
if (!isLoggedIn() || !isLaborer()) {
    header("Location: login.php");
    exit();
}

// Handle job completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_job') {
    $job_id = (int)$_POST['job_id'];
    $laborer_id = $_SESSION['user_id'];
    
    // Verify job belongs to laborer
    $stmt = $conn->prepare("
        SELECT j.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
               u.id as customer_id
        FROM jobs j 
        JOIN users u ON j.customer_id = u.id 
        WHERE j.id = ? AND j.laborer_id = ? AND j.status = 'assigned'
    ");
    $stmt->bind_param("ii", $job_id, $laborer_id);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    
    if ($job) {
        // Start transaction
        $conn->begin_transaction();
        
        // Update job status
        $stmt = $conn->prepare("UPDATE jobs SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        if ($stmt->execute()) {
            // Create notifications
            if (function_exists('createNotification')) {
                createNotification(
                    $job['customer_id'],
                    'Job Completed',
                    "Job '{$job['title']}' has been marked as completed by the laborer.",
                    'job_status'
                );
                
                createNotification(
                    $laborer_id,
                    'Job Completed',
                    "You have marked job '{$job['title']}' as completed.",
                    'job_status'
                );
            }
            
            $conn->commit();
            $response = ['success' => true, 'message' => 'Job marked as completed successfully!'];
        } else {
            throw new Exception('Failed to update job status');
        }
    } else {
        $response = ['success' => false, 'message' => 'Invalid job or wrong status'];
    }
    
    echo json_encode($response);
    exit();
}

// Get user jobs
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT j.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.phone as customer_phone
    FROM jobs j
    JOIN users u ON j.customer_id = u.id
    WHERE j.laborer_id = ?
    ORDER BY j.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate job statistics
$total_jobs = count($jobs);
$completed_jobs = 0;
$active_jobs = 0;

foreach ($jobs as $job) {
    if ($job['status'] === 'completed') {
        $completed_jobs++;
    } else if ($job['status'] === 'assigned' || $job['status'] === 'pending') {
        $active_jobs++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Jobs | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin-left: 280px;
            padding: 20px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .jobs-grid {
            display: grid;
            gap: 20px;
        }
        
        .job-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending { background: #ffeeba; color: #856404; }
        .status-assigned { background: #b8daff; color: #004085; }
        .status-completed { background: #c3e6cb; color: #155724; }
        .status-cancelled { background: #f5c6cb; color: #721c24; }
        
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .job-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-complete { background: #28a745; color: white; }
        .btn-message { background: #17a2b8; color: white; }
        .btn-cancel { background: #dc3545; color: white; }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
        }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <div class="container">
        <h1>My Jobs</h1>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Jobs</h3>
                <div class="stat-number"><?php echo $total_jobs; ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed Jobs</h3>
                <div class="stat-number"><?php echo $completed_jobs; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Jobs</h3>
                <div class="stat-number"><?php echo $active_jobs; ?></div>
            </div>
        </div>
        
        <div class="filters">
            <select id="status-filter" class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            
            <select id="date-filter" class="filter-select">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        
        <div class="jobs-grid">
            <?php foreach ($jobs as $job): ?>
                <div class="job-card" data-status="<?php echo htmlspecialchars($job['status']); ?>">
                    <div class="job-header">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <span class="status-badge status-<?php echo $job['status']; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </div>
                    
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p><strong>Price:</strong> ₹<?php echo number_format(isset($job['price']) ? $job['price'] : ($job['budget'] ?? 0), 2); ?></p>
                    
                    <div class="customer-info">
                        <h4>Customer Details</h4>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($job['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($job['customer_phone'] ?? ''); ?></p>
                    </div>
                    
                    <?php if ($job['status'] === 'assigned'): ?>
                        <div class="job-actions">
                            <button class="btn btn-complete" onclick="completeJob(<?php echo $job['id']; ?>)">
                                Mark as Complete
                            </button>
                            <button class="btn btn-message" onclick="messageCustomer(<?php echo $job['id']; ?>)">
                                Message Customer
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Filtering functionality
        document.getElementById('status-filter').addEventListener('change', filterJobs);
        document.getElementById('date-filter').addEventListener('change', filterJobs);
        
        function filterJobs() {
            const statusFilter = document.getElementById('status-filter').value;
            const dateFilter = document.getElementById('date-filter').value;
            
            const jobCards = document.querySelectorAll('.job-card');
            
            jobCards.forEach(card => {
                let visible = true;
                
                // Status filter
                if (statusFilter && card.dataset.status !== statusFilter) {
                    visible = false;
                }
                
                // Date filter could be implemented here
                
                card.style.display = visible ? 'block' : 'none';
            });
        }
        
        // Complete job functionality
        function completeJob(jobId) {
            if (confirm('Are you sure you want to mark this job as complete?')) {
                fetch('laborer_my_jobs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=complete_job&job_id=${jobId}`
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
                    alert('An error occurred. Please try again.');
                });
            }
        }
        
        // Message customer functionality
        function messageCustomer(jobId) {
            const message = prompt('Enter your message to the customer:');
            if (message) {
                // Add message sending functionality here
                alert('Message functionality will be implemented soon.');
            }
        }
    </script>
</body>
</html>