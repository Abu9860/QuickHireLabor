<?php
require_once 'config.php';

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS lastop";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("lastop");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'laborer', 'admin') NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create services table
$sql = "CREATE TABLE IF NOT EXISTS services (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Services table created successfully<br>";
} else {
    echo "Error creating services table: " . $conn->error . "<br>";
}

// Create jobs table with proper date handling
$sql = "CREATE TABLE IF NOT EXISTS jobs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    customer_id INT(11) NOT NULL,
    laborer_id INT(11),
    status ENUM('pending', 'assigned', 'completed', 'cancelled') DEFAULT 'pending',
    price DECIMAL(10,2),
    scheduled_date DATE DEFAULT NULL,
    required_skills VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (laborer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Jobs table created successfully<br>";
} else {
    echo "Error creating jobs table: " . $conn->error . "<br>";
}

// Add updated_at column to jobs table if it doesn't exist
$sql = "SHOW COLUMNS FROM jobs LIKE 'updated_at'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE jobs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
    if ($conn->query($sql) === TRUE) {
        echo "Added updated_at column to jobs table<br>";
        // Initialize updated_at with created_at for existing records
        $sql = "UPDATE jobs SET updated_at = created_at WHERE updated_at IS NULL";
        $conn->query($sql);
        echo "Initialized updated_at values<br>";
    }
}

// Check if location column exists in jobs table
$sql = "SHOW COLUMNS FROM jobs LIKE 'location'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    // Add location column if it doesn't exist
    $sql = "ALTER TABLE jobs ADD COLUMN location VARCHAR(255) DEFAULT NULL AFTER description";
    if ($conn->query($sql) === TRUE) {
        echo "Added location column to jobs table<br>";
        
        // Update existing jobs with sample locations
        $sql = "UPDATE jobs SET location = CASE 
                WHEN id = 1 THEN 'Sangli, Maharashtra'
                WHEN id = 2 THEN 'Miraj, Maharashtra'
                WHEN id = 3 THEN 'Kolhapur, Maharashtra'
                WHEN id = 4 THEN 'Satara, Maharashtra'
                WHEN id = 5 THEN 'Pune, Maharashtra'
                END";
        $conn->query($sql);
        echo "Updated jobs with sample locations<br>";
    } else {
        echo "Error adding location column: " . $conn->error . "<br>";
    }
}

// Check if scheduled_date column exists and update with proper default value
$sql = "SHOW COLUMNS FROM jobs LIKE 'scheduled_date'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    // Add scheduled_date column if it doesn't exist with proper default
    $sql = "ALTER TABLE jobs ADD COLUMN scheduled_date DATE DEFAULT NULL AFTER price";
    if ($conn->query($sql) === TRUE) {
        echo "Added scheduled_date column to jobs table<br>";
        
        // Set default dates for existing records using current date + random days
        $sql = "UPDATE jobs SET scheduled_date = CURDATE() + INTERVAL (FLOOR(1 + RAND() * 30)) DAY WHERE scheduled_date IS NULL";
        $conn->query($sql);
        echo "Updated jobs with sample dates<br>";
    } else {
        echo "Error adding scheduled_date column: " . $conn->error . "<br>";
    }
}

// Check if required_skills column exists in jobs table
$sql = "SHOW COLUMNS FROM jobs LIKE 'required_skills'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    // Add required_skills column if it doesn't exist
    $sql = "ALTER TABLE jobs ADD COLUMN required_skills VARCHAR(255) DEFAULT NULL AFTER scheduled_date";
    if ($conn->query($sql) === TRUE) {
        echo "Added required_skills column to jobs table<br>";
        
        // Update existing jobs with sample skills
        $sql = "UPDATE jobs SET required_skills = CASE 
                WHEN title LIKE '%Plumbing%' THEN 'Plumbing'
                WHEN title LIKE '%Paint%' THEN 'Painting'
                WHEN title LIKE '%Electrical%' THEN 'Electrical'
                WHEN title LIKE '%Bathroom%' THEN 'Plumbing,Renovation'
                WHEN title LIKE '%Lawn%' THEN 'Gardening'
                END";
        $conn->query($sql);
        echo "Updated jobs with sample required skills<br>";
    } else {
        echo "Error adding required_skills column: " . $conn->error . "<br>";
    }
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Payments table created successfully<br>";
} else {
    echo "Error creating payments table: " . $conn->error . "<br>";
}

