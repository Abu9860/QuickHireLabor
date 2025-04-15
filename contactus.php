<?php
require_once 'config.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }
    
    // Here you would typically save the contact request to database
    // or send an email notification
    
    // For now, just return success
    echo json_encode(['status' => 'success', 'message' => 'Message received']);
    exit;
}

// For GET requests, show the form
?>
<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />

  <title>Contact Us - QuickHire Labor</title>

  <!-- slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
  <!-- font awesome style -->
  <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />

</head>

<body>
  <div class="hero_area">
    <?php include 'includes/header.php'; ?>
  </div>

  <section class="contact-section">
    <div class="container">
      <div class="contact-grid">
        <div class="contact-info">
          <h2>Get in Touch</h2>
          <div class="info-item">
            <i class="fa fa-phone"></i>
            <p>+01 123455678990</p>
          </div>
          <div class="info-item">
            <i class="fa fa-envelope"></i>
            <p>support@quickhire.com</p>
          </div>
          <div class="info-item">
            <i class="fa fa-map-marker"></i>
            <p>123 Labor Street, Work City, IN 12345</p>
          </div>
        </div>

        <form class="contact-form" method="POST" action="">
          <h2>Send us a Message</h2>
          <input type="text" name="name" placeholder="Your Name" required>
          <input type="email" name="email" placeholder="Your Email" required>
          <input type="tel" name="phone" placeholder="Your Phone">
          <textarea name="message" placeholder="Your Message" required></textarea>
          <button type="submit">Send Message</button>
        </form>
      </div>

      <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="400" frameborder="0" allowfullscreen></iframe>
      </div>
    </div>
  </section>

  <style>
    .contact-section {
      padding: 80px 0;
      background: #f8f9fa;
    }

    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
      margin-bottom: 50px;
    }

    .contact-info,
    .contact-form {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .contact-form input,
    .contact-form textarea {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
    }

    .contact-form button {
      background: #4CAF50;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .info-item {
      display: flex;
      align-items: center;
      margin: 20px 0;
    }

    .info-item i {
      font-size: 24px;
      margin-right: 15px;
      color: #4CAF50;
    }
  </style>

  <!-- Add Font Awesome for Icons -->
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>

</body>

</html>
