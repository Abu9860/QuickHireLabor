<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php" <?php echo ($current_page == 'dashboard.php') ? 'class="active"' : ''; ?>>🏠 Dashboard</a></li>
        <li><a href="manage_users.php" <?php echo ($current_page == 'manage_users.php') ? 'class="active"' : ''; ?>>👤 Manage Users</a></li>
        <li><a href="job_management.php" <?php echo ($current_page == 'job_management.php') ? 'class="active"' : ''; ?>>💼 Job Management</a></li>
        <li><a href="payments.php" <?php echo ($current_page == 'payments.php') ? 'class="active"' : ''; ?>>💰 Payment Management</a></li>
        <li><a href="ratings.php" <?php echo ($current_page == 'ratings.php') ? 'class="active"' : ''; ?>>⭐ Ratings & Feedback</a></li>
        <li><a href="reports.php" <?php echo ($current_page == 'reports.php') ? 'class="active"' : ''; ?>>📊 Reports & Analytics</a></li>
        <li><a href="settings.php" <?php echo ($current_page == 'settings.php') ? 'class="active"' : ''; ?>>  ⚙  Settings</a></li>

        <li><a href="support.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
        <i class="fas fa-headset"></i><span>📞 Support Tickets</span>
        </a></li>
        <li><a href="profile.php" <?php echo ($current_page == 'profile.php') ? 'class="active"' : ''; ?>>✍ Update Profile</a></li>
        <li><a href="password.php" <?php echo ($current_page == 'password.php') ? 'class="active"' : ''; ?>>🔑 Change Password</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>