// Create ratings table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    customer_id INT(11) NOT NULL,
    laborer_id INT(11) NOT NULL,
    rating INT(1) NOT NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (laborer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Ratings table created successfully<br>";
} else {
    echo "Error creating ratings table: " . $conn->error . "<br>";
}

// Drop and recreate notifications table
$sql = "DROP TABLE IF EXISTS notifications";
$conn->query($sql);

// Update notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('job_status', 'payment', 'message', 'general') DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Notifications table created successfully<br>";
} else {
    echo "Error creating notifications table: " . $conn->error . "<br>";
}

// Create skills table
$sql = "CREATE TABLE IF NOT EXISTS skills (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Skills table created successfully<br>";
    
    // Insert default skills if table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM skills");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $sql = "INSERT INTO skills (name) VALUES 
            ('Plumbing'),
            ('Electrical'),
            ('Carpentry'),
            ('Cleaning'),
            ('Painting'),
            ('Gardening'),
            ('Renovation'),
            ('Moving'),
            ('Handyman')";
        if ($conn->query($sql)) {
            echo "Sample skills added successfully<br>";
        }
    }
} else {
    echo "Error creating skills table: " . $conn->error . "<br>";
}

// Create laborer_skills table for many-to-many relationship
$sql = "CREATE TABLE IF NOT EXISTS laborer_skills (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    laborer_id INT(11) NOT NULL,
    skill_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laborer_id) REFERENCES users(id),
    FOREIGN KEY (skill_id) REFERENCES skills(id),
    UNIQUE KEY unique_laborer_skill (laborer_id, skill_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Laborer skills table created successfully<br>";
} else {
    echo "Error creating laborer skills table: " . $conn->error . "<br>";
}

// Drop and recreate support_tickets table
$sql = "DROP TABLE IF EXISTS support_tickets";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS support_tickets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'low',
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    admin_response TEXT DEFAULT NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Support tickets table created successfully<br>";
    
    // Insert sample support tickets
    $result = $conn->query("SELECT COUNT(*) as count FROM support_tickets");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $sql = "INSERT INTO support_tickets (user_id, subject, message, priority, status, admin_response, responded_at) VALUES
            (2, 'Payment Issue', 'I have not received payment for completed job', 'high', 'open', NULL, NULL),
            (3, 'Account Access', 'Unable to update my profile information', 'medium', 'in_progress', 'We are working on fixing the profile update issue. Please try again in 2 hours.', NOW()),
            (4, 'Job Cancellation', 'Need help canceling a scheduled job', 'low', 'closed', 'Job has been successfully cancelled. Refund will be processed within 24 hours.', NOW()),
            (2, 'App Bug Report', 'Job search filter not working properly', 'medium', 'open', NULL, NULL),
            (3, 'Service Inquiry', 'How do I add new services to my profile?', 'low', 'closed', 'You can add new services from your profile settings under the Skills section.', NOW())";
        
        if ($conn->query($sql)) {
            echo "Sample support tickets added successfully<br>";
        } else {
            echo "Error adding sample support tickets: " . $conn->error . "<br>";
        }
    }
} else {
    echo "Error creating support tickets table: " . $conn->error . "<br>";
}

// Insert sample users
$password = password_hash('password123', PASSWORD_DEFAULT);

// Check if admin already exists
$result = $conn->query("SELECT * FROM users WHERE email = 'admin@lastop.com'");
if ($result->num_rows == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES
        ('Admin', 'admin@lastop.com', '$admin_password', '9876543210', 'admin')";
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created<br>";
    } else {
        echo "Error creating admin: " . $conn->error . "<br>";
    }
}

