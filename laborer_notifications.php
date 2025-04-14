<?php
require_once 'config.php';

if (!isLoggedIn() || !isLaborer()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark notifications as read
if (isset($_POST['mark_read'])) {
    $notification_id = (int)$_POST['notification_id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    exit();
}

// Get all notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unread count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - QuickHire Labor</title>
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

        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }

        .notifications-list {
            list-style: none;
            padding: 0;
        }

        .notification-item {
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

        .notification-item:hover {
            background: #d6d8db;
        }

        .notification-item.unread {
            font-weight: bold;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .notification-time {
            font-size: 12px;
            color: #666;
        }

        .no-notifications {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            color: #666;
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
    <?php include 'includes/laborer_sidebar.php'; ?>

    <div class="container">
        <h1>Notifications</h1>
        
        <?php if (!empty($notifications)): ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>"
                         data-id="<?php echo $notification['id']; ?>">
                        <div class="notification-header">
                            <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                            <span class="notification-time">
                                <?php echo date('M j, Y g:i a', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-notifications">No notifications yet</p>
        <?php endif; ?>
    </div>

    <script>
        // Add click handler to mark notifications as read
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.addEventListener('click', function() {
                markAsRead(this.dataset.id);
            });
        });

        function markAsRead(notificationId) {
            fetch('laborer_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mark_read=1&notification_id=${notificationId}`
            })
            .then(() => {
                document.querySelector(`[data-id="${notificationId}"]`).classList.remove('unread');
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>