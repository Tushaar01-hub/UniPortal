<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "root123";
$dbname = "bte_result_system";
$port = "3307";

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Get Institute List
$institutes_res = $conn->query("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");
$institutes = [];
if ($institutes_res && $institutes_res->num_rows > 0) {
    $institutes = $institutes_res->fetch_all(MYSQLI_ASSOC);
}

// Handle Principal Info Request
$institute_info = null;
if (isset($_POST['inst_info']) && $_POST['inst_info'] !== "") {
    $inst_id = (int) $_POST['inst_info'];
    $sql = "SELECT Inst_Name, Principal, Contact_No1, Contact_No2, Contact_No3 FROM institution WHERE Inst_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $inst_id);
        $stmt->execute();
        $institute_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// Handle Course List Request
$courses = null;
if (isset($_POST['institute']) && $_POST['institute'] !== "") {
    $inst_id = (int) $_POST['institute'];
    $sql = "SELECT p.Prog_Name, ip.Seats FROM institution_program ip
            JOIN program p ON ip.Prog_ID = p.Prog_ID WHERE ip.Inst_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $inst_id);
        $stmt->execute();
        $courses = $stmt->get_result();
        $stmt->close();
    }
}

// Handle Registration
$registration_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $check_sql = "SELECT User_ID FROM users WHERE Email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $registration_message = "<span style='color: red;'>Error: Email already exists!</span>";
    } else {
        $sql = "INSERT INTO users (Email, Password, Role, Status) VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $registration_message = "<span style='color: green;'>Registration successful! Please wait for approval.</span>";
        } else {
            $registration_message = "<span style='color: red;'>Error: " . $conn->error . "</span>";
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Board of Technical Education, Delhi</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
  body { margin: 0; font-family: Arial, sans-serif; background: #f8f9fa; }
  header { background: #003366; color: white; display: flex; align-items: center; justify-content: center; position: relative; padding: 8px 0; height: 90px; }
  header img { height: 60px; width: auto; }
  header .emblem { position: absolute; left: 25px; }
  header .flag { position: absolute; right: 25px; }
  header h1 { font-size: 22px; margin: 0; text-align: center; line-height: 1.4; }
  nav { background: #0066cc; padding: 0; }
  nav ul { list-style: none; margin: 0; padding: 0; display: flex; justify-content: center; }
  nav ul li { margin: 0; }
  nav ul li a { display: block; padding: 15px 20px; color: white; text-decoration: none; transition: background 0.3s; }
  nav ul li a:hover { background: #0052a3; }
  hr { border: none; border-top: 3px solid #f1c40f; margin: 0; }
  .main-wrapper { display: flex; gap: 20px; padding: 30px; }
  .registration-section { width: 70%; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
  .announcement-bar { width: 30%; background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%); padding: 20px; border-radius: 5px; color: white; height: fit-content; }
  .announcement-bar h3 { color: white; margin-top: 0; text-align: center; }
  .form-group { margin-bottom: 15px; }
  .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
  .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
  .form-group button { background: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%; }
  .form-group button:hover { background: #0056b3; }
  .message { margin-bottom: 15px; padding: 10px; border-radius: 5px; text-align: center; }
  .dashboard-container { padding: 30px; }
  form { margin-bottom: 30px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
  select, button { padding: 8px; font-size: 16px; margin-right: 10px; }
  table { border-collapse: collapse; width: 70%; background: white; margin-top: 20px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
  th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
  th { background-color: #eee; }
  .info { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1); width: 60%; }
  .login-link { text-align: center; margin-top: 15px; }
  .login-link a { color: #0066cc; text-decoration: none; }
  </style>
</head>
<body>

<header>
  <img src="emblem.jpg" alt="Emblem" class="emblem" onerror="this.style.display='none'">
  <h1>Board of Technical Education, Pitampura, Delhi 110088</h1>
  <img src="flag.jpg" alt="Flag" class="flag" onerror="this.style.display='none'">
</header>
<hr>

<nav>
  <ul>
    <li><a href="index.php">HOME</a></li>
    <li><a href="about.php">ABOUT</a></li>
    <li><a href="institutes.php">INSTITUTES</a></li>
    <?php if(isset($_SESSION['user_id'])): ?>
      <?php if($_SESSION['role'] == 'faculty'): ?>
        <li><a href="faculty/dashboard.php">FACULTY</a></li>
      <?php elseif($_SESSION['role'] == 'student'): ?>
        <li><a href="student/dashboard.php">STUDENT</a></li>
      <?php endif; ?>
      <li><a href="logout.php">LOGOUT</a></li>
    <?php else: ?>
      <li><a href="login.php">LOGIN</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="main-wrapper">
  <div class="registration-section">
    <?php if(isset($_SESSION['user_id'])): ?>
      <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</h2>
      <div style="text-align: center; padding: 40px;">
        <p>You are logged in as <strong><?php echo ucfirst($_SESSION['role']); ?></strong></p>
        <?php if($_SESSION['role'] == 'faculty'): ?>
          <a href="faculty/dashboard.php" class="btn">Faculty Dashboard</a>
        <?php elseif($_SESSION['role'] == 'student'): ?>
          <a href="student/dashboard.php" class="btn">Student Dashboard</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <h2>User Registration</h2>
      <?php if ($registration_message): ?>
          <div class="message"><?= $registration_message ?></div>
      <?php endif; ?>
      <form method="POST">
          <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" required>
          </div>
          <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" required>
          </div>
          <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" required minlength="6">
          </div>
          <div class="form-group">
              <label>Role</label>
              <select name="role" required>
                  <option value="student">Student</option>
                  <option value="faculty">Faculty</option>
              </select>
          </div>
          <div class="form-group">
              <button type="submit" name="register_submit">Register</button>
          </div>
      </form>
      <div class="login-link">
        Already have an account? <a href="login.php">Login</a> | 
        <a href="register.php">Full Registration</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="announcement-bar">
    <h3>📢 Announcements</h3>
    <p><strong>Welcome!</strong> Register to access the BTE Result System.</p>
    <p>Keep your credentials safe.</p>
  </div>
</div>

<div class="dashboard-container">
  <form method="POST">
      <h3>View Institute Information</h3>
      <select name="inst_info" required>
          <option value="">-- Select Institute --</option>
          <?php foreach ($institutes as $row): ?>
              <option value="<?= $row['Inst_ID'] ?>"><?= htmlspecialchars($row['Inst_Name']) ?></option>
          <?php endforeach; ?>
      </select>
      <button type="submit">Show Info</button>
  </form>

  <?php if ($institute_info): ?>
      <div class="info">
          <h3><?= htmlspecialchars($institute_info['Inst_Name']) ?></h3>
          <p><strong>Principal:</strong> <?= htmlspecialchars($institute_info['Principal'] ?? '-') ?></p>
          <p><strong>Contact:</strong> <?= htmlspecialchars($institute_info['Contact_No1'] ?? '-') ?></p>
      </div>
  <?php endif; ?>

  <form method="POST">
      <h3>View Courses by Institute</h3>
      <select name="institute" required>
          <option value="">-- Select Institute --</option>
          <?php foreach ($institutes as $row): ?>
              <option value="<?= $row['Inst_ID'] ?>"><?= htmlspecialchars($row['Inst_Name']) ?></option>
          <?php endforeach; ?>
      </select>
      <button type="submit">Show Courses</button>
  </form>

  <?php if ($courses && $courses->num_rows > 0): ?>
      <table>
          <tr><th>Program Name</th><th>Seats</th></tr>
          <?php while ($row = $courses->fetch_assoc()): ?>
              <tr>
                  <td><?= htmlspecialchars($row['Prog_Name']) ?></td>
                  <td><?= htmlspecialchars($row['Seats']) ?></td>
              </tr>
          <?php endwhile; ?>
      </table>
  <?php endif; ?>
</div>

</body>
</html>
