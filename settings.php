<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$success = false;
$error = null;

// Create settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Default settings
$default_settings = [
    'site_name' => 'Quick-Hire Labor',
    'contact_email' => 'admin@lastop.com',
    'contact_phone' => '+01 123455678990',
    'service_fee_percentage' => '10',
    'min_job_price' => '50',
    'max_job_price' => '10000',
    'maintenance_mode' => '0'
];

// Insert default settings if not exists
foreach ($default_settings as $key => $value) {
    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->bind_param("ss", $key, $value);
    $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        $success = true;
    } catch (Exception $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Quick-Hire Labor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="content">
        <header>
            <h2>System Settings</h2>
        </header>

        <?php if ($success): ?>
            <div class="alert success">Settings updated successfully!</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="settings-form">
            <form method="POST" class="admin-form">
                <div class="form-section">
                    <h3>General Settings</h3>
                    <div class="form-group">
                        <label>Site Name:</label>
                        <input type="text" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Email:</label>
                        <input type="email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Phone:</label>
                        <input type="text" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Business Rules</h3>
                    <div class="form-group">
                        <label>Service Fee (%):</label>
                        <input type="number" name="settings[service_fee_percentage]" value="<?php echo htmlspecialchars($settings['service_fee_percentage']); ?>" min="0" max="100" required>
                    </div>
                    <div class="form-group">
<<<<<<< HEAD
                        <label>Minimum Job Price (₹):</label>
                        <input type="number" name="settings[min_job_price]" value="<?php echo htmlspecialchars($settings['min_job_price']); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Maximum Job Price (₹):</label>
=======
                        <label>Minimum Job Price ($):</label>
                        <input type="number" name="settings[min_job_price]" value="<?php echo htmlspecialchars($settings['min_job_price']); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Maximum Job Price ($):</label>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
                        <input type="number" name="settings[max_job_price]" value="<?php echo htmlspecialchars($settings['max_job_price']); ?>" min="0" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3>System Status</h3>
                    <div class="form-group">
                        <label>Maintenance Mode:</label>
                        <select name="settings[maintenance_mode]">
                            <option value="0" <?php echo $settings['maintenance_mode'] == '0' ? 'selected' : ''; ?>>Off</option>
                            <option value="1" <?php echo $settings['maintenance_mode'] == '1' ? 'selected' : ''; ?>>On</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Settings</button>
                    <button type="reset" class="btn-cancel">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .settings-form {
            max-width: 800px;
            margin: 20px;
        }
        .form-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-actions {
            margin-top: 20px;
        }
        .btn-submit, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-submit {
            background: #4CAF50;
            color: white;
        }
        .btn-cancel {
            background: #f44336;
            color: white;
        }
    </style>
</body>
</html>