// Insert default customer account if not exists
$result = $conn->query("SELECT * FROM users WHERE email = 'customer@gmail.com'");
if ($result->num_rows == 0) {
    $customer_password = password_hash('customer123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, phone, role, address) VALUES
        ('John Customer', 'customer@gmail.com', '$customer_password', '9876543210', 'customer', 'Sangli, Maharashtra')";
    if ($conn->query($sql) === TRUE) {
        echo "Default customer account created<br>";
        echo "Default customer login credentials:<br>";
        echo "Email: customer@gmail.com<br>";
        echo "Password: customer123<br>";
    } else {
        echo "Error creating default customer: " . $conn->error . "<br>";
    }
}

// Check if sample customers already exist
$result = $conn->query("SELECT * FROM users WHERE email = 'john@example.com'");
if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES
        ('John Doe', 'john@example.com', '$password', '1234567890', 'customer'),
        ('Jane Smith', 'jane@example.com', '$password', '2345678901', 'customer')";
    if ($conn->query($sql) === TRUE) {
        echo "Sample customers created<br>";
    } else {
        echo "Error creating customers: " . $conn->error . "<br>";
    }
}

// Check if sample laborers already exist
$result = $conn->query("SELECT * FROM users WHERE email = 'michael@example.com'");
if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES
        ('Michael Smith', 'michael@example.com', '$password', '3456789012', 'laborer'),
        ('David Johnson', 'david@example.com', '$password', '4567890123', 'laborer')";
    if ($conn->query($sql) === TRUE) {
        echo "Sample laborers created<br>";
    } else {
        echo "Error creating laborers: " . $conn->error . "<br>";
    }
}

// Check if sample laborers already exist
$result = $conn->query("SELECT * FROM users WHERE email = 'michael@example.com'");
if ($result->num_rows == 0) {
    $laborer_password = password_hash('labor123', PASSWORD_DEFAULT); // Set default password for laborers
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES
        ('Michael Smith', 'labor@gmail.com', '$laborer_password', '3456789012', 'laborer'),
        ('David Johnson', 'david@example.com', '$laborer_password', '4567890123', 'laborer')";
    if ($conn->query($sql) === TRUE) {
        echo "Sample laborers created<br>";
        echo "Default laborer login - Email: labor@gmail.com, Password: labor123<br>";
    } else {
        echo "Error creating laborers: " . $conn->error . "<br>";
    }
}

// Insert default laborer if not exists
$result = $conn->query("SELECT * FROM users WHERE email = 'labor@gmail.com'");
if ($result->num_rows == 0) {
    $laborer_password = password_hash('labor123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, phone, role, address) VALUES
        ('John Labor', 'labor@gmail.com', '$laborer_password', '9876543210', 'laborer', 'Sangli, Maharashtra')";
    if ($conn->query($sql) === TRUE) {
        echo "Default laborer account created<br>";
        echo "Default laborer login credentials:<br>";
        echo "Email: labor@gmail.com<br>";
        echo "Password: labor123<br>";
    } else {
        echo "Error creating default laborer: " . $conn->error . "<br>";
    }
}

// First alter the users table to add profile_pic if it doesn't exist
$sql = "SHOW COLUMNS FROM users LIKE 'profile_pic'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL";
    $conn->query($sql);
    echo "Added profile_pic column to users table<br>";
}

// Now it's safe to update profile_pic
$sql = "UPDATE users SET profile_pic = 'images/client-1.jpg' WHERE profile_pic IS NULL";
$conn->query($sql);
echo "Updated default profile pictures<br>";

// Check if address column exists in users table
$sql = "SHOW COLUMNS FROM users LIKE 'address'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    // Add address column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN address VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Added address column to users table<br>";
        
        // Update existing users with sample addresses
        $sql = "UPDATE users SET address = CASE 
                WHEN email = 'michael@example.com' THEN 'Sangli, Maharashtra'
                WHEN email = 'david@example.com' THEN 'Miraj, Maharashtra'
                WHEN email = 'john@example.com' THEN 'Kolhapur, Maharashtra'
                WHEN email = 'jane@example.com' THEN 'Satara, Maharashtra'
                WHEN email = 'admin@lastop.com' THEN 'Pune, Maharashtra'
                END";
        $conn->query($sql);
        echo "Updated users with sample addresses<br>";
    } else {
        echo "Error adding address column: " . $conn->error . "<br>";
    }
}

