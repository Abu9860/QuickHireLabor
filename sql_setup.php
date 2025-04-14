<?php
require_once 'config.php';

// Database connection parameters from config.php are already loaded
// $conn is already established from config.php

// Create database if it doesn't exist (will only work if user has CREATE privileges)
$sql = "CREATE DATABASE IF NOT EXISTS ".DB_NAME;
$conn->query($sql);

// Ensure we're using the correct database
$conn->select_db(DB_NAME);

// Array of SQL statements to create all necessary tables
$tables = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50),
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer', 'laborer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Newsletter Subscriptions Table
    "CREATE TABLE IF NOT EXISTS newsletters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Support Tickets Table
    "CREATE TABLE IF NOT EXISTS support_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
        admin_response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        responded_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    // Services Table
    "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        price_range VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Jobs Table
    "CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        customer_id INT NOT NULL,
        laborer_id INT,
        service_id INT,
        location VARCHAR(255) NOT NULL,
        budget DECIMAL(10,2),
        status ENUM('open', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id),
        FOREIGN KEY (service_id) REFERENCES services(id),
        FOREIGN KEY (laborer_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    
    // Applications Table
    "CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        laborer_id INT NOT NULL,
        cover_letter TEXT,
        price_quote DECIMAL(10,2),
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id),
        FOREIGN KEY (laborer_id) REFERENCES users(id)
    )",
    
    // Notifications Table
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('general', 'job', 'application', 'payment', 'admin') DEFAULT 'general',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    // Reviews Table
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        reviewer_id INT NOT NULL,
        reviewed_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id),
        FOREIGN KEY (reviewer_id) REFERENCES users(id),
        FOREIGN KEY (reviewed_id) REFERENCES users(id)
    )",
    
    // Payments Table
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        transaction_id VARCHAR(100),
        status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id)
    )",
    
    // Contacts Table
    "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Laborer Settings Table
    "CREATE TABLE IF NOT EXISTS `laborer_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `is_available` tinyint(1) DEFAULT 1,
        `max_distance` int(11) DEFAULT 50,
        `min_pay` decimal(10,2) DEFAULT 0.00,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_id` (`user_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    )",
    
    // Notification Preferences Table
    "CREATE TABLE IF NOT EXISTS `notification_preferences` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `email_enabled` tinyint(1) DEFAULT 1,
        `sms_enabled` tinyint(1) DEFAULT 0,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_id` (`user_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    )"
];

// Execute each SQL statement
$errors = [];
$success = true;

echo "<h2>Database Setup Progress</h2>";
echo "<ul>";

foreach ($tables as $sql) {
    // Extract table name for reporting
    preg_match('/CREATE TABLE IF NOT EXISTS ([^\s(]+)/', $sql, $matches);
    $tableName = isset($matches[1]) ? $matches[1] : "unknown";
    
    if ($conn->query($sql) === TRUE) {
        echo "<li>✅ Table '{$tableName}' created or already exists</li>";
    } else {
        echo "<li>❌ Error creating table '{$tableName}': " . $conn->error . "</li>";
        $errors[] = "Error creating table '{$tableName}': " . $conn->error;
        $success = false;
    }
}

echo "</ul>";

