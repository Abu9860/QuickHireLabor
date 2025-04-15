<?php
require_once 'config.php';

if (!isLoggedIn() || !isCustomer()) {
    header("Location: login.php");
    exit();
}

// Get user data and skills
$user_id = $_SESSION['user_id'];
<<<<<<< HEAD
$stmt = $conn->prepare("SELECT NULL AS profile_pic, 
                              CONCAT(first_name, ' ', last_name) AS name, 
                              email, phone, id, role
                       FROM users 
                       WHERE id = ?");
=======
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

<<<<<<< HEAD
// Check if the skills table exists and fetch skills
$hasSkillsTable = $conn->query("SHOW TABLES LIKE 'skills'")->num_rows > 0;

if ($hasSkillsTable) {
    $stmt = $conn->prepare("SELECT * FROM skills ORDER BY name");
    $stmt->execute();
    $skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Provide default skills
    $skills = [
        ['id' => 1, 'name' => 'Plumbing'],
        ['id' => 2, 'name' => 'Electrical'],
        ['id' => 3, 'name' => 'Carpentry'],
        ['id' => 4, 'name' => 'Painting'],
        ['id' => 5, 'name' => 'Cleaning']
    ];
}
=======
// Get available skills before the form
$stmt = $conn->prepare("SELECT * FROM skills ORDER BY name");
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['jobTitle']) || empty($_POST['jobDescription']) || 
            empty($_POST['jobLocation']) || empty($_POST['jobDate']) || 
            empty($_POST['budget'])) {
            throw new Exception('All required fields must be filled');
        }

        $title = sanitize_input($_POST['jobTitle']);
        $description = sanitize_input($_POST['jobDescription']);
        $location = sanitize_input($_POST['jobLocation']);
        $budget = floatval($_POST['budget']);
        $date = sanitize_input($_POST['jobDate']);
        $skills = isset($_POST['skills']) ? implode(',', $_POST['skills']) : '';

        // Start transaction
        $conn->begin_transaction();

<<<<<<< HEAD
        // Remove both 'scheduled_date' and 'required_skills' from the column list
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, location, budget, customer_id, status) 
                               VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssdi", $title, $description, $location, $budget, $user_id);
=======
        // Insert job
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, location, price, scheduled_date, customer_id, required_skills, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssdsss", $title, $description, $location, $budget, $date, $user_id, $skills);
>>>>>>> 502667e9b8a70d5c5e5573eee70fa1d456f706f9

        if (!$stmt->execute()) {
            throw new Exception($conn->error);
        }

        $job_id = $conn->insert_id;

        // Handle image uploads if any
        if (!empty($_FILES['jobImages']['name'][0])) {
            $upload_dir = "uploads/jobs/{$job_id}";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['jobImages']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['jobImages']['error'][$key] === 0) {
                    $file_path = handle_file_upload([
                        'name' => $_FILES['jobImages']['name'][$key],
                        'type' => $_FILES['jobImages']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['jobImages']['error'][$key],
                        'size' => $_FILES['jobImages']['size'][$key]
                    ], $upload_dir);
                }
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Job posted successfully']);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Job posting error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error posting job: ' . $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - QuickHire Labor</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .job-form-container {
            max-width: 800px;
            margin-left: 280px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }

        .input-group input,
        .input-group textarea,
        .input-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-group input:focus,
        .input-group textarea:focus,
        .input-group select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .skill-checkbox {
            display: none;
        }

        .skill-label {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .skill-checkbox:checked + .skill-label {
            background: #4CAF50;
            color: white;
        }

        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #4CAF50;
        }

        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #45a049;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 5px;
            font-size: 14px;
        }

        .success-message {
            color: #2ecc71;
            margin-top: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/customer_sidebar.php'; ?>

    <div class="job-form-container">
        <form id="jobPostForm" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Job Details</h3>
                <div class="input-group">
                    <label for="jobTitle">Job Title*</label>
                    <input type="text" id="jobTitle" name="jobTitle" required>
                </div>
                <div class="input-group">
                    <label for="jobDescription">Job Description*</label>
                    <textarea id="jobDescription" name="jobDescription" rows="4" required></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>Location & Schedule</h3>
                <div class="input-group">
                    <label for="jobLocation">Location*</label>
                    <input type="text" id="jobLocation" name="jobLocation" required>
                </div>
                <div class="input-group">
                    <label for="jobDate">Preferred Date*</label>
                    <input type="date" id="jobDate" name="jobDate" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Required Skills</h3>
                <div class="skills-grid">
                    <?php foreach ($skills as $skill): ?>
                        <div>
                            <input type="checkbox" 
                                   id="skill_<?php echo $skill['id']; ?>" 
                                   name="skills[]" 
                                   value="<?php echo htmlspecialchars($skill['name']); ?>"
                                   class="skill-checkbox">
                            <label for="skill_<?php echo $skill['id']; ?>" 
                                   class="skill-label">
                                <?php echo htmlspecialchars($skill['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-section">
                <h3>Budget</h3>
                <div class="input-group">
                    <label for="budget">Estimated Budget (₹)*</label>
                    <input type="number" id="budget" name="budget" min="100" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Additional Information</h3>
                <div class="input-group">
                    <label for="jobImages">Upload Images (Optional)</label>
                    <input type="file" id="jobImages" name="jobImages[]" multiple accept="image/*">
                    <div id="imagePreview" class="image-preview"></div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Post Job</button>
        </form>
    </div>

    <script>
        document.getElementById('jobImages').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            [...e.target.files].forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });

        document.getElementById('jobPostForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Posting...';
            submitBtn.disabled = true;

            fetch('c_post_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'c_job_status.php';
                } else {
                    throw new Error(data.message || 'Error posting job');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while posting the job');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('jobDate').min = today;
    </script>
</body>
</html>