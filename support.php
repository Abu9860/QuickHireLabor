<?php
require_once 'config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = $error_msg = '';

// Handle ticket response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    try {
        $ticket_id = sanitize_input($_POST['ticket_id']);
        $response = sanitize_input($_POST['response']);
        $new_status = sanitize_input($_POST['status']);
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Update ticket status and add response
        $stmt = $conn->prepare("UPDATE support_tickets SET status = ?, admin_response = ?, responded_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $new_status, $response, $ticket_id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $success_msg = "Response submitted and status updated successfully!";
        } else {
            throw new Exception("Failed to update ticket");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Get all tickets with user information
try {
    $stmt = $conn->prepare("
<<<<<<< HEAD
        SELECT t.*, u.first_name, u.email, 
=======
        SELECT t.*, u.name, u.email, 
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
               DATE_FORMAT(t.created_at, '%b %d, %Y %H:%i') as ticket_date,
               DATE_FORMAT(t.responded_at, '%b %d, %Y %H:%i') as response_date
        FROM support_tickets t
        JOIN users u ON t.user_id = u.id 
        ORDER BY 
            CASE 
                WHEN t.status = 'open' THEN 1
                WHEN t.status = 'in_progress' THEN 2
                ELSE 3
            END,
            t.created_at DESC
    ");
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch tickets: " . $conn->error);
    }
    
    $tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_msg = "Error: " . $e->getMessage();
    $tickets = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Management | Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .support-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .ticket-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .ticket-form textarea {
            min-height: 150px;
        }
        .faq-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .ticket-history {
            margin-top: 20px;
        }
        .priority-high { color: #dc3545; }
        .priority-medium { color: #ffc107; }
        .priority-low { color: #28a745; }
        .status-open { color: #17a2b8; }
        .status-closed { color: #6c757d; }
        .ticket-details {
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .status-form {
            display: inline-block;
        }
        .message-content {
            max-height: 100px;
            overflow-y: auto;
            padding: 5px;
        }
        .response-form {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .response-form textarea {
            width: 100%;
            min-height: 60px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .previous-response {
            margin-top: 5px;
            color: #666;
        }
        .ticket-row.status-open { background-color: #fff; }
        .ticket-row.status-in_progress { background-color: #fff8e1; }
        .ticket-row.status-closed { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="content">
        <header>
            <h1>Support Ticket Management</h1>
        </header>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <section class="ticket-history">
            <h2>All Support Tickets</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr class="ticket-row <?php echo 'status-' . $ticket['status']; ?>">
                        <td><?php echo $ticket['ticket_date']; ?></td>
                        <td>
<<<<<<< HEAD
                            <?php echo htmlspecialchars($ticket['first_name']); ?><br>
=======
                            <?php echo htmlspecialchars($ticket['name']); ?><br>
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
                            <small><?php echo htmlspecialchars($ticket['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                        <td>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                            </div>
                        </td>
                        <td class="priority-<?php echo $ticket['priority']; ?>">
                            <?php echo ucfirst($ticket['priority']); ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                <?php echo ucfirst($ticket['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="" class="response-form">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                <textarea name="response" required placeholder="Enter your response..."><?php echo htmlspecialchars($ticket['admin_response'] ?? ''); ?></textarea>
                                <select name="status" required>
                                    <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                                <button type="submit" name="submit_response" class="btn-primary">Submit Response</button>
                            </form>
                            <?php if ($ticket['admin_response']): ?>
                                <div class="previous-response">
                                    <small>Last response: <?php echo $ticket['response_date']; ?></small>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No support tickets found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
