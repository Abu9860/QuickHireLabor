<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Get monthly earnings
$monthly_earnings = [];
$sql = "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(amount) as total
        FROM payments 
        WHERE status = 'completed'
        GROUP BY month 
        ORDER BY month DESC 
        LIMIT 12";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $monthly_earnings[$row['month']] = $row['total'];
}

// Get service statistics
$service_stats = [];
$sql = "SELECT 
        s.name,
        COUNT(j.id) as job_count,
        SUM(CASE WHEN j.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs
        FROM services s
        LEFT JOIN jobs j ON j.title LIKE CONCAT('%', s.name, '%')
        GROUP BY s.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $service_stats[] = $row;
}

// Get laborer performance
$laborer_stats = [];
$sql = "SELECT 
        u.name,
        COUNT(j.id) as total_jobs,
        AVG(r.rating) as avg_rating
        FROM users u
        LEFT JOIN jobs j ON j.laborer_id = u.id
        LEFT JOIN ratings r ON r.laborer_id = u.id
        WHERE u.role = 'laborer'
        GROUP BY u.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $laborer_stats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Quick-Hire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="content">
        <header>
            <h2>Reports & Analytics</h2>
        </header>

        <div class="report-section">
            <h3>Monthly Earnings</h3>
            <canvas id="earningsChart"></canvas>
        </div>

        <div class="report-section">
            <h3>Service Performance</h3>
            <table>
                <tr>
                    <th>Service</th>
                    <th>Total Jobs</th>
                    <th>Completed Jobs</th>
                    <th>Completion Rate</th>
                </tr>
                <?php foreach ($service_stats as $stat): ?>
                    <tr>
                        <td><?php echo $stat['name']; ?></td>
                        <td><?php echo $stat['job_count']; ?></td>
                        <td><?php echo $stat['completed_jobs']; ?></td>
                        <td>
                            <?php 
                            $rate = $stat['job_count'] > 0 ? 
                                   ($stat['completed_jobs'] / $stat['job_count']) * 100 : 0;
                            echo number_format($rate, 1) . '%';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="report-section">
            <h3>Laborer Performance</h3>
            <table>
                <tr>
                    <th>Laborer</th>
                    <th>Total Jobs</th>
                    <th>Average Rating</th>
                </tr>
                <?php foreach ($laborer_stats as $stat): ?>
                    <tr>
                        <td><?php echo $stat['name']; ?></td>
                        <td><?php echo $stat['total_jobs']; ?></td>
                        <td>
                            <?php 
                            $rating = $stat['avg_rating'] ? 
                                     number_format($stat['avg_rating'], 1) : 'N/A';
                            echo $rating;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <script>
        // Monthly earnings chart
        const ctx = document.getElementById('earningsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($monthly_earnings)); ?>,
                datasets: [{
                    label: 'Monthly Earnings ($)',
                    data: <?php echo json_encode(array_values($monthly_earnings)); ?>,
                    borderColor: '#4CAF50',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <style>
        .report-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        canvas {
            max-height: 400px;
        }
    </style>
</body>
</html>
