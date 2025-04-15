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
    
    // Job Applications Table
    "CREATE TABLE IF NOT EXISTS job_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        laborer_id INT NOT NULL,
        cover_letter TEXT,
        price_quote DECIMAL(10,2),
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
        FOREIGN KEY (laborer_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY `unique_application` (`job_id`, `laborer_id`)
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
    )",
    
    // Ratings Table
    "CREATE TABLE IF NOT EXISTS ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        rater_id INT NOT NULL,
        ratee_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id),
        FOREIGN KEY (rater_id) REFERENCES users(id),
        FOREIGN KEY (ratee_id) REFERENCES users(id)
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

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Function to check if table has data
function tableHasData($conn, $tableName) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $tableName");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    return false;
}

// Function to insert demo data and report results
function insertDemoData($conn, $tableName, $demoData, $columns) {
    $results = [
        'success' => 0,
        'error' => 0,
        'errors' => []
    ];
    
    // Check if table exists and is empty
    if (tableHasData($conn, $tableName)) {
        echo "<li>ℹ️ Table '$tableName' already has data - skipping demo data insertion</li>";
        return $results;
    }
    
    // Prepare columns and placeholders for the query
    $columnNames = implode("`, `", array_keys($columns));
    $placeholders = implode(", ", array_fill(0, count($columns), "?"));
    $types = "";
    
    // Build the type string for bind_param
    foreach ($columns as $type) {
        $types .= $type;
    }
    
    // Prepare the statement
    $stmt = $conn->prepare("INSERT INTO `$tableName` (`$columnNames`) VALUES ($placeholders)");
    
    if (!$stmt) {
        echo "<li>❌ Error preparing statement for '$tableName': " . $conn->error . "</li>";
        $results['errors'][] = "Preparation error: " . $conn->error;
        return $results;
    }
    
    // Insert each row of demo data
    foreach ($demoData as $dataRow) {
        $values = [];
        foreach (array_keys($columns) as $column) {
            $values[] = &$dataRow[$column];
        }
        
        // Bind parameters and execute
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            $results['success']++;
            echo "<li>✅ Added demo data row to '$tableName'</li>";
        } else {
            $results['error']++;
            $results['errors'][] = $stmt->error;
            echo "<li>❌ Error adding demo data to '$tableName': " . $stmt->error . "</li>";
        }
    }
    
    $stmt->close();
    return $results;
}

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
        
        // Verify if the required name columns exist
        if (!in_array('first_name', $columns)) {
            echo "<li>⚠️ Warning: 'first_name' column not found in users table</li>";
        }
        if (!in_array('last_name', $columns)) {
            echo "<li>⚠️ Warning: 'last_name' column not found in users table</li>";
        }
    }

    // Default admin user - adjusted to handle different column names
    $adminExists = $conn->query("SELECT id FROM users WHERE email = 'admin@quickhirelabor.com' LIMIT 1");
    
    if ($adminExists && $adminExists->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        // Always use first_name and last_name for consistency
        $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                VALUES ('Admin', 'User', 'admin@quickhirelabor.com', '1234567890', '$admin_password', 'admin')";
        
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

// Database migration section is removed since it's not needed

// Add demo data for Payments
if (tableExists($conn, 'payments') && !tableHasData($conn, 'payments')) {
    echo "<h2>Adding Payment Demo Data</h2>";
    echo "<ul>";
    
    // Get job IDs
    $jobResult = $conn->query("SELECT id FROM jobs ORDER BY id ASC LIMIT 5");
    $jobs = [];
    if ($jobResult) {
        while ($row = $jobResult->fetch_assoc()) {
            $jobs[] = $row['id'];
        }
        echo "<li>ℹ️ Retrieved job IDs for payments: " . implode(", ", $jobs) . "</li>";
    }
    
    if (!empty($jobs)) {
        $paymentColumns = [
            'job_id' => 'i',
            'amount' => 'd',
            'transaction_id' => 's',
            'status' => 's'
        ];
        
        // Make sure we don't try to access jobs that don't exist
        $jobCount = count($jobs);
        
        $paymentDemoData = [
            [
                'job_id' => $jobs[0],
                'amount' => 300.00,
                'transaction_id' => 'TXN'.mt_rand(1000000, 9999999),
                'status' => 'completed'
            ]
        ];
        
        // Only add more payment records if we have enough jobs
        if ($jobCount > 1) {
            $paymentDemoData[] = [
                'job_id' => $jobs[1],
                'amount' => 75.00,
                'transaction_id' => 'TXN'.mt_rand(1000000, 9999999),
                'status' => 'pending'
            ];
        }
        
        if ($jobCount > 2) {
            $paymentDemoData[] = [
                'job_id' => $jobs[2],
                'amount' => 120.00,
                'transaction_id' => 'TXN'.mt_rand(1000000, 9999999),
                'status' => 'completed'
            ];
        }
        
        if ($jobCount > 3) {
            $paymentDemoData[] = [
                'job_id' => $jobs[3],
                'amount' => 250.00,
                'transaction_id' => 'TXN'.mt_rand(1000000, 9999999),
                'status' => 'refunded'
            ];
        }
        
        if ($jobCount > 4) {
            $paymentDemoData[] = [
                'job_id' => $jobs[4],
                'amount' => 85.00,
                'transaction_id' => 'TXN'.mt_rand(1000000, 9999999),
                'status' => 'pending'
            ];
        }
        
        // Fix for the issue in insertDemoData function
        // Check each row to ensure all required fields exist
        foreach ($paymentDemoData as $index => $data) {
            foreach (array_keys($paymentColumns) as $column) {
                if (!isset($data[$column])) {
                    echo "<li>⚠️ Warning: Missing '{$column}' in payment data record {$index}, fixing...</li>";
                    // Set a default value based on column type
                    if ($column == 'job_id' && !empty($jobs)) {
                        $paymentDemoData[$index][$column] = $jobs[0]; // Use first job ID if missing
                    } elseif ($column == 'amount') {
                        $paymentDemoData[$index][$column] = 100.00; // Default amount
                    } elseif ($column == 'transaction_id') {
                        $paymentDemoData[$index][$column] = 'TXN'.mt_rand(1000000, 9999999); // Generate random ID
                    } elseif ($column == 'status') {
                        $paymentDemoData[$index][$column] = 'pending'; // Default status
                    }
                }
            }
        }
        
        $paymentResult = insertDemoData($conn, 'payments', $paymentDemoData, $paymentColumns);
        if ($paymentResult['success'] > 0) {
            echo "<li>✅ Successfully added {$paymentResult['success']} payments as demo data</li>";
        } else {
            echo "<li>❌ Failed to add payment demo data: " . implode("; ", $paymentResult['errors']) . "</li>";
        }
    } else {
        echo "<li>❌ Cannot add payment demo data: no jobs found in database</li>";
        echo "<li>ℹ️ Try adding job data first before payments</li>";
    }
    
    echo "</ul>";
}

// Also fix the ratings data - similar protections
if (tableExists($conn, 'ratings') && !tableHasData($conn, 'ratings')) {
    echo "<h2>Adding Ratings Demo Data</h2>";
    echo "<ul>";
    
    // Get job IDs
    $jobResult = $conn->query("SELECT id FROM jobs ORDER BY id ASC LIMIT 3");
    $jobs = [];
    if ($jobResult) {
        while ($row = $jobResult->fetch_assoc()) {
            $jobs[] = $row['id'];
        }
        echo "<li>ℹ️ Retrieved job IDs for ratings: " . implode(", ", $jobs) . "</li>";
    }
    
    // Get user IDs (for rater and ratee)
    $userResult = $conn->query("SELECT id FROM users ORDER BY id ASC LIMIT 4");
    $users = [];
    if ($userResult) {
        while ($row = $userResult->fetch_assoc()) {
            $users[] = $row['id'];
        }
        echo "<li>ℹ️ Retrieved user IDs for ratings: " . implode(", ", $users) . "</li>";
    }
    
    if (!empty($jobs) && count($users) >= 2) {
        $ratingColumns = [
            'job_id' => 'i',
            'rater_id' => 'i',
            'ratee_id' => 'i',
            'rating' => 'i',
            'comment' => 's'
        ];
        
        $jobCount = count($jobs);
        $userCount = count($users);
        
        $ratingDemoData = [];
        
        // First rating - always add if we have at least 1 job and 2 users
        if ($jobCount > 0 && $userCount >= 2) {
            $ratingDemoData[] = [
                'job_id' => $jobs[0],
                'rater_id' => $users[0],
                'ratee_id' => $users[1],
                'rating' => 5,
                'comment' => 'Excellent work! Very professional and completed the job ahead of schedule.'
            ];
            
            // Second rating - for the same job, reverse rater/ratee
            $ratingDemoData[] = [
                'job_id' => $jobs[0],
                'rater_id' => $users[1],
                'ratee_id' => $users[0],
                'rating' => 5,
                'comment' => 'Great customer! Clear instructions and paid promptly.'
            ];
        }
        
        // Only add more if we have enough jobs and users
        if ($jobCount > 1 && $userCount >= 3) {
            $ratingDemoData[] = [
                'job_id' => $jobs[1],
                'rater_id' => $users[0],
                'ratee_id' => $users[2],
                'rating' => 4,
                'comment' => 'Good work overall. Could have cleaned up a bit better afterwards.'
            ];
            
            $ratingDemoData[] = [
                'job_id' => $jobs[1],
                'rater_id' => $users[2],
                'ratee_id' => $users[0],
                'rating' => 5,
                'comment' => 'Very clear instructions and fair compensation.'
            ];
        }
        
        if ($jobCount > 2 && $userCount >= 4) {
            $ratingDemoData[] = [
                'job_id' => $jobs[2],
                'rater_id' => $users[3],
                'ratee_id' => $users[0],
                'rating' => 3,
                'comment' => 'Job requirements changed midway through the project.'
            ];
        }
        
        // Verify all data is present
        foreach ($ratingDemoData as $index => $data) {
            foreach (array_keys($ratingColumns) as $column) {
                if (!isset($data[$column])) {
                    echo "<li>⚠️ Warning: Missing '{$column}' in rating data record {$index}, fixing...</li>";
                    // Set a default value based on column type
                    if ($column == 'job_id' && !empty($jobs)) {
                        $ratingDemoData[$index][$column] = $jobs[0];
                    } elseif (($column == 'rater_id' || $column == 'ratee_id') && !empty($users)) {
                        $ratingDemoData[$index][$column] = $users[0];
                    } elseif ($column == 'rating') {
                        $ratingDemoData[$index][$column] = 5;
                    } elseif ($column == 'comment') {
                        $ratingDemoData[$index][$column] = 'Good service';
                    }
                }
            }
        }
        
        $ratingResult = insertDemoData($conn, 'ratings', $ratingDemoData, $ratingColumns);
        if ($ratingResult['success'] > 0) {
            echo "<li>✅ Successfully added {$ratingResult['success']} ratings as demo data</li>";
        } else {
            echo "<li>❌ Failed to add rating demo data: " . implode("; ", $ratingResult['errors']) . "</li>";
        }
    } else {
        echo "<li>❌ Cannot add rating demo data: missing job or user data</li>";
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
    <meta name="viewport" width="device-width, initial-scale=1.0">
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