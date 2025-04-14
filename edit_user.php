<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = false;
$user = null;

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_users.php");
    exit();
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $phone = sanitize_input($_POST['phone']);
    $new_password = $_POST['new_password'];

    // Validate input
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";

    // Check if email exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already exists";
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            // Update with new password
            if (strlen($new_password) < 6) {
                $errors[] = "Password must be at least 6 characters";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $email, $hashed_password, $role, $phone, $user_id);
            }
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $role, $phone, $user_id);
        }

        if (empty($errors) && $stmt->execute()) {
            $success = true;
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Error updating user: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Quick-Hire Labor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="content">
        <header>
            <h2>Edit User: <?php echo htmlspecialchars($user['name']); ?></h2>
            <a href="manage_users.php" class="btn-back">Back to Users</a>
        </header>

        <?php if ($success): ?>
            <div class="alert success">User updated successfully!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current):</label>
                    <input type="password" id="new_password" name="new_password" minlength="6">
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <?php 
                        $roles = ['customer', 'laborer', 'admin'];
                        foreach ($roles as $role): 
                        ?>
                            <option value="<?php echo $role; ?>" <?php echo $user['role'] == $role ? 'selected' : ''; ?>>
                                <?php echo ucfirst($role); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update User</button>
                    <a href="manage_users.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-section {
            max-width: 600px;
            margin: 20px;
        }
        .admin-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-back {
            background: #2196F3;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-submit, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-submit { background: #4CAF50; color: white; }
        .btn-cancel { background: #f44336; color: white; }
    </style>
</body>
</html>
