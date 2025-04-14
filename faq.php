<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/stylee.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/responsive.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 30px 0;
        }

        header h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .faq-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .faq-item {
            border-bottom: 1px solid #ececec;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .faq-question:hover {
            background-color: #bdc3c7;
        }

        .faq-answer {
            display: none;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #3498db;
            border-radius: 5px;
            margin-top: 10px;
        }

        .icon {
            font-size: 20px;
            font-weight: bold;
        }

        .faq-question.open .icon {
            transform: rotate(45deg);
        }

        .faq-answer p {
            font-size: 16px;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
            }

            .faq-question h2 {
                font-size: 16px;
            }

            .faq-answer p {
                font-size: 14px;
            }
        }
    </style>
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

    <div class="container">
        <header>
            <h1>Frequently Asked Questions</h1>
        </header>
        
        <div class="faq-container">
            <!-- FAQ 1 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(1)">
                    <h2>How do I create an account?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-1">
                    <p>To create an account, follow these steps:</p>
                    <ol>
                        <li>Click on the "Sign Up" button on the homepage.</li>
                        <li>Fill in your personal details (email, phone, name, etc.).</li>
                        <li>Choose whether you're a customer or a laborer and provide necessary information.</li>
                        <li>Set a strong password and confirm your email address.</li>
                        <li>Once registered, you will be able to log in and start using the platform.</li>
                    </ol>
                </div>
            </div>
            
            <!-- FAQ 2 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(2)">
                    <h2>What services do you offer?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-2">
                    <p>We offer a wide range of services including:</p>
                    <ul>
                        <li>General construction and repairs</li>
                        <li>Electrical work</li>
                        <li>Plumbing</li>
                        <li>Home maintenance</li>
                        <li>Renovation and remodeling</li>
                    </ul>
                    <p>For more details, you can check our "Services" section on the website.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(3)">
                    <h2>How can I pay for a job?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-3">
                    <p>Payments can be made securely through the platform using the following steps:</p>
                    <ol>
                        <li>Once the job is completed, go to the "Payments" section in your dashboard.</li>
                        <li>Choose the payment method (credit card, debit card, or online wallet).</li>
                        <li>Enter your payment details and confirm the transaction.</li>
                        <li>You will receive a payment receipt via email for your records.</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(4)">
                    <h2>How do I rate a laborer?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-4">
                    <p>To rate a laborer, follow these steps:</p>
                    <ol>
                        <li>Go to the "My Jobs" section after the job is completed.</li>
                        <li>Find the job you wish to rate and click on it.</li>
                        <li>Click on the "Rate Laborer" button.</li>
                        <li>Give the laborer a rating (1 to 5 stars) and write a brief review based on the service provided.</li>
                        <li>Click "Submit" to finalize your rating.</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(5)">
                    <h2>How do I change my account password?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-5">
                    <p>To change your account password, do the following:</p>
                    <ol>
                        <li>Log in to your account and go to "Account Settings".</li>
                        <li>Click on the "Change Password" option.</li>
                        <li>Enter your current password and then input your new password.</li>
                        <li>Confirm the new password and click "Save Changes".</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(6)">
                    <h2>Can I post a job as a customer?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-6">
                    <p>Yes, customers can post jobs. Follow these steps:</p>
                    <ol>
                        <li>Log in to your account and go to the "Post a Job" section.</li>
                        <li>Fill in the job details such as description, location, required skills, and timeline.</li>
                        <li>Click "Submit" to publish your job listing and wait for laborers to apply.</li>
                        <li>You can review applicants, check their profiles, and select the best fit for the job.</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 7 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(7)">
                    <h2>How do I apply for a job as a laborer?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-7">
                    <p>To apply for a job as a laborer, do the following:</p>
                    <ol>
                        <li>Log in to your account and go to the "Browse Jobs" section.</li>
                        <li>Find jobs that match your skills and location.</li>
                        <li>Click "Apply" on the job listing you are interested in.</li>
                        <li>You will receive a notification if your application is accepted.</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ 8 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(8)">
                    <h2>How do I contact customer support?</h2>
                    <span class="icon">&#43;</span>
                </div>
                <div class="faq-answer" id="answer-8">
                    <p>If you need assistance, follow these steps:</p>
                    <ol>
                        <li>Go to the "Support Center" in the footer of the website.</li>
                        <li>Click "Contact Support" and fill in the form with your query.</li>
                        <li>Alternatively, you can email us directly at atharvshinde3093@gmail.com.</li>
                    </ol>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleAnswer(faqId) {
            const answer = document.getElementById(`answer-${faqId}`);
            const question = document.querySelector(`#answer-${faqId}`).previousElementSibling;
            
            // Toggle the visibility of the answer
            if (answer.style.display === "block") {
                answer.style.display = "none";
                question.classList.remove('open');
            } else {
                answer.style.display = "block";
                question.classList.add('open');
            }
        }
    </script>

</body>
</html>
