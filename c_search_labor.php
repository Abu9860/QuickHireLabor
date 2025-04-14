<?php
require_once 'config.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    header("Location: login.php");
    exit();
}

// Get user data for profile picture
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle AJAX search requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
    $skill = isset($_GET['skill']) ? sanitize_input($_GET['skill']) : '';
    
    $sql = "SELECT u.*, 
            AVG(r.rating) as avg_rating, 
            COUNT(DISTINCT r.id) as total_ratings,
            COUNT(DISTINCT j.id) as completed_jobs,
            GROUP_CONCAT(DISTINCT s.name) as skills_list
            FROM users u 
            LEFT JOIN ratings r ON u.id = r.laborer_id 
            LEFT JOIN jobs j ON u.id = j.laborer_id AND j.status = 'completed'
            LEFT JOIN laborer_skills ls ON u.id = ls.laborer_id
            LEFT JOIN skills s ON ls.skill_id = s.id
            WHERE u.role = 'laborer'";
    
    $params = [];
    $types = "";
    
    if ($search) {
        $sql .= " AND (u.name LIKE ? OR u.description LIKE ? OR s.name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    if ($location) {
        $sql .= " AND u.address LIKE ?";
        $params[] = "%$location%";
        $types .= "s";
    }
    
    if ($skill) {
        $sql .= " AND s.name = ?";
        $params[] = $skill;
        $types .= "s";
    }
    
    $sql .= " GROUP BY u.id ORDER BY avg_rating DESC, completed_jobs DESC";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $laborers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($laborers);
    exit();
}

// Get available locations
$locations = $conn->query("SELECT DISTINCT address FROM users WHERE role = 'laborer'")->fetch_all(MYSQLI_ASSOC);

// Get available skills - modify this part
$skills = $conn->query("SELECT name FROM skills ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Laborers - QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1100px;
            margin-left: 280px;  // Changed from 250px
            padding: 20px;
        }

        .search-filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-filters input,
        .search-filters select {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-filters input:focus,
        .search-filters select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .search-results {
            margin-top: 20px;
            display: grid;
            gap: 20px;
        }

        .laborer-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .laborer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .laborer-info {
            flex: 1;
        }

        .laborer-info h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 20px;
        }

        .laborer-info p {
            margin: 5px 0;
            color: #666;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 18px;
            margin-top: 5px;
        }

        .view-details-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            white-space: nowrap;
        }

        .view-details-btn:hover {
            background: #45a049;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #666;
            font-size: 18px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 10px;
            }

            .search-filters {
                flex-direction: column;
            }

            .laborer-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/customer_sidebar.php'; ?>

    <div class="container">
        <div class="search-filters">
            <input type="text" id="searchInput" placeholder="Search by name or skills...">
            <select id="locationFilter">
                <option value="">All Locations</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?php echo htmlspecialchars($location['address']); ?>">
                        <?php echo htmlspecialchars($location['address']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="skillFilter">
                <option value="">All Skills</option>
                <?php foreach ($skills as $skill): ?>
                    <option value="<?php echo htmlspecialchars($skill['name']); ?>">
                        <?php echo htmlspecialchars($skill['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="searchResults" class="search-results"></div>
    </div>

    <script>
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function performSearch() {
            const searchInput = document.getElementById('searchInput').value;
            const locationFilter = document.getElementById('locationFilter').value;
            const skillFilter = document.getElementById('skillFilter').value;
            
            fetch(`c_search_labor.php?search=${searchInput}&location=${locationFilter}&skill=${skillFilter}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const resultsContainer = document.getElementById('searchResults');
                resultsContainer.innerHTML = '';
                
                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="no-results">No laborers found matching your criteria</div>';
                    return;
                }
                
                data.forEach(laborer => {
                    const rating = parseFloat(laborer.avg_rating) || 0;
                    const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
                    
                    const card = document.createElement('div');
                    card.className = 'laborer-card';
                    card.innerHTML = `
                        <div class="laborer-info">
                            <h3>${laborer.name}</h3>
                            <p>Skills: ${laborer.skills_list || 'Not specified'}</p>
                            <p>Location: ${laborer.address || 'Not specified'}</p>
                            <p class="rating-stars">${stars} (${laborer.total_ratings || 0} reviews)</p>
                        </div>
                        <a href="laborer_details.php?id=${laborer.id}" class="view-details-btn">View Details</a>
                    `;
                    resultsContainer.appendChild(card);
                });
            })
            .catch(error => console.error('Error:', error));
        }

        // Add event listeners with debounce
        document.getElementById('searchInput').addEventListener('input', debounce(performSearch, 300));
        document.getElementById('locationFilter').addEventListener('change', performSearch);
        document.getElementById('skillFilter').addEventListener('change', performSearch);

        // Initial search on page load
        performSearch();
    </script>
</body>
</html>
