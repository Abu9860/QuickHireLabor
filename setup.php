<?php
require_once 'config.php';

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'laborer', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}

// Create services table
$sql = "CREATE TABLE IF NOT EXISTS services (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating services table: " . $conn->error);
}

// Create jobs table
$sql = "CREATE TABLE IF NOT EXISTS jobs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    customer_id INT(11) NOT NULL,
    laborer_id INT(11),
    service_id INT(11) NOT NULL,
    status ENUM('pending', 'assigned', 'completed', 'cancelled') DEFAULT 'pending',
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (laborer_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating jobs table: " . $conn->error);
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating payments table: " . $conn->error);
}

// Create ratings table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    customer_id INT(11) NOT NULL,
    laborer_id INT(11) NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (laborer_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating ratings table: " . $conn->error);
}

// Create contacts table
$sql = "CREATE TABLE IF NOT EXISTS contacts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating contacts table: " . $conn->error);
}

// Create newsletter table
$sql = "CREATE TABLE IF NOT EXISTS newsletter (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating newsletter table: " . $conn->error);
}

// Insert default admin if not exists
$check_admin = $conn->query("SELECT id FROM users WHERE email = 'admin@quickhire.com'");
if ($check_admin->num_rows == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES 
            ('Admin User', 'admin@quickhire.com', '$admin_password', '1234567890', 'admin')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting admin user: " . $conn->error);
    }
}

// Insert default services if not exists
$check_services = $conn->query("SELECT id FROM services");
if ($check_services->num_rows == 0) {
    $sql = "INSERT INTO services (name, image, description) VALUES 
            ('Painters', 'images/Painters.png', 'Transform your home with a fresh coat of paint! Our professional painters provide smooth, flawless finishes and expert color consultation for every room.'),
            ('Electrical', 'images/s2.png', 'Ensure your home\'s electrical system is safe and efficient with our certified electricians. From lighting installations to wiring upgrades, we handle all your electrical needs.'),
            ('Plumbing', 'images/s3.png', 'Fix leaks, clogged drains, and water heater issues with our expert plumbing services. Our licensed plumbers offer fast, reliable solutions to keep your home running smoothly.'),
            ('Kitchen Renovation', 'images/renew.png', 'Transform your kitchen into a modern, functional space with top-of-the-line appliances, cabinetry, and flooring options.'),
            ('Bathroom Renovation', 'images/renew.png', 'Upgrade your bathroom with luxurious fixtures, new tile work, and space-efficient designs to improve comfort and style.'),
            ('Basement Finishing', 'images/renew.png', 'Turn your basement into a functional living area with modern finishes, ample storage, and personalized design elements.')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting default services: " . $conn->error);
    }
}

// Insert sample users if not exists
$check_users = $conn->query("SELECT id FROM users WHERE email = 'john@example.com'");
if ($check_users->num_rows == 0) {
    $user_password = password_hash('password123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES 
            ('John Doe', 'john@example.com', '$user_password', '9876543210', 'customer'),
            ('Jane Smith', 'jane@example.com', '$user_password', '8765432109', 'customer'),
            ('Michael Smith', 'michael@example.com', '$user_password', '7654321098', 'laborer'),
            ('David Johnson', 'david@example.com', '$user_password', '6543210987', 'laborer')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting sample users: " . $conn->error);
    }
}

// Insert sample jobs if not exists
$check_jobs = $conn->query("SELECT id FROM jobs");
if ($check_jobs->num_rows == 0) {
    $sql = "INSERT INTO jobs (title, description, customer_id, laborer_id, service_id, status, price) VALUES 
            ('Plumbing Repair', 'Fix leaking kitchen sink and bathroom faucet', 2, 3, 3, 'completed', 150.00),
            ('Painting Living Room', 'Paint living room walls with premium paint', 2, 4, 1, 'assigned', 300.00),
            ('Electrical Wiring', 'Install new light fixtures in dining room', 3, NULL, 2, 'pending', 200.00),
            ('Bathroom Renovation', 'Retile bathroom floor and install new shower', 3, 3, 5, 'assigned', 500.00),
            ('Kitchen Painting', 'Paint kitchen walls and ceiling', 2, 4, 1, 'pending', 250.00)";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting sample jobs: " . $conn->error);
    }
}

// Insert sample payments if not exists
$check_payments = $conn->query("SELECT id FROM payments");
if ($check_payments->num_rows == 0) {
    $sql = "INSERT INTO payments (job_id, amount, transaction_id, status) VALUES 
            (1, 150.00, 'TXN12345', 'completed'),
            (2, 300.00, 'TXN23456', 'pending'),
            (4, 500.00, 'TXN34567', 'pending')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting sample payments: " . $conn->error);
    }
}

// Insert sample ratings if not exists
$check_ratings = $conn->query("SELECT id FROM ratings");
if ($check_ratings->num_rows == 0) {
    $sql = "INSERT INTO ratings (job_id, customer_id, laborer_id, rating, feedback) VALUES 
            (1, 2, 3, 4.5, 'Great job fixing the sink! Very professional and quick.'),
            (2, 2, 4, 4.0, 'Good painting job but took longer than expected.')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error inserting sample ratings: " . $conn->error);
    }
}

echo "Database setup completed successfully! <a href='index.php'>Go to homepage</a>";
?>
