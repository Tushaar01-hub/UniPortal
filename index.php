<?php
// ===== Database Connection =====
$host = "localhost";
$user = "root";
$pass = "root123"; // enter your MySQL password if any
$dbname = "bte_result_system"; // your database name
$port="3307";

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===== Get Institute List =====
$institutes_res = $conn->query("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");
$institutes = [];
if ($institutes_res && $institutes_res->num_rows > 0) {
    $institutes = $institutes_res->fetch_all(MYSQLI_ASSOC);
}

// ===== Handle Principal Info Request =====
$institute_info = null;
if (isset($_POST['inst_info']) && $_POST['inst_info'] !== "") {
    $inst_id = (int) $_POST['inst_info'];
    $sql = "SELECT Inst_Name, Principal, Contact_No1, Contact_No2, Contact_No3 
            FROM institution 
            WHERE Inst_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $inst_id);
        $stmt->execute();
        $institute_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// ===== Handle Course List Request =====
$courses = null;
if (isset($_POST['institute']) && $_POST['institute'] !== "") {
    $inst_id = (int) $_POST['institute'];
    $sql = "SELECT p.Prog_Name, ip.Seats 
            FROM institution_program ip
            JOIN program p ON ip.Prog_ID = p.Prog_ID
            WHERE ip.Inst_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $inst_id);
        $stmt->execute();
        $courses = $stmt->get_result();
        $stmt->close();
    }
}

// ===== Handle Registration Form =====
$registration_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_submit'])) {
    // Create username table if it doesn't exist
    $table_sql = "CREATE TABLE IF NOT EXISTS username (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL
    )";
    $conn->query($table_sql);
    
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM username WHERE email = '$email'";
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        $registration_message = "<span style='color: red;'>Error: Email already exists!</span>";
    } else {
        // Insert into database
        $sql = "INSERT INTO username (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
        
        if ($conn->query($sql) === TRUE) {
            $registration_message = "<span style='color: green;'>Registration successful! Welcome, $name!</span>";
        } else {
            $registration_message = "<span style='color: red;'>Error: " . $conn->error . "</span>";
        }
    }
}
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
    height: 60px;              /* 🔹 Reduced size */
    width: auto;               /* Keep natural aspect ratio */
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
    font-size: 22px;           /* Slightly smaller for better alignment */
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
  /* Layout Styles */
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
  /* Dashboard container */
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
</style>

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
        <div class="message"><?= $registration_message ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
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
    <p>Your password will be securely stored and encrypted.</p>
    <p style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px;">
      <strong>Important:</strong> Keep your credentials safe and do not share your password with anyone.
    </p>
  </div>
</div>

<!-- BTE Dashboard Section -->
<div class="dashboard-container">
  <div class="container">

    <!-- 🔹 Form 1: Show Principal & Contact Info -->
    <form method="POST" action="">
        <h3>View Institute Information</h3>
        <select name="inst_info" required>
            <option value="">-- Select Institute --</option>
            <?php foreach ($institutes as $row): ?>
                <option value="<?= (int)$row['Inst_ID'] ?>"
                    <?= (isset($_POST['inst_info']) && $_POST['inst_info'] == $row['Inst_ID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['Inst_Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Show Info</button>
    </form>

    <?php if ($institute_info): ?>
        <div class="info">
            <h3><?= htmlspecialchars($institute_info['Inst_Name']) ?></h3>
            <p><strong>Principal:</strong> <?= htmlspecialchars($institute_info['Principal'] ?? '-') ?></p>
            <p><strong>Contact Numbers:</strong>
                <?= htmlspecialchars($institute_info['Contact_No1'] ?? '-') ?>,
                <?= htmlspecialchars($institute_info['Contact_No2'] ?? '-') ?>,
                <?= htmlspecialchars($institute_info['Contact_No3'] ?? '-') ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- 🔹 Form 2: Show Course List -->
    <form method="POST" action="">
        <h3>View Courses Offered by Institute</h3>
        <select name="institute" required>
            <option value="">-- Select Institute --</option>
            <?php foreach ($institutes as $row): ?>
                <option value="<?= (int)$row['Inst_ID'] ?>"
                    <?= (isset($_POST['institute']) && $_POST['institute'] == $row['Inst_ID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['Inst_Name']) ?>
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
                    <td><?= htmlspecialchars($row['Prog_Name']) ?></td>
                    <td><?= htmlspecialchars($row['Seats']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif (isset($_POST['institute'])): ?>
        <p><em>No courses found for this institute.</em></p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
