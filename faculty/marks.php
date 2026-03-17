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

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_marks'])) {
    $student_id = intval($_POST['student_id']);
    $subj_id = intval($_POST['subj_id']);
    $exam_session = mysqli_real_escape_string($conn, $_POST['exam_session']);
    $obt_int_th = intval($_POST['obt_int_th']);
    $obt_int_pr = intval($_POST['obt_int_pr']);
    $obt_ext_th = intval($_POST['obt_ext_th']);
    $obt_ext_pr = intval($_POST['obt_ext_pr']);
    
    // Check if record exists
    $check_sql = "SELECT * FROM result WHERE Student_ID = ? AND Subj_ID = ? AND Exam_Session = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iis", $student_id, $subj_id, $exam_session);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $update_sql = "UPDATE result SET Obt_Int_Th = ?, Obt_Int_Pr = ?, Obt_Ext_Th = ?, Obt_Ext_Pr = ?
                      WHERE Student_ID = ? AND Subj_ID = ? AND Exam_Session = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iiiiis", $obt_int_th, $obt_int_pr, $obt_ext_th, $obt_ext_pr, $student_id, $subj_id, $exam_session);
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO result (Student_ID, Subj_ID, Exam_Session, Obt_Int_Th, Obt_Int_Pr, Obt_Ext_Th, Obt_Ext_Pr)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iisiiii", $student_id, $subj_id, $exam_session, $obt_int_th, $obt_int_pr, $obt_ext_th, $obt_ext_pr);
    }
    
    if ($stmt->execute()) {
        $success_message = "Marks saved successfully!";
    } else {
        $error_message = "Error saving marks: " . $conn->error;
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
    <title>Marks Entry - Faculty</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .marks-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .page-header {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .page-header h1 {
            margin: 0;
        }
        .marks-form {
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .marks-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .marks-section {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .marks-section h4 {
            margin-top: 0;
            color: #0066cc;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background: #218838;
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
        }
        .btn-back:hover {
            background: #5a6268;
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
        .student-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .student-info h4 {
            margin-top: 0;
            color: #0066cc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .btn-edit {
            background: #ffc107;
            color: #000;
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="marks-container">
            <div class="page-header">
                <h1>Marks Entry & Management</h1>
                <p>Enter and manage student examination marks</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="marks-form">
                <h3>Select Student & Subject</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Subject</label>
                        <select id="select_subject" onchange="loadStudentsForMarks()">
                            <option value="">-- Select Subject --</option>
                            <?php while($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $subject['Subj_ID']; ?>" 
                                        data-prog="<?php echo $subject['Prog_ID']; ?>"
                                        data-semester="<?php echo $subject['Semester']; ?>">
                                    <?php echo htmlspecialchars($subject['Subj_Name'] . ' (' . $subject['Prog_Name'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Student</label>
                        <select id="select_student" onchange="loadExistingMarks()">
                            <option value="">-- Select Student --</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Exam Session</label>
                        <select id="exam_session">
                            <option value="">-- Select Session --</option>
                            <option value="Summer 2026">Summer 2026</option>
                            <option value="Winter 2025">Winter 2025</option>
                            <option value="Summer 2025">Summer 2025</option>
                        </select>
                    </div>
                </div>
                
                <div id="marksEntrySection" style="display:none;">
                    <div class="student-info" id="studentInfo"></div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="student_id" id="student_id">
                        <input type="hidden" name="subj_id" id="subj_id">
                        <input type="hidden" name="exam_session" id="exam_session_hidden">
                        
                        <div class="marks-grid">
                            <div class="marks-section">
                                <h4>Internal Marks</h4>
                                <div class="form-group">
                                    <label>Theory (Max: 30)</label>
                                    <input type="number" name="obt_int_th" id="obt_int_th" min="0" max="30" value="0">
                                </div>
                                <div class="form-group">
                                    <label>Practical (Max: 20)</label>
                                    <input type="number" name="obt_int_pr" id="obt_int_pr" min="0" max="20" value="0">
                                </div>
                            </div>
                            
                            <div class="marks-section">
                                <h4>External Marks</h4>
                                <div class="form-group">
                                    <label>Theory (Max: 70)</label>
                                    <input type="number" name="obt_ext_th" id="obt_ext_th" min="0" max="70" value="0">
                                </div>
                                <div class="form-group">
                                    <label>Practical (Max: 80)</label>
                                    <input type="number" name="obt_ext_pr" id="obt_ext_pr" min="0" max="80" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button type="submit" name="submit_marks" class="btn-submit">Save Marks</button>
                            <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        function loadStudentsForMarks() {
            var subjectSelect = document.getElementById('select_subject');
            var selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            var subjId = subjectSelect.value;
            var progId = selectedOption.getAttribute('data-prog');
            
            if (subjId) {
                fetch('../ajax/get_students_for_subject.php?subj_id=' + subjId + '&prog_id=' + progId)
                    .then(response => response.json())
                    .then(data => {
                        var studentSelect = document.getElementById('select_student');
                        studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
                        data.forEach(student => {
                            studentSelect.innerHTML += `<option value="${student.Student_ID}" 
                                data-name="${student.Name}" 
                                data-roll="${student.Roll_No}">
                                ${student.Roll_No} - ${student.Name}
                            </option>`;
                        });
                    });
            }
        }
        
        function loadExistingMarks() {
            var studentSelect = document.getElementById('select_student');
            var subjId = document.getElementById('select_subject').value;
            var studentId = studentSelect.value;
            var examSession = document.getElementById('exam_session').value;
            
            if (studentId && subjId && examSession) {
                var selectedOption = studentSelect.options[studentSelect.selectedIndex];
                var studentName = selectedOption.getAttribute('data-name');
                var rollNo = selectedOption.getAttribute('data-roll');
                
                document.getElementById('studentInfo').innerHTML = `
                    <h4>Student Details</h4>
                    <p><strong>Roll No:</strong> ${rollNo}</p>
                    <p><strong>Name:</strong> ${studentName}</p>
                `;
                
                document.getElementById('student_id').value = studentId;
                document.getElementById('subj_id').value = subjId;
                document.getElementById('exam_session_hidden').value = examSession;
                
                // Fetch existing marks
                fetch(`../ajax/get_marks.php?student_id=${studentId}&subj_id=${subjId}&exam_session=${examSession}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.found) {
                            document.getElementById('obt_int_th').value = data.Obt_Int_Th;
                            document.getElementById('obt_int_pr').value = data.Obt_Int_Pr;
                            document.getElementById('obt_ext_th').value = data.Obt_Ext_Th;
                            document.getElementById('obt_ext_pr').value = data.Obt_Ext_Pr;
                        } else {
                            document.getElementById('obt_int_th').value = 0;
                            document.getElementById('obt_int_pr').value = 0;
                            document.getElementById('obt_ext_th').value = 0;
                            document.getElementById('obt_ext_pr').value = 0;
                        }
                    });
                
                document.getElementById('marksEntrySection').style.display = 'block';
            }
        }
        
        document.getElementById('exam_session').addEventListener('change', loadExistingMarks);
    </script>
</body>
</html>
<?php $conn->close(); ?>