// First check if skills column exists
$sql = "SHOW COLUMNS FROM users LIKE 'skills'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    // Add skills column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN skills VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Added skills column to users table<br>";
        
        // Update existing laborers with sample skills
        $sql = "UPDATE users SET skills = CASE 
                WHEN email = 'michael@example.com' THEN 'Plumbing,Electrical,Carpentry'
                WHEN email = 'david@example.com' THEN 'Painting,Cleaning,Gardening'
                END 
                WHERE role = 'laborer'";
        $conn->query($sql);
        echo "Updated laborers with sample skills<br>";
    } else {
        echo "Error adding skills column: " . $conn->error . "<br>";
    }
}

// Insert sample jobs first
$result = $conn->query("SELECT COUNT(*) as count FROM jobs");
$row = $result->fetch_assoc();
if ($row['count'] < 10) {
    $current_date = date('Y-m-d');
    $past_date = date('Y-m-d', strtotime('-5 days'));
    $future_date = date('Y-m-d', strtotime('+7 days'));
    
    // First batch of jobs
    $sql = "INSERT INTO jobs (title, description, customer_id, laborer_id, status, price, scheduled_date, location) VALUES
        ('Plumbing Repair', 'Fix leaking kitchen sink', 2, 3, 'completed', 150.00, '$current_date', 'Sangli, Maharashtra'),
        ('Painting Room', 'Paint living room walls', 2, 4, 'assigned', 300.00, '$future_date', 'Miraj, Maharashtra'),
        ('Electrical Wiring', 'Install new light fixtures', 3, NULL, 'pending', 200.00, '$future_date', 'Kolhapur, Maharashtra'),
        ('Bathroom Renovation', 'Retile bathroom floor', 3, 3, 'assigned', 500.00, '$future_date', 'Satara, Maharashtra'),
        ('Lawn Maintenance', 'Mow lawn and trim hedges', 2, 4, 'pending', 80.00, '$future_date', 'Pune, Maharashtra'),
        ('Kitchen Renovation', 'Complete kitchen renovation work', 2, 3, 'assigned', 2500.00, '$current_date', 'Sangli'),
        ('Garden Landscaping', 'Full garden redesign and planting', 2, 4, 'completed', 1200.00, '$past_date', 'Miraj'),
        ('Home Cleaning', 'Deep cleaning of 3BHK apartment', 2, 3, 'pending', 800.00, '$future_date', 'Kolhapur'),
        ('Bathroom Repair', 'Fix leaking pipes and retiling', 2, 4, 'completed', 1500.00, '$past_date', 'Pune'),
        ('AC Installation', 'Install 2 new AC units', 2, 3, 'assigned', 3000.00, '$future_date', 'Sangli')";
    
    $conn->query($sql);
    echo "Sample jobs created<br>";
}

// Now insert payments after jobs exist
$result = $conn->query("SELECT COUNT(*) as count FROM payments");
$row = $result->fetch_assoc();
if ($row['count'] < 10) {
    $current_date = date('Y-m-d H:i:s');
    $past_date = date('Y-m-d H:i:s', strtotime('-5 days'));
    $older_date = date('Y-m-d H:i:s', strtotime('-10 days'));
    
    // Make sure to reference valid job IDs
    $sql = "INSERT INTO payments (job_id, amount, status, created_at)
            SELECT id, price, 
                CASE 
                    WHEN status = 'completed' THEN 'completed'
                    ELSE 'pending'
                END,
                CASE 
                    WHEN status = 'completed' THEN '$past_date'
                    ELSE '$current_date'
                END
            FROM jobs
            WHERE status IN ('completed', 'assigned')
            AND NOT EXISTS (
                SELECT 1 FROM payments p WHERE p.job_id = jobs.id
            )";
    
    if ($conn->query($sql)) {
        echo "Sample payments created successfully<br>";
    } else {
        echo "Error creating payments: " . $conn->error . "<br>";
    }
}

