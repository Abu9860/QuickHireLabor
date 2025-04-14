<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'laborer') {
    header("Location: login.php");
    exit();
}

$laborer_id = $_SESSION['user_id'];

// Get payment summary
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as total_earnings,
        SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) as pending_amount
    FROM jobs j
    LEFT JOIN payments p ON j.id = p.job_id
    WHERE j.laborer_id = ?
");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$payment_summary = $stmt->get_result()->fetch_assoc();

// Get payment history
$stmt = $conn->prepare("
    SELECT p.*, j.title as job_title, u.name as employer_name,
           DATE_FORMAT(p.created_at, '%b %d, %Y') as payment_date
    FROM payments p
    JOIN jobs j ON p.job_id = j.id
    JOIN users u ON j.customer_id = u.id
    WHERE j.laborer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Tracking | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/laborer.css">
    <style>
        .payment-overview {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .balance-card {
            text-align: center;
            padding: 20px;
        }
        .balance {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
            margin: 20px 0;
        }
        .withdraw-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .payment-history table {
            margin-top: 20px;
        }
        .payment-history .completed { color: #28a745; }
        .payment-history .pending { color: #ffc107; }
        .payment-history .refunded { color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="content">
        <header>
            <h1>Payment Tracking</h1>
        </header>

        <!-- Payment Overview -->
        <section class="payment-overview">
            <h2>Payment Summary</h2>
            <div class="balance-card">
                <h3>Current Balance</h3>
                <p class="balance">Rs.<?php echo number_format($payment_summary['pending_amount'], 2); ?></p>
                <button class="withdraw-btn" onclick="alert('Withdrawal feature coming soon!')">Withdraw Funds</button>
            </div>
        </section>

        <!-- Payment History -->
        <section class="payment-history">
            <h2>Payment History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Job</th>
                        <th>Employer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['payment_date']; ?></td>
                        <td><?php echo htmlspecialchars($payment['job_title']); ?></td>
                        <td><?php echo htmlspecialchars($payment['employer_name']); ?></td>
                        <td>Rs.<?php echo number_format($payment['amount'], 2); ?></td>
                        <td class="<?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No payment history found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
