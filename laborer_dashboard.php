<?php
require_once 'config.php';

// Check if user is logged in and is a laborer
if (!isLoggedIn() || !isLaborer()) {
    header("Location: login.php");
    exit();
}

// Get user data - only get name, remove profile_pic
$user_id = $_SESSION['user_id'];
<<<<<<< HEAD
$stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE id = ?");
=======
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get recent jobs
$stmt = $conn->prepare("
<<<<<<< HEAD
    SELECT 
        j.id, j.title, j.description, j.location, j.budget, j.status,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
        u.email AS customer_email,
        s.name AS service_name
    FROM jobs j
    JOIN users u ON j.customer_id = u.id
    LEFT JOIN services s ON j.service_id = s.id
    WHERE j.status = 'open'
    ORDER BY j.created_at DESC
    LIMIT 5
");
=======
    SELECT j.*, u.name as customer_name 
    FROM jobs j 
    LEFT JOIN users u ON j.customer_id = u.id 
    WHERE j.laborer_id = ? 
    ORDER BY j.created_at DESC LIMIT 5
");
$stmt->bind_param("i", $user_id);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
$stmt->execute();
$recent_jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get earnings stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_jobs,
<<<<<<< HEAD
        SUM(CASE WHEN status = 'completed' THEN budget ELSE 0 END) as total_earnings,
=======
        SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END) as total_earnings,
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
        AVG(rating) as avg_rating
    FROM jobs j
    LEFT JOIN ratings r ON j.id = r.job_id
    WHERE j.laborer_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laborer Dashboard - QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin-left: 280px;
            padding: 20px;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }

        .job-list {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .job-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .job-item:last-child {
            border-bottom: none;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <div class="container">
        <div class="welcome-banner">
<<<<<<< HEAD
            <h1>Welcome, <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Laborer'; ?>!</h1>
=======
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            <p>Here's your activity overview</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Jobs</h3>
                <div class="stat-number"><?php echo $stats['total_jobs']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Earnings</h3>
                <div class="stat-number">₹<?php echo number_format($stats['total_earnings'], 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Average Rating</h3>
                <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?> ★</div>
            </div>
        </div>

        <div class="job-list">
            <h2>Recent Jobs</h2>
            <?php if (empty($recent_jobs)): ?>
                <p>No recent jobs found</p>
            <?php else: ?>
                <?php foreach ($recent_jobs as $job): ?>
                    <div class="job-item">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p>Customer: <?php echo htmlspecialchars($job['customer_name']); ?></p>
                        <p>Status: <strong><?php echo ucfirst($job['status']); ?></strong></p>
<<<<<<< HEAD
                        <p>Price: ₹<?php echo number_format($job['budget'], 2); ?></p>
=======
                        <p>Price: ₹<?php echo number_format($job['price'], 2); ?></p>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
                        <small>Posted: <?php echo date('M j, Y', strtotime($job['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
