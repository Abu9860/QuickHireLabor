<?php
// Ensure user is logged in and is a laborer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'laborer') {
    header("Location: login.php");
    exit();
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Quick-Hire Labor</h3>
    </div>
    
    <ul class="nav-links">
        <li><a href="laborer_dashboard.php">Dashboard</a></li>
        <li><a href="laborer_profile.php">Profile</a></li>
        <li><a href="laborer_jobs.php">Available Jobs</a></li>
        <li><a href="laborer_my_jobs.php">My Jobs</a></li>
        <li><a href="laborer_earnings.php">Earnings</a></li>
        <li><a href="laborer_reviews.php">Reviews</a></li>
        <li><a href="laborer_notifications.php">Notifications</a></li>
        <li><a href="laborer_settings.php">Settings</a></li>
        <li><a href="laborer_support.php">Support</a></li>
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

    .nav-links li a {
        color: white;
        text-decoration: none;
        padding: 15px 20px;
        display: block;
        transition: 0.3s;
    }

    .nav-links li a:hover {
        background: rgba(255,255,255,0.1);
    }

    .nav-links li a.active {
        background: rgba(255,255,255,0.2);
        border-left: 4px solid white;
    }
</style>
