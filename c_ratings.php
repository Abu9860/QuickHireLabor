<?php
require_once 'config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'submit_review':
                    $job_id = (int)$_POST['job_id'];
                    $rating = (int)$_POST['rating'];
                    $feedback = sanitize_input($_POST['feedback']);
                    
                    // Verify job belongs to user and is completed
                    $stmt = $conn->prepare("SELECT laborer_id FROM jobs WHERE id = ? AND customer_id = ? AND status = 'completed'");
                    $stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
                    $stmt->execute();
                    $job = $stmt->get_result()->fetch_assoc();
                    
                    if (!$job) {
                        throw new Exception('Invalid job or not completed');
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO ratings (job_id, customer_id, laborer_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiiis", $job_id, $_SESSION['user_id'], $job['laborer_id'], $rating, $feedback);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Review submitted successfully';
                    }
                    break;
                    
                case 'edit_review':
                    $rating_id = (int)$_POST['rating_id'];
                    $feedback = sanitize_input($_POST['feedback']);
                    
                    $stmt = $conn->prepare("UPDATE ratings SET feedback = ? WHERE id = ? AND customer_id = ?");
                    $stmt->bind_param("sii", $feedback, $rating_id, $_SESSION['user_id']);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Review updated successfully';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Get completed jobs without reviews
$stmt = $conn->prepare("
    SELECT j.*, CONCAT(u.first_name, ' ', u.last_name) AS laborer_name 
    FROM jobs j 
    JOIN users u ON j.laborer_id = u.id
    LEFT JOIN ratings r ON j.id = r.job_id 
    WHERE j.customer_id = ? AND j.status = 'completed' AND r.id IS NULL
    ORDER BY j.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pending_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get ratings data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT r.*, j.title AS job_title, j.description AS job_description 
                        FROM ratings r 
                        JOIN jobs j ON r.job_id = j.id 
                        WHERE j.customer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ratings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ratings & Reviews - QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin-left: 280px;  // Changed from margin: 30px auto
            padding: 20px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .rating-header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .rating-header h2 {
            margin: 0;
            text-align: center;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }

        .rating-list {
            list-style: none;
            padding: 0;
        }

        .rating-item {
            background: #e9ecef;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.3s;
            cursor: pointer;
        }

        .rating-item:hover {
            background: #d6d8db;
        }

        .stars {
            color: gold;
            font-size: 20px;
        }

        .review-text {
            font-style: italic;
            color: #555;
        }

        .review-details {
            display: none;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 5px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        .btn-add-review {
            background: #007bff;
            color: white;
            display: block;
            width: 100%;
            margin-top: 15px;
        }

        .review-form {
            display: none;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
        }

        .review-form select, .review-form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .review-form button {
            margin-top: 10px;
        }

        .completed-job {
            color: green;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
        }

    </style>
</head>
<body>
    <?php include 'includes/customer_sidebar.php'; ?>

    <div class="container">
        <div class="rating-header">
            <h2>Ratings & Reviews</h2>
        </div>
        <ul class="rating-list">
            <?php foreach ($ratings as $rating): ?>
            <li class="rating-item" data-rating="<?php echo htmlspecialchars($rating['rating']); ?>">
                <span><strong><?php echo htmlspecialchars($rating['laborer_name']); ?></strong> - <?php echo htmlspecialchars($rating['job_title']); ?></span>
                <span class="stars"><?php echo str_repeat('★', $rating['rating']) . str_repeat('☆', 5 - $rating['rating']); ?></span>
            </li>
            <div class="review-details">
                <p class="review-text">"<?php echo htmlspecialchars($rating['feedback']); ?>"</p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($rating['created_at']); ?></p>
                <div class="edit-review" style="display: none;">
                    <textarea class="edit-text">"<?php echo htmlspecialchars($rating['feedback']); ?>"</textarea>
                    <button class="btn btn-save">Save</button>
                </div>
            </div>
            <?php endforeach; ?>
        </ul>

        <button class="btn btn-add-review">Add New Review</button>

        <div class="review-form">
            <h3>Submit a Review</h3>
            <label for="laborer">Select Completed Job:</label>
            <select id="jobSelect" required>
                <option value="">Select a job...</option>
                <?php foreach ($pending_reviews as $job): ?>
                    <option value="<?php echo $job['id']; ?>">
                        <?php echo htmlspecialchars($job['laborer_name'] . ' - ' . $job['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="rating">Rating:</label>
            <select id="rating">
                <option value="5">★★★★★ - Excellent</option>
                <option value="4">★★★★☆ - Very Good</option>
                <option value="3">★★★☆☆ - Good</option>
                <option value="2">★★☆☆☆ - Fair</option>
                <option value="1">★☆☆☆☆ - Poor</option>
            </select>
            <label for="review">Your Review:</label>
            <textarea id="review" rows="4" placeholder="Write your review here..."></textarea>
            <button class="btn btn-save" id="submitReview">Submit Review</button>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $(".rating-item").click(function () {
                $(this).next(".review-details").slideToggle();
            });

            $(".btn-edit").click(function () {
                $(this).parent().next(".review-details").find(".edit-review").slideToggle();
            });

            $(".btn-save").click(function () {
                var newText = $(this).prev(".edit-text").val();
                $(this).closest(".review-details").find(".review-text").text(newText);
                $(this).parent(".edit-review").slideUp();
            });

            $(".btn-add-review").click(function () {
                $(".review-form").slideToggle();
            });

            $("#submitReview").click(function() {
                const jobId = $("#jobSelect").val();
                const rating = $("#rating").val();
                const reviewText = $("#review").val();

                if (!jobId || !reviewText.trim()) {
                    alert("Please fill all required fields");
                    return;
                }

                fetch('c_ratings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=submit_review&job_id=${jobId}&rating=${rating}&feedback=${encodeURIComponent(reviewText)}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the review');
                });
            });

            // Add event handler for editing reviews
            $(".btn-edit").click(function(e) {
                e.stopPropagation();
                const ratingId = $(this).data('rating-id');
                const newText = $(this).closest('.review-details').find('.edit-text').val();

                fetch('c_ratings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=edit_review&rating_id=${ratingId}&feedback=${encodeURIComponent(newText)}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the review');
                });
            });
        });
    </script>

</body>
</html>