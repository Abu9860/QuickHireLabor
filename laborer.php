<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a laborer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'laborer') {
    header("Location: login.php");
    exit();
}

// Get laborer data
$laborer_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$laborer = $stmt->get_result()->fetch_assoc();

// Get dashboard statistics
$stats = [
    'total_jobs' => 0,
    'completed_jobs' => 0,
    'pending_payment' => 0,
    'rating' => 0
];

// Get total jobs
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM jobs WHERE laborer_id = ?");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$stats['total_jobs'] = $stmt->get_result()->fetch_assoc()['total'];

// Get completed jobs
$stmt = $conn->prepare("SELECT COUNT(*) as completed FROM jobs WHERE laborer_id = ? AND status = 'completed'");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$stats['completed_jobs'] = $stmt->get_result()->fetch_assoc()['completed'];

// Get pending payments
$stmt = $conn->prepare("SELECT SUM(amount) as pending FROM payments WHERE job_id IN (SELECT id FROM jobs WHERE laborer_id = ?) AND status = 'pending'");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['pending_payment'] = $result['pending'] ?? 0;

// Get average rating
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE laborer_id = ?");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['rating'] = number_format($result['avg_rating'] ?? 0, 1);

// Get recent activities
$stmt = $conn->prepare("SELECT * FROM jobs WHERE laborer_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$recent_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laborer Dashboard | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar1">
        <div class="profilepic">
            <a href="laborer.php"> 
                <img src="<?php echo htmlspecialchars($laborer['profile_pic'] ?? 'images/default.png'); ?>" 
                     alt="Profile Picture" class="profile-img1">
            </a>
        </div>
        <h2><a href="laborer.php">Labor Module</a></h2>
        <ul>
            <li><a href="laborer_profile.php">Profile</a></li>
            <li><a href="laborer_jobs.php">Job Listings</a></li>
            <li><a href="laborer_status.php">Application Status</a></li>
            <li><a href="laborer_ratings.php">Ratings & Reviews</a></li>
            <li><a href="laborer_payments.php">Payment Tracking</a></li>
            <li><a href="laborer_notification.php">Notifications</a></li>
            <li><a href="module1.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content1">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($laborer['name']); ?></h1>
            <p>Find new job opportunities and track your work progress.</p>
        </header>

        <!-- Dashboard Overview -->
         
        <div class="dashboard-cards1">
            <a href="laborer_jobs.php">
                 <div class="card1">
               <h3>Total Jobs Applied</h3> 
                <p><?php echo $stats['total_jobs']; ?></p>
                  </div>
            </a>
            <a href="laborer_jobs.php">
            <div class="card1">
                <h3>Jobs Completed</h3>
                <p><?php echo $stats['completed_jobs']; ?></p>
            </div>
            </a>
            <a href="laborer_payments.php">
            <div class="card1">
                <h3>Pending Payments</h3>
                <p>Rs.<?php echo $stats['pending_payment']; ?></p>
            </div>
            </a>
            <a href="laborer_ratings.php">
            <div class="card1">
                <h3>Overall Rating</h3>
                <p><?php echo $stats['rating']; ?> ★</p>
            </div>
            </a>
        </div>
   
        <!-- Recent Activities -->
        <section class="recent-activities">
            <h2>Recent Activities</h2>
            <table>
                <tr>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($recent_activities as $activity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                    <td><?php echo htmlspecialchars($activity['status']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- Suggested Jobs -->
        <section class="suggested-jobs">
            <h2>Recommended Jobs</h2>
            <div class="job">
                <h3>Carpentry Work</h3>
                <p>Location: New York, NY</p>
                <p>Pay: Rs.200</p>
                <button>Apply Now</button>
            </div>
            <div class="job">
                <h3>Roof Repair</h3>
                <p>Location: Los Angeles, CA</p>
                <p>Pay: Rs.350</p>
                <button>Apply Now</button>
            </div>
        </section>

        <!-- Notifications -->
        <section class="notifications">
            <h2>Latest Notifications</h2>
            <ul>
                <li>🔔 New job available: Roofing Work ($300)</li>
                <li>✔ Your application for Electrical Repair is under review.</li>
                <li>💰 You received a payment of $100.</li>
            </ul>
        </section>
    </div>

</body>
</html>