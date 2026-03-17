<?php
/**
 * BTE System Diagnostic Tool
 * Run this file to check if everything is set up correctly
 * Access: http://localhost/bte_system/check_setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>BTE System Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #0066cc; }
        .test { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        .test strong { display: block; margin-bottom: 5px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 BTE System Diagnostic Tool</h1>
    <p>This tool checks if your installation is configured correctly.</p>
    <hr>
";

// Test 1: PHP Version
echo "<div class='test " . (version_compare(PHP_VERSION, '7.0.0', '>=') ? "success" : "error") . "'>";
echo "<strong>✓ PHP Version Check</strong>";
echo "Current PHP Version: <code>" . PHP_VERSION . "</code><br>";
echo version_compare(PHP_VERSION, '7.0.0', '>=') ? "PHP version is compatible!" : "Error: PHP 7.0+ required!";
echo "</div>";

// Test 2: Required Extensions
$required_extensions = ['mysqli', 'session', 'json'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

echo "<div class='test " . (empty($missing_extensions) ? "success" : "error") . "'>";
echo "<strong>✓ PHP Extensions Check</strong>";
if (empty($missing_extensions)) {
    echo "All required extensions are loaded!<br>";
    echo "Loaded: " . implode(', ', $required_extensions);
} else {
    echo "Missing extensions: <code>" . implode(', ', $missing_extensions) . "</code>";
}
echo "</div>";

// Test 3: File Structure
$required_files = [
    'index.php',
    'login.php',
    'register.php',
    'config/database.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'faculty/dashboard.php',
    'student/dashboard.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

echo "<div class='test " . (empty($missing_files) ? "success" : "error") . "'>";
echo "<strong>✓ File Structure Check</strong>";
if (empty($missing_files)) {
    echo "All required files exist!";
} else {
    echo "Missing files:<br>";
    foreach ($missing_files as $file) {
        echo "- <code>$file</code><br>";
    }
}
echo "</div>";

// Test 4: Database Connection
echo "<div class='test";
try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    echo " success'>";
    echo "<strong>✓ Database Connection</strong>";
    echo "Successfully connected to database!<br>";
    echo "Database: <code>" . DB_NAME . "</code><br>";
    echo "Host: <code>" . DB_HOST . "</code><br>";
    echo "Port: <code>" . DB_PORT . "</code>";
    $conn->close();
} catch (Exception $e) {
    echo " error'>";
    echo "<strong>✗ Database Connection</strong>";
    echo "Failed to connect to database!<br>";
    echo "Error: <code>" . $e->getMessage() . "</code><br>";
    echo "<br><strong>Fix:</strong> Check your database credentials in <code>config/database.php</code>";
}
echo "</div>";

// Test 5: Database Tables
echo "<div class='test";
try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    
    $required_tables = ['users', 'student', 'faculty', 'institution', 'program', 'subject', 'result', 'attendance'];
    $existing_tables = [];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $existing_tables[] = $table;
        } else {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo " success'>";
        echo "<strong>✓ Database Tables Check</strong>";
        echo "All required tables exist!<br>";
        echo "Tables found: " . count($existing_tables);
    } else {
        echo " warning'>";
        echo "<strong>⚠ Database Tables Check</strong>";
        echo "Some tables are missing:<br>";
        foreach ($missing_tables as $table) {
            echo "- <code>$table</code><br>";
        }
        echo "<br><strong>Fix:</strong> Run <code>database_updates.sql</code> in phpMyAdmin";
    }
    $conn->close();
} catch (Exception $e) {
    echo " error'>";
    echo "<strong>✗ Database Tables Check</strong>";
    echo "Error: <code>" . $e->getMessage() . "</code>";
}
echo "</div>";

// Test 6: Write Permissions
echo "<div class='test";
$writable = is_writable(__DIR__);
echo $writable ? " success'>" : " warning'>";
echo "<strong>" . ($writable ? "✓" : "⚠") . " Directory Permissions</strong>";
echo $writable ? 
    "Directory is writable (good for file uploads)" : 
    "Directory is not writable (may affect file uploads)";
echo "</div>";

// Test 7: Session Support
echo "<div class='test";
session_start();
$_SESSION['test'] = 'working';
$session_works = isset($_SESSION['test']) && $_SESSION['test'] === 'working';
echo $session_works ? " success'>" : " error'>";
echo "<strong>" . ($session_works ? "✓" : "✗") . " Session Support</strong>";
echo $session_works ? 
    "PHP sessions are working correctly!" : 
    "PHP sessions are not working!";
echo "</div>";

// Test 8: .htaccess
echo "<div class='test";
$htaccess_exists = file_exists('.htaccess');
echo $htaccess_exists ? " success'>" : " warning'>";
echo "<strong>" . ($htaccess_exists ? "✓" : "⚠") . " .htaccess File</strong>";
if ($htaccess_exists) {
    echo ".htaccess file exists (prevents directory listing)";
} else {
    echo ".htaccess file not found<br>";
    echo "<strong>Fix:</strong> Create .htaccess file to prevent directory listing";
}
echo "</div>";

// Summary
echo "<hr>";
echo "<div class='test info'>";
echo "<strong>📊 Summary</strong>";
echo "Installation diagnostics complete!<br><br>";
echo "<strong>Next Steps:</strong><br>";
echo "1. If all checks pass: Access <a href='index.php'>Home Page</a><br>";
echo "2. Create an account: <a href='register.php'>Register</a><br>";
echo "3. Login: <a href='login.php'>Login Page</a><br>";
echo "4. View institutes: <a href='institutes.php'>Institutes</a>";
echo "</div>";

echo "</div></body></html>";
?>
