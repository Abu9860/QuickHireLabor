<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Delete user if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Check if user has any jobs, ratings, or payments
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM jobs WHERE customer_id = ? OR laborer_id = ?) as job_count,
            (SELECT COUNT(*) FROM ratings WHERE customer_id = ? OR laborer_id = ?) as rating_count,
            (SELECT COUNT(*) FROM payments p 
             JOIN jobs j ON p.job_id = j.id 
             WHERE j.customer_id = ? OR j.laborer_id = ?) as payment_count
    ");
    $stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $counts = $result->fetch_assoc();
    
    if ($counts['job_count'] > 0) {
        $delete_error = "Cannot delete user because they have associated jobs.";
    } elseif ($counts['rating_count'] > 0) {
        $delete_error = "Cannot delete user because they have associated ratings.";
    } elseif ($counts['payment_count'] > 0) {
        $delete_error = "Cannot delete user because they have associated payments.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $admin_id = $_SESSION['user_id']; // Prevent admin from deleting themselves
        $stmt->bind_param("ii", $user_id, $admin_id);
        
        if ($stmt->execute()) {
            $delete_success = "User deleted successfully.";
        } else {
            $delete_error = "Error deleting user: " . $stmt->error;
        }
    }
}

// Get all users except current admin
$users = [];
$sql = "SELECT id, name, email, role, phone, created_at 
        FROM users 
        WHERE id != ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Quick-Hire Labor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="content">
        <header>
            <h2>Manage Users</h2>
            <div class="header-actions">
                <a href="add_user.php" class="btn-add">Add New User</a>
            </div>
        </header>
        
        <?php if (isset($delete_success) || isset($delete_error)): ?>
            <div class="alert <?php echo isset($delete_success) ? 'success' : 'error'; ?>">
                <?php echo isset($delete_success) ? $delete_success : $delete_error; ?>
            </div>
        <?php endif; ?>

        <div class="table-section">
            <div class="table-header">
                <h3>All Users</h3>
                <div class="table-filters">
                    <select id="roleFilter" onchange="filterUsers()">
                        <option value="">All Roles</option>
                        <option value="customer">Customers</option>
                        <option value="laborer">Laborers</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($users)): ?>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                        <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterUsers() {
            const role = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll('table tr:not(:first-child)');
            
            rows.forEach(row => {
                const roleCell = row.cells[3].textContent.toLowerCase();
                row.style.display = !role || roleCell === role ? '' : 'none';
            });
        }
    </script>

    <style>
        .header-actions {
            margin-bottom: 20px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-filters select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
    </style>
</body>
</html>
