<?php
// Ensure user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Get user data for profile picture
$user_id = $_SESSION['user_id'];
$user = isset($user) ? $user : [
    'profile_pic' => 'images/default_profile.png'
];
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Quick-Hire Labor</h3>
    </div>
    
    <ul class="nav-links">
        <li><a href="customer_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'customer_dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="c_my_jobs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_my_jobs.php' ? 'class="active"' : ''; ?>>My Jobs</a></li>
        <li><a href="c_profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_profile.php' ? 'class="active"' : ''; ?>>Profile</a></li>
        <li><a href="c_search_labor.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_search_labor.php' ? 'class="active"' : ''; ?>>Search Laborers</a></li>
        <li><a href="c_post_job.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_post_job.php' ? 'class="active"' : ''; ?>>Post Job</a></li>
        <li><a href="c_job_status.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_job_status.php' ? 'class="active"' : ''; ?>>Job Status</a></li>
        <li><a href="c_ratings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_ratings.php' ? 'class="active"' : ''; ?>>Ratings & Reviews</a></li>
        <li><a href="c_payments.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_payments.php' ? 'class="active"' : ''; ?>>Payments</a></li>
        <li><a href="c_notification.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_notification.php' ? 'class="active"' : ''; ?>>Notifications</a></li>
        <li><a href="c_support.php" <?php echo basename($_SERVER['PHP_SELF']) == 'c_support.php' ? 'class="active"' : ''; ?>>Support</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<style>
    .sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        background: #4CAF50;
        padding: 20px 0;
        color: white;
    }

    .sidebar-header {
        text-align: center;
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .nav-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
</style>
