<?php 
require_once 'config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Query to get user
    $sql = "SELECT u.*, 
            CASE 
                WHEN u.Role = 'student' THEN s.Name
                WHEN u.Role = 'faculty' THEN f.Name
                ELSE 'Admin'
            END as Name,
            CASE 
                WHEN u.Role = 'student' THEN s.Inst_ID
                WHEN u.Role = 'faculty' THEN f.Inst_ID
                ELSE NULL
            END as Inst_ID
            FROM users u
            LEFT JOIN student s ON u.Role = 'student' AND u.Reference_ID = s.Student_ID
            LEFT JOIN faculty f ON u.Role = 'faculty' AND u.Reference_ID = f.Faculty_ID
            WHERE u.Email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['Status'] == 'pending') {
            $error_message = "Your account is pending approval. Please contact admin.";
        } elseif ($user['Status'] == 'inactive') {
            $error_message = "Your account has been deactivated. Please contact admin.";
        } elseif (password_verify($password, $user['Password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['reference_id'] = $user['Reference_ID'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['inst_id'] = $user['Inst_ID'];
            
            // Update last login
            $update_login = "UPDATE users SET Last_Login = NOW() WHERE User_ID = ?";
            $stmt_update = $conn->prepare($update_login);
            $stmt_update->bind_param("i", $user['User_ID']);
            $stmt_update->execute();
            
            // Redirect based on role
            if ($user['Role'] == 'student') {
                header("Location: student/dashboard.php");
            } elseif ($user['Role'] == 'faculty') {
                header("Location: faculty/dashboard.php");
            } else {
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            $error_message = "Invalid email or password!";
        }
    } else {
        $error_message = "Invalid email or password!";
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BTE Result System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-container h2 {
            text-align: center;
            color: #0066cc;
            margin-bottom: 30px;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-login {
            background-color: #0066cc;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-login:hover {
            background-color: #0052a3;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .forgot-password {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="login-container">
            <h2>Login to Your Account</h2>
            
            <?php if ($error_message): ?>
                <div class="alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-login">Login</button>
                </div>
                
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