// Insert sample ratings
$result = $conn->query("SELECT COUNT(*) as count FROM ratings");
$row = $result->fetch_assoc();
if ($row['count'] < 2) {
    $sql = "INSERT INTO ratings (job_id, customer_id, laborer_id, rating, feedback) VALUES
        (1, 2, 3, 5, 'Excellent work fixing the sink!'),
        (2, 2, 4, 4, 'Good painting job but took longer than expected')";
    if ($conn->query($sql) === TRUE) {
        echo "Sample ratings created<br>";
    } else {
        echo "Error creating ratings: " . $conn->error . "<br>";
    }
}

// Insert sample services if not exists
$result = $conn->query("SELECT COUNT(*) as count FROM services");
$row = $result->fetch_assoc();
if ($row['count'] < 1) {
    $sql = "INSERT INTO services (name, description, image) VALUES
        ('Plumbing', 'Professional plumbing services for your home and office', 'images/s3.png'),
        ('Electrical', 'Expert electrical installation and repair services', 'images/s2.png'),
        ('Painting', 'Quality painting services for interior and exterior', 'images/Painters.png'),
        ('Renovation', 'Complete home renovation and remodeling services', 'images/renew.png'),
        ('Cleaning', 'Professional cleaning services for all spaces', 'images/cleaning.png'),
        ('Gardening', 'Expert landscaping and garden maintenance', 'images/gardening.png')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Sample services created<br>";
    } else {
        echo "Error creating services: " . $conn->error . "<br>";
    }
}

// Insert sample notifications if not exists
$result = $conn->query("SELECT COUNT(*) as count FROM notifications");
$row = $result->fetch_assoc();
if ($row['count'] < 1) {
    $current_time = date('Y-m-d H:i:s');
    $past_time = date('Y-m-d H:i:s', strtotime('-2 days'));
    $older_time = date('Y-m-d H:i:s', strtotime('-5 days'));
    
    $sql = "INSERT INTO notifications (user_id, title, message, type, created_at, is_read) VALUES 
        -- Customer notifications (user_id = 2 is the default customer)
        (2, 'Laborer Assigned', 'Michael Smith has been assigned to your plumbing job', 'job_status', '$current_time', 0),
        (2, 'Job Started', 'Work has begun on your painting project', 'job_status', '$current_time', 0),
        (2, 'Payment Required', 'Payment of ₹1500 is due for completed bathroom repair', 'payment', '$current_time', 0),
        (2, 'New Message', 'Laborer: I will arrive at 10 AM tomorrow', 'message', '$past_time', 1),
        (2, 'Job Completed', 'Your electrical repair job has been marked as completed', 'job_status', '$past_time', 1),
        (2, 'Rating Reminder', 'Please rate your experience with the recent paint job', 'general', '$past_time', 0),
        (2, 'Price Update', 'Updated quote received for garden maintenance', 'general', '$older_time', 1),
        (2, 'Special Offer', 'Get 10% off on your next booking', 'general', '$older_time', 1),
        
        -- Keep existing notifications for other users
        (3, 'New Job Posted', 'New plumbing job available in your area', 'job_status', '$current_time', 0),
        (3, 'Payment Received', 'You received payment for completed painting job', 'payment', '$past_time', 1),
        (3, 'Job Cancelled', 'A job has been cancelled by the customer', 'job_status', '$current_time', 0),
        (4, 'Profile Review', 'Your profile has been verified successfully', 'general', '$past_time', 1)";
    
    if ($conn->query($sql)) {
        echo "Sample notifications created successfully<br>";
    } else {
        echo "Error creating sample notifications: " . $conn->error . "<br>";
    }
}

// Create payment_history table
$sql = "CREATE TABLE IF NOT EXISTS payment_history (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    job_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('online', 'cash', 'qr') DEFAULT 'online',
    transaction_id VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Payment history table created successfully<br>";
} else {
    echo "Error creating payment history table: " . $conn->error . "<br>";
}

echo "<br>Database setup completed successfully. <a href='index.php'>Go to Homepage</a>";
?>
