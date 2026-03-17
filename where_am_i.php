<!DOCTYPE html>
<html>
<head>
    <title>File Location Check</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; color: #0c5460; margin: 10px 0; }
        h1 { color: #0066cc; }
        code { background: #f4f4f4; padding: 2px 8px; border-radius: 3px; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0066cc; color: white; }
        .exists { color: green; font-weight: bold; }
        .missing { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>📍 BTE System - File Location Checker</h1>
    
    <?php
    // Get current location
    $current_dir = __DIR__;
    $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    echo "<div class='info'>";
    echo "<strong>Current Location:</strong><br>";
    echo "File Path: <code>" . htmlspecialchars($current_dir) . "</code><br>";
    echo "URL: <code>" . htmlspecialchars($current_url) . "</code>";
    echo "</div>";
    
    // Check important files
    $files_to_check = [
        'index.php' => 'Home Page',
        'login.php' => 'Login Page',
        'register.php' => 'Registration Page',
        'institutes.php' => 'Institutes Page',
        'config/database.php' => 'Database Config',
        'includes/header.php' => 'Header Include',
        'includes/footer.php' => 'Footer Include',
        'assets/css/style.css' => 'Stylesheet',
        'faculty/dashboard.php' => 'Faculty Dashboard',
        'student/dashboard.php' => 'Student Dashboard',
        'ajax/get_programs.php' => 'AJAX Scripts'
    ];
    
    echo "<h2>📂 File Check Results</h2>";
    echo "<table>";
    echo "<tr><th>File</th><th>Description</th><th>Status</th></tr>";
    
    $all_exist = true;
    foreach ($files_to_check as $file => $description) {
        $exists = file_exists($current_dir . '/' . $file);
        if (!$exists) $all_exist = false;
        
        echo "<tr>";
        echo "<td><code>$file</code></td>";
        echo "<td>$description</td>";
        echo "<td class='" . ($exists ? 'exists' : 'missing') . "'>";
        echo $exists ? "✓ Found" : "✗ Missing";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Final verdict
    if ($all_exist) {
        echo "<div class='success'>";
        echo "<strong>✓ Perfect!</strong> All files are in the correct location.<br><br>";
        echo "<strong>Next Steps:</strong><br>";
        echo "1. Visit Home: <a href='index.php'>index.php</a><br>";
        echo "2. Register: <a href='register.php'>register.php</a><br>";
        echo "3. Login: <a href='login.php'>login.php</a><br>";
        echo "4. View Institutes: <a href='institutes.php'>institutes.php</a>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>⚠ Files Not in Correct Location!</strong><br><br>";
        echo "<strong>Problem:</strong> This file is not in the same folder as the other PHP files.<br><br>";
        echo "<strong>Solution:</strong><br>";
        echo "1. Find where you put <code>index.php</code>, <code>login.php</code>, etc.<br>";
        echo "2. Make sure THIS file (<code>where_am_i.php</code>) is in the SAME folder<br>";
        echo "3. All files should be in: <code>C:\\xampp1\\htdocs\\bte_system\\</code><br><br>";
        echo "<strong>Current folder structure:</strong><br>";
        echo "<div class='file-list'>";
        $files = scandir($current_dir);
        echo "Files in current directory:<br>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $icon = is_dir($current_dir . '/' . $file) ? '📁' : '📄';
                echo "$icon $file<br>";
            }
        }
        echo "</div>";
        echo "</div>";
    }
    ?>
    
    <h2>🎯 Correct Folder Structure</h2>
    <div class='info'>
        Your files should be organized like this:<br><br>
        <code>
        C:\xampp1\htdocs\bte_system\<br>
        ├── index.php<br>
        ├── login.php<br>
        ├── register.php<br>
        ├── institutes.php<br>
        ├── about.php<br>
        ├── where_am_i.php (this file)<br>
        ├── check_setup.php<br>
        ├── ajax\<br>
        ├── assets\<br>
        ├── config\<br>
        ├── faculty\<br>
        ├── student\<br>
        └── includes\
        </code>
    </div>
    
    <h2>🔧 Quick Fixes</h2>
    <div class='info'>
        <strong>If files are missing:</strong><br>
        1. Make sure you extracted ALL files from the zip<br>
        2. Copy them to: <code>C:\xampp1\htdocs\bte_system\</code><br>
        3. Refresh this page<br><br>
        
        <strong>If you see files but they don't work:</strong><br>
        1. Check database connection in <code>config/database.php</code><br>
        2. Make sure MySQL is running in XAMPP<br>
        3. Import your database SQL file
    </div>
</div>
</body>
</html>
