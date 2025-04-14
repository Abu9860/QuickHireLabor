<?php
require_once 'config.php';

if (!isLoggedIn() || !isLaborer()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $subject = sanitize_input($_POST['subject']);
        $message = sanitize_input($_POST['message']);
        $priority = sanitize_input($_POST['priority']);
        
        $stmt = $conn->prepare("
            INSERT INTO support_tickets (user_id, subject, message, priority) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $subject, $message, $priority);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Support ticket submitted successfully';
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode($response);
        exit();
    }
}

// Get existing tickets
$stmt = $conn->prepare("
    SELECT * FROM support_tickets 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin-left: 280px;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .support-form,
        .tickets-list {
            margin-top: 30px;
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

        .submit-btn {
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

        .submit-btn:hover {
            background: #45a049;
        }

        .ticket-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-open { background: #ffc107; }
        .status-in_progress { background: #17a2b8; color: white; }
        .status-closed { background: #28a745; color: white; }

        .priority-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .priority-low { background: #28a745; color: white; }
        .priority-medium { background: #ffc107; color: #333; }
        .priority-high { background: #dc3545; color: white; }

        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .admin-response {
            background: #e9ecef;
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <div class="container">
        <h1>Support Center</h1>

        <div class="support-form">
            <h2>Submit a New Ticket</h2>
            <form id="ticketForm">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Submit Ticket</button>
            </form>
        </div>

        <div class="tickets-list">
            <h2>My Tickets</h2>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card">
                    <div class="ticket-header">
                        <h3><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                        <span class="status-badge <?php echo $ticket['status']; ?>">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </div>
                    <p class="ticket-message"><?php echo htmlspecialchars($ticket['message']); ?></p>
                    <div class="ticket-footer">
                        <span class="priority-badge <?php echo $ticket['priority']; ?>">
                            <?php echo ucfirst($ticket['priority']); ?>
                        </span>
                        <span class="ticket-date">
                            <?php echo date('M j, Y g:i a', strtotime($ticket['created_at'])); ?>
                        </span>
                    </div>
                    <?php if ($ticket['admin_response']): ?>
                        <div class="admin-response">
                            <strong>Admin Response:</strong>
                            <p><?php echo htmlspecialchars($ticket['admin_response']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('laborer_support.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
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
                alert('An error occurred while submitting the ticket');
            });
        });
    </script>
</body>
</html>