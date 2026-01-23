<?php
// ===== Configuration =====
// TODO: Move these to a config file outside web root or use environment variables
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "root123"; // Change this!
$dbname = getenv('DB_NAME') ?: "bte_result_system";
$port = getenv('DB_PORT') ?: "3307";

// Start session for CSRF protection
session_start();

// ===== Database Connection =====
$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");

// ===== Helper Functions =====
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function validateRole($role) {
    return in_array($role, ['student', 'faculty']);
}

// ===== Get Institute List =====
$institutes = [];
$stmt = $conn->prepare("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $institutes = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// ===== Handle Principal Info Request =====
$institute_info = null;
if (isset($_POST['inst_info']) && $_POST['inst_info'] !== "" && isset($_POST['csrf_token_info'])) {
    if (validateCSRFToken($_POST['csrf_token_info'])) {
        $inst_id = filter_var($_POST['inst_info'], FILTER_VALIDATE_INT);
        if ($inst_id !== false && $inst_id > 0) {
            $stmt = $conn->prepare("SELECT Inst_Name, Principal, Contact_No1, Contact_No2, Contact_No3 
                                   FROM institution 
                                   WHERE Inst_ID = ?");
            if ($stmt) {
                $stmt->bind_param("i", $inst_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $institute_info = $result->fetch_assoc();
                $stmt->close();
            }
        }
    }
}

// ===== Handle Course List Request =====
$courses = null;
if (isset($_POST['institute']) && $_POST['institute'] !== "" && isset($_POST['csrf_token_courses'])) {
    if (validateCSRFToken($_POST['csrf_token_courses'])) {
        $inst_id = filter_var($_POST['institute'], FILTER_VALIDATE_INT);
        if ($inst_id !== false && $inst_id > 0) {
            $stmt = $conn->prepare("SELECT p.Prog_Name, ip.Seats 
                                   FROM institution_program ip
                                   JOIN program p ON ip.Prog_ID = p.Prog_ID
                                   WHERE ip.Inst_ID = ?");
            if ($stmt) {
                $stmt->bind_param("i", $inst_id);
                $stmt->execute();
                $courses = $stmt->get_result();
                $stmt->close();
            }
        }
    }
}

// ===== Handle Registration Form =====
$registration_message = "";
$registration_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_submit'])) {
    $errors = [];
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid security token. Please try again.";
    }
    
    // Get and validate inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    
    // Validation
    if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = "Name must be between 2 and 100 characters.";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "Invalid email address.";
    }
    
    if (!validatePassword($password)) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and a number.";
    }
    
    if (!validateRole($role)) {
        $errors[] = "Invalid role selected.";
    }
    
    // If no validation errors, proceed with database operations
    if (empty($errors)) {
        // Create table if it doesn't exist (do this once, ideally in a setup script)
        $table_sql = "CREATE TABLE IF NOT EXISTS username (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        )";
        $conn->query($table_sql);
        
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM username WHERE email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Email already registered.";
            } else {
                // Hash password and insert
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert_stmt = $conn->prepare("INSERT INTO username (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
                    
                    if ($insert_stmt->execute()) {
                        $registration_success = true;
                        $registration_message = "Registration successful! Welcome, " . sanitizeInput($name) . "!";
                    } else {
                        error_log("Registration insert error: " . $insert_stmt->error);
                        $errors[] = "Registration failed. Please try again.";
                    }
                    $insert_stmt->close();
                } else {
                    $errors[] = "System error. Please try again.";
                }
            }
            $check_stmt->close();
        } else {
            $errors[] = "System error. Please try again.";
        }
    }
    
    // Display errors
    if (!empty($errors)) {
        $registration_message = implode("<br>", array_map(function($err) {
            return sanitizeInput($err);
        }, $errors));
    }
}

// Generate CSRF tokens for all forms
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Board of Technical Education, Delhi</title>
  <style>
  body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background-color: #f8f9fa;
  }
  header {
    background-color: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 8px 0;
    height: 90px;
  }
  header img {
    height: 60px;
    width: auto;
  }
  header .emblem {
    position: absolute;
    left: 25px;
  }
  header .flag {
    position: absolute;
    right: 25px;
  }
  header h1 {
    font-size: 22px;
    margin: 0;
    text-align: center;
    line-height: 1.4;
  }
  hr {
    border: none;
    border-top: 3px solid #f1c40f;
    margin: 0;
  }
  h2 {
    color: #003366;
  }
  form {
    margin-bottom: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
  }
  select, button {
    padding: 8px;
    font-size: 16px;
    margin-right: 10px;
  }
  table {
    border-collapse: collapse;
    width: 70%;
    background: #fff;
    margin-top: 20px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
  }
  th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
  }
  th {
    background-color: #eee;
  }
  .info {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    width: 60%;
  }
  .main-wrapper {
    display: flex;
    gap: 20px;
    padding: 30px;
  }
  .registration-section {
    width: 70%;
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  .announcement-bar {
    width: 30%;
    background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%);
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color: white;
    height: fit-content;
  }
  .announcement-bar h3 {
    color: white;
    margin-top: 0;
    text-align: center;
  }
  .announcement-bar p {
    font-size: 14px;
    line-height: 1.6;
  }
  .dashboard-container {
    padding: 30px;
  }
  .registration-section h2 {
    text-align: center;
  }
  .form-group {
    margin-bottom: 15px;
  }
  .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
  }
  .form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
  }
  .form-group button {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
  }
  .form-group button:hover {
    background-color: #0056b3;
  }
  .message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
  }
  .message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  .password-requirements {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
  }
