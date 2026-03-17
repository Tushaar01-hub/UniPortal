<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();
$student_id = $_SESSION['reference_id'];

// Get student info
$sql_student = "SELECT s.*, i.Inst_Name, p.Prog_Name, p.Duration_Type, p.Total_Sem_Year
                FROM student s
                LEFT JOIN institution i ON s.Inst_ID = i.Inst_ID
                LEFT JOIN program p ON s.Prog_ID = p.Prog_ID
                WHERE s.Student_ID = ?";
$stmt = $conn->prepare($sql_student);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_info = $stmt->get_result()->fetch_assoc();

// Calculate current year and semester
$current_year = date('Y');
$years_since_admission = $current_year - $student_info['Admission_Year'];
$current_academic_year = $years_since_admission + 1;

// Get subjects for current program
$sql_subjects = "SELECT * FROM subject WHERE Prog_ID = ? ORDER BY Semester, Subj_Name";
$stmt = $conn->prepare($sql_subjects);
$prog_id = $student_info['Prog_ID'];
$stmt->bind_param("i", $prog_id);
$stmt->execute();
$subjects = $stmt->get_result();

// Get results
$sql_results = "SELECT r.*, s.Subj_Name, s.Subj_code 
                FROM result r
                JOIN subject s ON r.Subj_ID = s.Subj_ID
                WHERE r.Student_ID = ?
                ORDER BY r.Exam_Session DESC, s.Semester";
$stmt = $conn->prepare($sql_results);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();

// Get attendance summary
$sql_attendance = "SELECT 
    COUNT(*) as total_classes,
    SUM(CASE WHEN Status = 'Present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN Status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN Status = 'Late' THEN 1 ELSE 0 END) as late_count
    FROM attendance 
    WHERE Student_ID = ?";
$stmt = $conn->prepare($sql_attendance);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance_summary = $stmt->get_result()->fetch_assoc();

$attendance_percentage = 0;
if ($attendance_summary['total_classes'] > 0) {
    $attendance_percentage = round(($attendance_summary['present_count'] / $attendance_summary['total_classes']) * 100, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - BTE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .student-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-item {
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 5px;
        }
        .info-item label {
            display: block;
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .info-item span {
            font-size: 16px;
            font-weight: bold;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #0066cc;
        }
        .stat-card.attendance {
            background: linear-gradient(135deg, #28a745 0%, #20873a 100%);
            color: white;
        }
        .stat-card.attendance .stat-value {
            color: white;
        }
        .section-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .section-card h3 {
            color: #0066cc;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0066cc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #0066cc;
            color: white;
            padding: 12px;
            text-align: left;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .subject-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .subject-name {
            font-weight: bold;
            color: #333;
        }
        .subject-code {
            color: #666;
            font-size: 14px;
        }
        .semester-badge {
            background: #0066cc;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
        }
        .total-marks {
            font-weight: bold;
            color: #28a745;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($student_info['Name']); ?></h1>
                <p>Roll No: <?php echo htmlspecialchars($student_info['Roll_No']); ?></p>
                
                <div class="student-info">
                    <div class="info-item">
                        <label>Institution</label>
                        <span><?php echo htmlspecialchars($student_info['Inst_Name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Program</label>
                        <span><?php echo htmlspecialchars($student_info['Prog_Name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Admission Year</label>
                        <span><?php echo $student_info['Admission_Year']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Current Year</label>
                        <span>Year <?php echo min($current_academic_year, ceil($student_info['Total_Sem_Year'] / 2)); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card attendance">
                    <h3>Attendance</h3>
                    <div class="stat-value"><?php echo $attendance_percentage; ?>%</div>
                    <p style="margin-top: 10px; font-size: 14px;">
                        <?php echo $attendance_summary['present_count']; ?> / <?php echo $attendance_summary['total_classes']; ?> Classes
                    </p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Subjects</h3>
                    <div class="stat-value"><?php echo $subjects->num_rows; ?></div>
                    <p style="margin-top: 10px; font-size: 14px;">Enrolled in program</p>
                </div>
                
                <div class="stat-card">
                    <h3>Results Available</h3>
                    <div class="stat-value"><?php echo $results->num_rows; ?></div>
                    <p style="margin-top: 10px; font-size: 14px;">Examination results</p>
                </div>
            </div>
            
            <!-- Recent Results -->
            <div class="section-card">
                <h3>📊 Examination Results</h3>
                <?php if ($results->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Exam Session</th>
                                <th>Internal Theory</th>
                                <th>Internal Practical</th>
                                <th>External Theory</th>
                                <th>External Practical</th>
                                <th>Total Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($result = $results->fetch_assoc()): 
                                $total = $result['Obt_Int_Th'] + $result['Obt_Int_Pr'] + 
                                        $result['Obt_Ext_Th'] + $result['Obt_Ext_Pr'];
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($result['Subj_Name']); ?></strong><br>
                                        <small><?php echo $result['Subj_code']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($result['Exam_Session']); ?></td>
                                    <td><?php echo $result['Obt_Int_Th']; ?> / 30</td>
                                    <td><?php echo $result['Obt_Int_Pr']; ?> / 20</td>
                                    <td><?php echo $result['Obt_Ext_Th']; ?> / 70</td>
                                    <td><?php echo $result['Obt_Ext_Pr']; ?> / 80</td>
                                    <td class="total-marks"><?php echo $total; ?> / 200</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>No examination results available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Subjects -->
            <div class="section-card">
                <h3>📚 Program Subjects</h3>
                <?php
                $subjects->data_seek(0); // Reset pointer
                if ($subjects->num_rows > 0):
                    $current_semester = 0;
                    while($subject = $subjects->fetch_assoc()):
                        if ($current_semester != $subject['Semester']):
                            if ($current_semester != 0) echo '</div>';
                            $current_semester = $subject['Semester'];
                            echo '<h4 style="margin-top: 20px; margin-bottom: 10px; color: #0066cc;">Semester ' . $current_semester . '</h4>';
                            echo '<div>';
                        endif;
                ?>
                        <div class="subject-item">
                            <div>
                                <div class="subject-name"><?php echo htmlspecialchars($subject['Subj_Name']); ?></div>
                                <div class="subject-code"><?php echo $subject['Subj_code']; ?> - <?php echo $subject['Subj_Type']; ?></div>
                            </div>
                            <span class="semester-badge">Sem <?php echo $subject['Semester']; ?></span>
                        </div>
                <?php
                    endwhile;
                    echo '</div>';
                else:
                ?>
                    <div class="no-data">
                        <p>No subjects found for your program.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Attendance Details -->
            <div class="section-card">
                <h3>✅ Attendance Summary</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 5px;">
                        <div style="font-size: 32px; font-weight: bold; color: #28a745;">
                            <?php echo $attendance_summary['present_count']; ?>
                        </div>
                        <div style="color: #155724;">Present</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8d7da; border-radius: 5px;">
                        <div style="font-size: 32px; font-weight: bold; color: #dc3545;">
                            <?php echo $attendance_summary['absent_count']; ?>
                        </div>
                        <div style="color: #721c24;">Absent</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 5px;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffc107;">
                            <?php echo $attendance_summary['late_count']; ?>
                        </div>
                        <div style="color: #856404;">Late</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #d1ecf1; border-radius: 5px;">
                        <div style="font-size: 32px; font-weight: bold; color: #0c5460;">
                            <?php echo $attendance_summary['total_classes']; ?>
                        </div>
                        <div style="color: #0c5460;">Total Classes</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
