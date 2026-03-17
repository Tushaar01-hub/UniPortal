<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();
$faculty_id = $_SESSION['reference_id'];
$success_message = '';
$error_message = '';

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_attendance'])) {
    $subj_id = intval($_POST['subj_id']);
    $date = mysqli_real_escape_string($conn, $_POST['attendance_date']);
    $student_ids = $_POST['student_ids'];
    $attendance_status = $_POST['attendance_status'];
    
    $conn->begin_transaction();
    
    try {
        foreach ($student_ids as $index => $student_id) {
            $status = $attendance_status[$index];
            $student_id = intval($student_id);
            
            // Check if attendance already exists
            $check_sql = "SELECT Attendance_ID FROM attendance 
                         WHERE Student_ID = ? AND Subj_ID = ? AND Faculty_ID = ? AND Date = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("iiis", $student_id, $subj_id, $faculty_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing
                $update_sql = "UPDATE attendance SET Status = ? 
                              WHERE Student_ID = ? AND Subj_ID = ? AND Faculty_ID = ? AND Date = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("siiis", $status, $student_id, $subj_id, $faculty_id, $date);
                $stmt->execute();
            } else {
                // Insert new
                $insert_sql = "INSERT INTO attendance (Student_ID, Subj_ID, Faculty_ID, Date, Status)
                              VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iiiss", $student_id, $subj_id, $faculty_id, $date, $status);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $success_message = "Attendance marked successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error marking attendance: " . $e->getMessage();
    }
}

// Get faculty subjects
$sql_subjects = "SELECT s.*, p.Prog_Name
                FROM faculty_subject_assignment fsa
                JOIN subject s ON fsa.Subj_ID = s.Subj_ID
                JOIN program p ON s.Prog_ID = p.Prog_ID
                WHERE fsa.Faculty_ID = ?
                ORDER BY s.Subj_Name";
$stmt = $conn->prepare($sql_subjects);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$subjects = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - Faculty</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .attendance-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20873a 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .attendance-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-load {
            background: #0066cc;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .attendance-table {
            display: none;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table th {
            background: #28a745;
            color: white;
            padding: 12px;
            text-align: left;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .attendance-radio {
            display: flex;
            gap: 15px;
        }
        .attendance-radio label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            margin-left: 10px;
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
        .select-all {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .btn-select-all {
            padding: 5px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-all-present {
            background: #28a745;
            color: white;
        }
        .btn-all-absent {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="attendance-container">
            <div class="page-header">
                <h1>📋 Attendance Management</h1>
                <p>Mark and manage student attendance</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="attendance-form">
                <h3>Select Class for Attendance</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Subject</label>
                        <select id="select_subject">
                            <option value="">-- Select Subject --</option>
                            <?php while($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $subject['Subj_ID']; ?>" 
                                        data-prog="<?php echo $subject['Prog_ID']; ?>">
                                    <?php echo htmlspecialchars($subject['Subj_Name'] . ' (' . $subject['Prog_Name'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="attendance_date" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <button type="button" class="btn-load" onclick="loadStudentsForAttendance()">Load Students</button>
                
                <div class="attendance-table" id="attendanceTable">
                    <div class="select-all">
                        <strong>Quick Select:</strong>
                        <button type="button" class="btn-select-all btn-all-present" onclick="markAll('Present')">Mark All Present</button>
                        <button type="button" class="btn-select-all btn-all-absent" onclick="markAll('Absent')">Mark All Absent</button>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="subj_id" id="form_subj_id">
                        <input type="hidden" name="attendance_date" id="form_attendance_date">
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <!-- Students will be loaded here -->
                            </tbody>
                        </table>
                        
                        <button type="submit" name="submit_attendance" class="btn-submit">Submit Attendance</button>
                        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        function loadStudentsForAttendance() {
            var subjectSelect = document.getElementById('select_subject');
            var subjId = subjectSelect.value;
            var progId = subjectSelect.options[subjectSelect.selectedIndex].getAttribute('data-prog');
            var date = document.getElementById('attendance_date').value;
            
            if (!subjId || !date) {
                alert('Please select subject and date');
                return;
            }
            
            document.getElementById('form_subj_id').value = subjId;
            document.getElementById('form_attendance_date').value = date;
            
            fetch('../ajax/get_students_for_subject.php?subj_id=' + subjId + '&prog_id=' + progId)
                .then(response => response.json())
                .then(data => {
                    var tbody = document.getElementById('studentsTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach((student, index) => {
                            var row = `
                                <tr>
                                    <td>${student.Roll_No}</td>
                                    <td>${student.Name}</td>
                                    <td>
                                        <input type="hidden" name="student_ids[]" value="${student.Student_ID}">
                                        <div class="attendance-radio">
                                            <label>
                                                <input type="radio" name="attendance_status[${index}]" value="Present" checked>
                                                Present
                                            </label>
                                            <label>
                                                <input type="radio" name="attendance_status[${index}]" value="Absent">
                                                Absent
                                            </label>
                                            <label>
                                                <input type="radio" name="attendance_status[${index}]" value="Late">
                                                Late
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                        
                        document.getElementById('attendanceTable').style.display = 'block';
                    } else {
                        alert('No students found for this subject');
                    }
                });
        }
        
        function markAll(status) {
            var radios = document.querySelectorAll('input[type="radio"][value="' + status + '"]');
            radios.forEach(radio => {
                radio.checked = true;
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
