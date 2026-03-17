<?php 
require_once 'config/database.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    // Sanitize inputs
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long!";
    } else {
        // Check if email already exists
        $check_email = "SELECT User_ID FROM users WHERE Email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                $reference_id = null;
                
                if ($role == 'student') {
                    // Get additional student fields
                    $inst_id = mysqli_real_escape_string($conn, $_POST['inst_id']);
                    $prog_id = mysqli_real_escape_string($conn, $_POST['prog_id']);
                    $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
                    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
                    $admission_year = mysqli_real_escape_string($conn, $_POST['admission_year']);
                    $address = mysqli_real_escape_string($conn, $_POST['address']);
                    
                    // Insert into student table
                    $sql_student = "INSERT INTO student (Inst_ID, Prog_ID, Roll_No, Name, F_name, DOB, Gender, Admission_Year, Mobile_No, Email, Address) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql_student);
                    $stmt->bind_param("iisssssssss", $inst_id, $prog_id, $roll_no, $name, $father_name, $dob, $gender, $admission_year, $phone, $email, $address);
                    $stmt->execute();
                    $reference_id = $conn->insert_id;
                    
                } elseif ($role == 'faculty') {
                    // Get additional faculty fields
                    $inst_id = mysqli_real_escape_string($conn, $_POST['inst_id']);
                    $prog_id = mysqli_real_escape_string($conn, $_POST['prog_id']);
                    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
                    $doj = mysqli_real_escape_string($conn, $_POST['doj']);
                    $pay_level = mysqli_real_escape_string($conn, $_POST['pay_level']);
                    $phone2 = mysqli_real_escape_string($conn, $_POST['phone2'] ?? '');
                    $email2 = mysqli_real_escape_string($conn, $_POST['email2'] ?? '');
                    
                    // Insert into faculty table
                    $sql_faculty = "INSERT INTO faculty (Inst_ID, Prog_ID, Name, DOB, Gender, Designation, DOJ, Mobile_No1, Mobile_No2, Email1, Email2, Pay_Level) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql_faculty);
                    $stmt->bind_param("iissssssssss", $inst_id, $prog_id, $name, $dob, $gender, $designation, $doj, $phone, $phone2, $email, $email2, $pay_level);
                    $stmt->execute();
                    $reference_id = $conn->insert_id;
                }
                
                // Insert into users table
                $sql_user = "INSERT INTO users (Email, Password, Role, Reference_ID, Status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($sql_user);
                $stmt->bind_param("sssi", $email, $hashed_password, $role, $reference_id);
                $stmt->execute();
                
                $conn->commit();
                $success_message = "Registration successful! Please wait for admin approval.";
                
                // Clear form
                $_POST = array();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Registration failed: " . $e->getMessage();
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BTE Result System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .registration-form {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .role-specific {
            display: none;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-register {
            background-color: #0066cc;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-register:hover {
            background-color: #0052a3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="registration-form">
                <h2>User Registration</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registrationForm">
                    <!-- Role Selection -->
                    <div class="form-group">
                        <label>Select Your Role *</label>
                        <select name="role" id="role" required onchange="showRoleFields()">
                            <option value="">-- Select Role --</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                        </select>
                    </div>
                    
                    <!-- Common Fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" pattern="[0-9]{10}" required>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth *</label>
                            <input type="date" name="dob" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">-- Select Gender --</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" id="password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    
                    <!-- Student Specific Fields -->
                    <div id="studentFields" class="role-specific">
                        <h3>Student Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Institution *</label>
                                <select name="inst_id" id="student_inst_id" onchange="loadPrograms('student')">
                                    <option value="">-- Select Institution --</option>
                                    <?php
                                    $conn = getDBConnection();
                                    $result = $conn->query("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['Inst_ID']}'>{$row['Inst_Name']}</option>";
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Program *</label>
                                <select name="prog_id" id="student_prog_id">
                                    <option value="">-- Select Program --</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Roll Number *</label>
                                <input type="text" name="roll_no">
                            </div>
                            <div class="form-group">
                                <label>Father's Name *</label>
                                <input type="text" name="father_name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Admission Year *</label>
                            <select name="admission_year">
                                <option value="">-- Select Year --</option>
                                <?php
                                $current_year = date('Y');
                                for($i = $current_year; $i >= $current_year - 5; $i--) {
                                    echo "<option value='$i'>$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Address *</label>
                            <textarea name="address" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <!-- Faculty Specific Fields -->
                    <div id="facultyFields" class="role-specific">
                        <h3>Faculty Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Institution *</label>
                                <select name="inst_id" id="faculty_inst_id" onchange="loadPrograms('faculty')">
                                    <option value="">-- Select Institution --</option>
                                    <?php
                                    $conn = getDBConnection();
                                    $result = $conn->query("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['Inst_ID']}'>{$row['Inst_Name']}</option>";
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Department/Program *</label>
                                <select name="prog_id" id="faculty_prog_id">
                                    <option value="">-- Select Program --</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Designation *</label>
                                <input type="text" name="designation" placeholder="e.g., Assistant Professor">
                            </div>
                            <div class="form-group">
                                <label>Date of Joining *</label>
                                <input type="date" name="doj">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pay Level *</label>
                                <input type="text" name="pay_level" placeholder="e.g., Level-10">
                            </div>
                            <div class="form-group">
                                <label>Alternate Phone</label>
                                <input type="tel" name="phone2" pattern="[0-9]{10}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alternate Email</label>
                            <input type="email" name="email2">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-register">Register</button>
                    </div>
                    
                    <p style="text-align: center; margin-top: 15px;">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        function showRoleFields() {
            var role = document.getElementById('role').value;
            document.getElementById('studentFields').style.display = 'none';
            document.getElementById('facultyFields').style.display = 'none';
            
            if (role === 'student') {
                document.getElementById('studentFields').style.display = 'block';
                // Make student fields required
                document.querySelectorAll('#studentFields input, #studentFields select, #studentFields textarea').forEach(el => {
                    if (el.name !== 'phone2' && el.name !== 'email2') {
                        el.required = true;
                    }
                });
                // Remove faculty field requirements
                document.querySelectorAll('#facultyFields input, #facultyFields select').forEach(el => {
                    el.required = false;
                });
            } else if (role === 'faculty') {
                document.getElementById('facultyFields').style.display = 'block';
                // Make faculty fields required
                document.querySelectorAll('#facultyFields input, #facultyFields select').forEach(el => {
                    if (el.name !== 'phone2' && el.name !== 'email2') {
                        el.required = true;
                    }
                });
                // Remove student field requirements
                document.querySelectorAll('#studentFields input, #studentFields select, #studentFields textarea').forEach(el => {
                    el.required = false;
                });
            }
        }
        
        function loadPrograms(roleType) {
            var instId = document.getElementById(roleType + '_inst_id').value;
            var progSelect = document.getElementById(roleType + '_prog_id');
            
            if (instId) {
                fetch('ajax/get_programs.php?inst_id=' + instId)
                    .then(response => response.json())
                    .then(data => {
                        progSelect.innerHTML = '<option value="">-- Select Program --</option>';
                        data.forEach(program => {
                            progSelect.innerHTML += `<option value="${program.Prog_ID}">${program.Prog_Name}</option>`;
                        });
                    });
            } else {
                progSelect.innerHTML = '<option value="">-- Select Program --</option>';
            }
        }
    </script>
</body>
</html>
