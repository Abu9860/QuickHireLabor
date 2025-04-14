<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a laborer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'laborer') {
    header("Location: login.php");
    exit();
}

// Handle job application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    $laborer_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE jobs SET laborer_id = ?, status = 'assigned' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $laborer_id, $job_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Successfully applied for the job!";
    } else {
        $_SESSION['error'] = "Error applying for the job.";
    }
    
    header("Location: laborer_jobs.php");
    exit();
}

// Get available jobs
$search = $_GET['search'] ?? '';
$skill = $_GET['skill'] ?? '';
$location = $_GET['location'] ?? '';

$query = "SELECT * FROM jobs WHERE status = 'pending'";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $search = "%$search%";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if ($skill) {
    $query .= " AND required_skills LIKE ?";
    $skill = "%$skill%";
    $params[] = $skill;
    $types .= "s";
}

if ($location) {
    $query .= " AND location = ?";
    $params[] = $location;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct locations and skills for filters
$locations = $conn->query("SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL")->fetch_all(MYSQLI_ASSOC);
$skills = $conn->query("SELECT DISTINCT name FROM skills")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings | QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/laborer.css">
    <style>
        .job-filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .job-filters input,
        .job-filters select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .job-filters button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .job-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .apply-btn {
            background: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'includes/laborer_sidebar.php'; ?>

    <main class="content">
        <header>
            <h1>Find Job Opportunities</h1>
            <p>Browse and apply for jobs that match your skills and location.</p>
        </header>

        <!-- Search & Filter Section -->
        <div class="job-filters">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search for jobs..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="skill">
                    <option value="">Filter by Skill</option>
                    <?php foreach ($skills as $skill): ?>
                        <option value="<?php echo htmlspecialchars($skill['name']); ?>"><?php echo htmlspecialchars($skill['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="location">
                    <option value="">Filter by Location</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc['location']); ?>"><?php echo htmlspecialchars($loc['location']); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Apply Filters</button>
            </form>
        </div>

        <!-- Job Listings -->
        <div class="job-listings">
            <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p><strong>Pay:</strong> Rs.<?php echo number_format($job['price'], 2); ?></p>
                <p><?php echo htmlspecialchars($job['description']); ?></p>
                <form method="POST" action="">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                    <button type="submit" class="apply-btn">Apply Now</button>
                </form>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($jobs)): ?>
            <p>No jobs found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>