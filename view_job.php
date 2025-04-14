<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details with related information
$sql = "SELECT j.*, 
        c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
        l.name as laborer_name, l.email as laborer_email, l.phone as laborer_phone,
        p.status as payment_status, p.amount as payment_amount, p.created_at as payment_date,
        r.rating, r.feedback
        FROM jobs j
        JOIN users c ON j.customer_id = c.id
        LEFT JOIN users l ON j.laborer_id = l.id
        LEFT JOIN payments p ON p.job_id = j.id
        LEFT JOIN ratings r ON r.job_id = j.id
        WHERE j.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: job_management.php");
    exit();
}

$job = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Job - Quick-Hire Labor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="content">
        <header>
            <h2>Job Details #<?php echo $job_id; ?></h2>
            <a href="job_management.php" class="btn-back">Back to Jobs</a>
        </header>

        <div class="job-details">
            <div class="detail-section">
                <h3>Job Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Title:</label>
                        <span><?php echo htmlspecialchars($job['title']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Description:</label>
                        <span><?php echo nl2br(htmlspecialchars($job['description'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Price:</label>
                        <span>$<?php echo number_format($job['price'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span class="status-badge <?php echo $job['status']; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Created:</label>
                        <span><?php echo date('M d, Y H:i', strtotime($job['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Customer Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo $job['customer_name']; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo $job['customer_email']; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Phone:</label>
                        <span><?php echo $job['customer_phone']; ?></span>
                    </div>
                </div>
            </div>

            <?php if ($job['laborer_name']): ?>
            <div class="detail-section">
                <h3>Laborer Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo $job['laborer_name']; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo $job['laborer_email']; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Phone:</label>
                        <span><?php echo $job['laborer_phone']; ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($job['payment_status']): ?>
            <div class="detail-section">
                <h3>Payment Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Amount:</label>
                        <span>$<?php echo number_format($job['payment_amount'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span class="status-badge <?php echo $job['payment_status']; ?>">
                            <?php echo ucfirst($job['payment_status']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Date:</label>
                        <span><?php echo date('M d, Y H:i', strtotime($job['payment_date'])); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($job['rating']): ?>
            <div class="detail-section">
                <h3>Rating & Feedback</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Rating:</label>
                        <div class="rating-stars">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $job['rating'] ? '★' : '☆';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label>Feedback:</label>
                        <span><?php echo htmlspecialchars($job['feedback']); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="edit_job.php?id=<?php echo $job_id; ?>" class="btn-edit">Edit Job</a>
                <a href="job_management.php?action=delete&id=<?php echo $job_id; ?>" 
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this job?')">Delete Job</a>
            </div>
        </div>
    </div>

    <style>
        .job-details {
            max-width: 1000px;
            margin: 20px;
        }
        .detail-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-item label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #666;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
        }
        .status-badge.pending { background: #f39c12; }
        .status-badge.assigned { background: #3498db; }
        .status-badge.completed { background: #27ae60; }
        .status-badge.cancelled { background: #e74c3c; }
        .rating-stars {
            color: #FFD700;
            font-size: 18px;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .btn-back, .btn-edit, .btn-delete {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 10px;
            color: white;
            display: inline-block;
        }
        .btn-back { background: #2196F3; }
        .btn-edit { background: #4CAF50; }
        .btn-delete { background: #f44336; }
    </style>
</body>
</html>
