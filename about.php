<?php require_once 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BTE Result System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-section {
            max-width: 1000px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .about-section h2 {
            color: #0066cc;
            margin-bottom: 20px;
        }
        .about-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-box {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #0066cc;
        }
        .feature-box h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="about-section">
                <h2>About BTE Result Management System</h2>
                
                <p>The Board of Technical Education (BTE) Result Management System is a comprehensive digital platform designed to streamline the management of student records, examination results, and academic activities across technical institutions in Haryana.</p>
                
                <p>This system provides a unified interface for students, faculty members, and administrative staff to access and manage academic information efficiently and securely.</p>
                
                <h2 style="margin-top: 40px;">Key Features</h2>
                
                <div class="features">
                    <div class="feature-box">
                        <h3>Student Portal</h3>
                        <p>Students can view their results, attendance records, and academic progress through a user-friendly interface.</p>
                    </div>
                    
                    <div class="feature-box">
                        <h3>Faculty Management</h3>
                        <p>Faculty members can manage class schedules, mark attendance, enter examination marks, and track student performance.</p>
                    </div>
                    
                    <div class="feature-box">
                        <h3>Result Processing</h3>
                        <p>Automated result calculation and generation system that ensures accuracy and timely publication of results.</p>
                    </div>
                    
                    <div class="feature-box">
                        <h3>Multi-Institution Support</h3>
                        <p>Supports multiple institutions, programs, and courses under the BTE Haryana umbrella.</p>
                    </div>
                    
                    <div class="feature-box">
                        <h3>Secure Access</h3>
                        <p>Role-based access control ensures that information is accessible only to authorized users.</p>
                    </div>
                    
                    <div class="feature-box">
                        <h3>Real-time Updates</h3>
                        <p>Instant access to the latest information on results, notifications, and announcements.</p>
                    </div>
                </div>
                
                <h2 style="margin-top: 40px;">Our Mission</h2>
                <p>To provide a robust, efficient, and transparent system for managing technical education records and results, thereby facilitating better academic outcomes and administrative efficiency across all BTE-affiliated institutions in Haryana.</p>
                
                <h2 style="margin-top: 40px;">Contact Information</h2>
                <p><strong>Address:</strong> Government Polytechnic Campus, Sector 26, Panchkula Extension, Haryana - 134116</p>
                <p><strong>Phone:</strong> 0172-2993512</p>
                <p><strong>Email:</strong> info@bteharyana.gov.in</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
