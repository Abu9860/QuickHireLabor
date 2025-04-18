<?php
require_once 'config.php';

// Check if user is logged in and is a laborer
if (!isLoggedIn() || !isLaborer()) {
    header("Location: login.php");
    exit();
}

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
<<<<<<< HEAD
        $first_name = sanitize_input($_POST['firstName']);
        $last_name = sanitize_input($_POST['lastName']);
=======
        $name = sanitize_input($_POST['fullName']);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update user info
<<<<<<< HEAD
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssii", $first_name, $last_name, $phone, $address, $user_id);
=======
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update profile');
        }
        
        // Update skills
        $stmt = $conn->prepare("DELETE FROM laborer_skills WHERE laborer_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        if (!empty($skills)) {
            $stmt = $conn->prepare("INSERT INTO laborer_skills (laborer_id, skill_id) VALUES (?, ?)");
            foreach ($skills as $skill_id) {
                $stmt->bind_param("ii", $user_id, $skill_id);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode($response);
        exit();
    }
}

// Get user data
$user_id = $_SESSION['user_id'];
<<<<<<< HEAD
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Add null check before accessing array elements
if ($user) {
    // Safe to access user data
    $firstName = $user['first_name'];
    $lastName = $user['last_name'];
    $email = $user['email'];
    $phone = $user['phone'];
} else {
    // Handle case when user is not found
    $firstName = "Unknown";
    $lastName = "User";
    $email = "unknown@example.com";
    $phone = "000-000-0000";
}

// Get all available skills and laborer's current skills
$hasSkillsTable = $conn->query("SHOW TABLES LIKE 'skills'")->num_rows > 0;

if ($hasSkillsTable) {
    $stmt = $conn->prepare("
        SELECT s.*, IF(ls.laborer_id IS NOT NULL, 1, 0) as is_selected
        FROM skills s
        LEFT JOIN laborer_skills ls ON s.id = ls.skill_id AND ls.laborer_id = ?
        ORDER BY s.name
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Create a default skills array if the table doesn't exist
    $skills = [
        ['skill_name' => 'General Labor', 'experience' => 'Beginner'],
        ['skill_name' => 'Handyman', 'experience' => 'Intermediate']
    ];
}
=======
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get all available skills and laborer's current skills
$stmt = $conn->prepare("
    SELECT s.*, IF(ls.laborer_id IS NOT NULL, 1, 0) as is_selected
    FROM skills s
    LEFT JOIN laborer_skills ls ON s.id = ls.skill_id AND ls.laborer_id = ?
    ORDER BY s.name
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laborer Profile | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/laborer.css">
    <style>
        .profile-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
        }
        .form-group select[multiple] {
            height: 120px;
        }
        .btn-save {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        .btn-save:hover {
            background: #45a049;
        }
        .content header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .content header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .content header p {
            color: #666;
            margin: 10px 0 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <!-- Profile Content -->
    <div class="content">
        <header>
            <h2>Manage Your Profile</h2>
            <p>Update your details to keep your profile visible to employers.</p>
        </header>
        
        <?php if ($response['success']): ?>
            <div class="alert alert-success"><?php echo $response['message']; ?></div>
        <?php elseif ($response['message']): ?>
            <div class="alert alert-danger"><?php echo $response['message']; ?></div>
        <?php endif; ?>

        <form class="profile-form" method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
<<<<<<< HEAD
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" 
                       value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>

            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" 
                       value="<?php echo htmlspecialchars($lastName); ?>" required>
=======
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="fullName" 
                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
<<<<<<< HEAD
                       value="<?php echo htmlspecialchars($email); ?>" required readonly>
=======
                       value="<?php echo htmlspecialchars($user['email']); ?>" required readonly>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" 
<<<<<<< HEAD
                       value="<?php echo htmlspecialchars($phone); ?>" required>
=======
                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" 
<<<<<<< HEAD
                       value="<?php echo htmlspecialchars(isset($user['address']) ? $user['address'] : ''); ?>">
=======
                       value="<?php echo htmlspecialchars($user['address']); ?>">
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            </div>

            <div class="form-group">
                <label for="skills">Skills:</label>
<<<<<<< HEAD
                <?php if (isset($skills) && !empty($skills)): ?>
                    <select id="skills" name="skills[]" multiple>
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?php echo htmlspecialchars(isset($skill['id']) ? $skill['id'] : ''); ?>" 
                                    <?php echo isset($skill['is_selected']) && $skill['is_selected'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(isset($skill['name']) ? $skill['name'] : ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p>No skills listed yet.</p>
                <?php endif; ?>
=======
                <select id="skills" name="skills[]" multiple>
                    <?php foreach ($skills as $skill): 
                        $selected = $skill['is_selected'] ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($skill['id']); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($skill['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

</body>
</html>