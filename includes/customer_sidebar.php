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
<nav class="sidebar">
    <div class="sidebar-header">
        <h2>QuickHire Labor</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="customer_dashboard.php">Dashboard</a></li>
        <li><a href="c_post_job.php">Post a Job</a></li>
        <li><a href="c_job_status.php">My Jobs</a></li>
        <!-- Add new link to applications page -->
        <li><a href="c_applications.php">Job Applications</a></li>
        <li><a href="c_payments.php">Payments</a></li>
        <li><a href="c_support.php">Support</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
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