</style>
</head>
<body>

<header>
  <img src="emblem.jpg" alt="Emblem" class="emblem">
  <h1>Board of Technical Education, Pitampura, Delhi 110088</h1>
  <img src="flag.jpg" alt="Flag" class="flag">
</header>
<hr>

<!-- Registration and Announcement Section -->
<div class="main-wrapper">
  <!-- Registration Form (70%) -->
  <div class="registration-section">
    <h2>User Registration</h2>
    <?php if ($registration_message): ?>
        <div class="message <?= $registration_success ? 'success' : 'error' ?>">
            <?= $registration_message ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" required minlength="2" maxlength="100"
                   value="<?= isset($_POST['name']) && !$registration_success ? sanitizeInput($_POST['name']) : '' ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required
                   value="<?= isset($_POST['email']) && !$registration_success ? sanitizeInput($_POST['email']) : '' ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="8">
            <div class="password-requirements">
                Must be at least 8 characters with uppercase, lowercase, and a number
            </div>
        </div>
        
        <div class="form-group">
            <label for="role">Role *</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="student" <?= (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                <option value="faculty" <?= (isset($_POST['role']) && $_POST['role'] === 'faculty') ? 'selected' : '' ?>>Faculty</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" name="register_submit">Register</button>
        </div>
    </form>
  </div>

  <!-- Announcement Bar (30%) -->
  <div class="announcement-bar">
    <h3>📢 Announcements</h3>
    <p><strong>Welcome!</strong> Register to access the BTE Result System.</p>
    <p>Please enter your details carefully. Make sure to choose your correct role - Student or Faculty.</p>
    <p><strong>Password Requirements:</strong></p>
    <ul style="font-size: 13px; line-height: 1.8;">
      <li>At least 8 characters long</li>
      <li>Contains uppercase letters</li>
      <li>Contains lowercase letters</li>
      <li>Contains numbers</li>
    </ul>
    <p style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px;">
      <strong>Important:</strong> Keep your credentials safe and do not share your password with anyone.
    </p>
  </div>
</div>

<!-- BTE Dashboard Section -->
<div class="dashboard-container">
  <div class="container">

    <!-- Form 1: Show Principal & Contact Info -->
    <form method="POST" action="">
        <input type="hidden" name="csrf_token_info" value="<?= $csrf_token ?>">
        <h3>View Institute Information</h3>
        <select name="inst_info" required>
            <option value="">-- Select Institute --</option>
            <?php foreach ($institutes as $row): ?>
                <option value="<?= (int)$row['Inst_ID'] ?>"
                    <?= (isset($_POST['inst_info']) && $_POST['inst_info'] == $row['Inst_ID']) ? 'selected' : '' ?>>
                    <?= sanitizeInput($row['Inst_Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Show Info</button>
    </form>

    <?php if ($institute_info): ?>
        <div class="info">
            <h3><?= sanitizeInput($institute_info['Inst_Name']) ?></h3>
            <p><strong>Principal:</strong> <?= sanitizeInput($institute_info['Principal'] ?? '-') ?></p>
            <p><strong>Contact Numbers:</strong>
                <?= sanitizeInput($institute_info['Contact_No1'] ?? '-') ?>,
                <?= sanitizeInput($institute_info['Contact_No2'] ?? '-') ?>,
                <?= sanitizeInput($institute_info['Contact_No3'] ?? '-') ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Form 2: Show Course List -->
    <form method="POST" action="">
        <input type="hidden" name="csrf_token_courses" value="<?= $csrf_token ?>">
        <h3>View Courses Offered by Institute</h3>
        <select name="institute" required>
            <option value="">-- Select Institute --</option>
            <?php foreach ($institutes as $row): ?>
                <option value="<?= (int)$row['Inst_ID'] ?>"
                    <?= (isset($_POST['institute']) && $_POST['institute'] == $row['Inst_ID']) ? 'selected' : '' ?>>
                    <?= sanitizeInput($row['Inst_Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Show Courses</button>
    </form>

    <?php if ($courses && $courses->num_rows > 0): ?>
        <h3>Courses Offered:</h3>
        <table>
            <tr>
                <th>Program Name</th>
                <th>Seats</th>
            </tr>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <tr>
                    <td><?= sanitizeInput($row['Prog_Name']) ?></td>
                    <td><?= sanitizeInput($row['Seats']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif (isset($_POST['institute']) && validateCSRFToken($_POST['csrf_token_courses'] ?? '')): ?>
        <p><em>No courses found for this institute.</em></p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
<?php
$conn->close();
?>