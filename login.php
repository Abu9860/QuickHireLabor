<?php
require_once 'config.php';

$errors = [];

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect based on role
    if (isAdmin()) {
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, check login credentials
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last name
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: dashboard.php");
                } elseif ($user['role'] == 'customer') {
                    header("Location: customer_dashboard.php");
                } elseif ($user['role'] == 'laborer') {
                    header("Location: laborer_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quick-Hire Labor</title> 
    <link rel="stylesheet" href="css/stylee.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/responsive.css" rel="stylesheet" />
  </head>
<body>
<div class="hero_area">
    <?php include 'includes/header.php'; ?>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="aboutus.php">About us <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="services.php">Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">Contact Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="newsletter.php">NewsLetter</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="faq.php">FAQs</a>
        </li>
      </ul>
    </div>
  </div>
   
  </header>
  <div class="container">
    <img src="images/slider-img.png" alt="" id="bg-img">
    
  <div class="wrapper">
    <h2>Login</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
      <div class="input-box">
        <input type="text" name="email" placeholder="Enter your email" required>
      </div>
      
      <div class="input-box">
        <input type="password" name="password" placeholder="Enter password" required>
      </div>
      
      <div class="policy">
        <input type="checkbox">
        <h3>Remember me</h3>
      </div>

      <div class="input-box button">
        <input type="submit" value="Login">
      </div>
      
      <div class="text">
        <center>
        <h3>Don't have an account? <a href="signup.php">Register now</a></h3>
        </center>
      </div>
    </form>
  </div>
  
</div>
</body>
</html>