// Insert default data
if ($success) {
    echo "<h2>Inserting Default Data</h2>";
    echo "<ul>";
    
    // Check the structure of the users table before inserting
    $checkTableStructure = $conn->query("DESCRIBE users");
    $columns = [];
    if ($checkTableStructure) {
        while ($row = $checkTableStructure->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        echo "<li>ℹ️ Found users table with columns: " . implode(", ", $columns) . "</li>";
    }

    // Default admin user - adjusted to handle different column names
    $adminExists = $conn->query("SELECT id FROM users WHERE email = 'admin@quickhirelabor.com' LIMIT 1");
    
    if ($adminExists && $adminExists->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        // Determine if we should use 'name' or separate first/last name fields
        if (in_array('first_name', $columns) && in_array('last_name', $columns)) {
            $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                    VALUES ('Admin', 'User', 'admin@quickhirelabor.com', '1234567890', '$admin_password', 'admin')";
        } else if (in_array('username', $columns)) {
            $sql = "INSERT INTO users (username, email, phone, password, role) 
                    VALUES ('admin', 'admin@quickhirelabor.com', '1234567890', '$admin_password', 'admin')";
        } else {
            echo "<li>❌ Cannot determine correct column structure for users table</li>";
            $adminInserted = false;
        }
        if (isset($sql)) {
            try {
                if ($conn->query($sql) === TRUE) {
                    echo "<li>✅ Default admin user created</li>";
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                echo "<li>❌ Error creating admin user: " . $e->getMessage() . "</li>";
                echo "<li>Debug: SQL used was: " . $sql . "</li>";
                $errors[] = "Error creating admin user: " . $e->getMessage();
            }
        }
    } else {
        echo "<li>ℹ️ Admin user already exists or couldn't check</li>";
    }
    
    // Default services
    $servicesCount = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
    
    if ($servicesCount == 0) {
        $services = [
            ['Painters', 'Professional painting services for interior and exterior walls', 'images/Painters.png', '$100-$500'],
            ['Electrical', 'Expert electrical repair and installation services', 'images/s2.png', '$80-$300'],
            ['Plumbing', 'Reliable plumbing services for all your needs', 'images/s3.png', '$90-$400'],
            ['Carpentry', 'Custom carpentry work and furniture making', 'images/renew.png', '$150-$700'],
            ['Cleaning', 'Thorough home and office cleaning services', 'images/cleaning.png', '$50-$200']
        ];
        
        $stmt = $conn->prepare("INSERT INTO services (name, description, image, price_range) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $description, $image, $price_range);
        
        foreach ($services as $service) {
            $name = $service[0];
            $description = $service[1];
            $image = $service[2];
            $price_range = $service[3];
            if ($stmt->execute()) {
                echo "<li>✅ Service '{$name}' created</li>";
            } else {
                echo "<li>❌ Error creating service '{$name}': " . $stmt->error . "</li>";
                $errors[] = "Error creating service '{$name}': " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        echo "<li>ℹ️ Services already exist in the database</li>";
    }
    
    echo "</ul>";
}

// Data migration section
echo "<h2>Data Migration</h2>";
echo "<ul>";

$migration_path = __DIR__ . '/database/migrations';
if (is_dir($migration_path)) {
    // Process each CSV file in the migrations directory
    $files = glob($migration_path . '/*.csv');
    
    foreach ($files as $file) {
        $table_name = basename($file, '.csv');
        echo "<li>Found migration file for table '{$table_name}'</li>";
        
        // Check if the table exists
        $table_exists = $conn->query("SHOW TABLES LIKE '{$table_name}'")->num_rows > 0;
        
        if ($table_exists) {
            // Read CSV file
            if (($handle = fopen($file, "r")) !== FALSE) {
                $headers = fgetcsv($handle, 0, ","); // Get column names
                $row_count = 0;
                
                // Prepare SQL for insertion
                $columns = implode("`, `", $headers);
                $placeholders = implode(", ", array_fill(0, count($headers), "?"));
                $sql = "INSERT INTO `{$table_name}` (`{$columns}`) VALUES ({$placeholders})";
                
                try {
                    $stmt = $conn->prepare($sql);
                    
                    // Process each row in the CSV
                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                        if (count($data) != count($headers)) continue; // Skip invalid rows
                        
                        // Bind parameters
                        $types = str_repeat("s", count($data)); // Assume all strings
                        $stmt->bind_param($types, ...$data);
                        
                        if ($stmt->execute()) {
                            $row_count++;
                        }
                    }
                    echo "<li>✅ Imported {$row_count} rows into '{$table_name}'</li>";
                } catch (Exception $e) {
                    echo "<li>❌ Error importing data to '{$table_name}': " . $e->getMessage() . "</li>";
                }
                
                fclose($handle);
            } else {
                echo "<li>❌ Could not open file: {$file}</li>";
            }
        } else {
            echo "<li>⚠️ Table '{$table_name}' does not exist, skipping import</li>";
        }
    }
} else {
    echo "<li>ℹ️ No migration directory found at {$migration_path}</li>";
}

echo "</ul>";

// Sample user data - Create test accounts for development
if ($success && isset($_GET['with_sample_data']) && $_GET['with_sample_data'] == 1) {
    echo "<h2>Creating Sample User Accounts</h2>";
    echo "<ul>";
    
    // Sample customer and laborer accounts
    $sample_users = [
        ['Customer One', 'customer1@example.com', '1234567890', 'password123', 'customer'],
        ['Customer Two', 'customer2@example.com', '2345678901', 'password123', 'customer'],
        ['Laborer One', 'laborer1@example.com', '3456789012', 'password123', 'laborer'],
        ['Laborer Two', 'laborer2@example.com', '4567890123', 'password123', 'laborer']
    ];
    
    foreach ($sample_users as $user) {
        $name = $user[0];
        $email = $user[1];
        $mobile = $user[2];
        $password = password_hash($user[3], PASSWORD_DEFAULT);
        $role = $user[4];
        
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            // Split name into first_name and last_name
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $mobile, $password, $role);
            if ($stmt->execute()) {
                echo "<li>✅ Created sample user: {$name} ({$role})</li>";
                
                // For laborers, create settings entry
                if ($role == 'laborer') {
                    $user_id = $stmt->insert_id;
                    
                    // Create laborer settings
                    $settings = $conn->prepare("INSERT INTO laborer_settings (user_id) VALUES (?)");
                    $settings->bind_param("i", $user_id);
                    if ($settings->execute()) {
                        echo "<li>✅ Created laborer settings for: {$name}</li>";
                    }
                    
                    // Create notification preferences
                    $prefs = $conn->prepare("INSERT INTO notification_preferences (user_id) VALUES (?)");
                    $prefs->bind_param("i", $user_id);
                    if ($prefs->execute()) {
                        echo "<li>✅ Created notification preferences for: {$name}</li>";
                    }
                }
            } else {
                echo "<li>❌ Error creating sample user {$name}: " . $stmt->error . "</li>";
            }
        } else {
            echo "<li>ℹ️ Sample user {$name} already exists</li>";
        }
    }
    echo "</ul>";
}

// Final status message
if (empty($errors)) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>✅ Database setup completed successfully!</h3>";
    echo "<p>All required tables and default data have been created.</p>";
    echo "<p><a href='index.php' style='display: inline-block; background-color: #0355cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to Homepage</a></p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>⚠️ Database setup completed with errors</h3>";
    echo "<p>Please check the error messages above and fix the issues before continuing.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickHire Labor - Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #0355cc;
        }
        ul {
            background-color: #f8f9fa;
            padding: 15px 30px;
            list-style-type: none;
            border-radius: 5px;
        }
        li {
            padding: 5px;
            margin-bottom: 8px;
        }
        a {
            color: #0355cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #0355cc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0243a3;
        }
        .option-box {
            background-color: #e9f5ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .option-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        .option-links a {
            padding: 10px 15px;
            background-color: #0355cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .option-links a:hover {
            background-color: #0243a3;
        }
        .option-links a.secondary {
            background-color: #6c757d;
        }
        .option-links a.secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>QuickHire Labor - Database Setup</h1>
    <?php if (empty($errors)): ?>
    <div class="option-box">
        <h3>Setup Options</h3>
        <p>Your database has been set up successfully. What would you like to do next?</p>
        <div class="option-links">
            <a href="sql_setup.php?with_sample_data=1">Import Sample Data</a>
            <a href="index.php" class="secondary">Go to Homepage</a>
        </div>
        <p><small>Note: The "Import Sample Data" option will create test accounts with username:password123 for testing purposes.</small></p>
    </div>
    <?php endif; ?>
</body>
</html>