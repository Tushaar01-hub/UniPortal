<?php
require_once '../config/database.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();
$faculty_id = $_SESSION['reference_id'];

// Get faculty info
$sql_faculty = "SELECT f.*, i.Inst_Name, p.Prog_Name 
                FROM faculty f
                LEFT JOIN institution i ON f.Inst_ID = i.Inst_ID
                LEFT JOIN program p ON f.Prog_ID = p.Prog_ID
                WHERE f.Faculty_ID = ?";
$stmt = $conn->prepare($sql_faculty);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$faculty_info = $stmt->get_result()->fetch_assoc();

// Get assigned subjects
$sql_subjects = "SELECT s.*, p.Prog_Name, p.Total_Sem_Year
                FROM faculty_subject_assignment fsa
                JOIN subject s ON fsa.Subj_ID = s.Subj_ID
                JOIN program p ON s.Prog_ID = p.Prog_ID
                WHERE fsa.Faculty_ID = ?
                ORDER BY s.Semester, s.Subj_Name";
$stmt = $conn->prepare($sql_subjects);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$assigned_subjects = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - BTE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            margin: 0 0 10px 0;
        }
        .faculty-info {
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .dashboard-card h3 {
            color: #0066cc;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .dashboard-card a {
            display: inline-block;
            background: #0066cc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .dashboard-card a:hover {
            background: #0052a3;
        }
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .filter-section h3 {
            color: #0066cc;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-filter {
            background: #0066cc;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-filter:hover {
            background: #0052a3;
        }
        .subjects-list {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .subjects-list h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .subject-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        .subject-item h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .subject-details {
            font-size: 14px;
            color: #666;
        }
        .students-table {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }
        .students-table h3 {
            color: #0066cc;
            margin-top: 0;
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
        table tr:hover {
            background: #f5f5f5;
        }
        .action-btns {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 5px 15px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        .btn-small:hover {
            background: #0052a3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-warning {
            background: #ffc107;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($faculty_info['Name']); ?></h1>
                <p><?php echo htmlspecialchars($faculty_info['Designation']); ?></p>
                
                <div class="faculty-info">
                    <div class="info-item">
                        <label>Institution</label>
                        <span><?php echo htmlspecialchars($faculty_info['Inst_Name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Department</label>
                        <span><?php echo htmlspecialchars($faculty_info['Prog_Name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span><?php echo htmlspecialchars($faculty_info['Email1']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <span><?php echo htmlspecialchars($faculty_info['Mobile_No1']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>📚 My Classes</h3>
                    <p>View and manage your assigned classes and subjects</p>
                    <a href="#classes">View Classes</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>✅ Attendance</h3>
                    <p>Mark and manage student attendance</p>
                    <a href="attendance.php">Mark Attendance</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>📝 Marks Entry</h3>
                    <p>Enter internal and external marks</p>
                    <a href="marks.php">Enter Marks</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>👥 Students</h3>
                    <p>View student lists and details</p>
                    <a href="#students">View Students</a>
                </div>
            </div>
            
            <!-- Assigned Subjects -->
            <div class="subjects-list">
                <h3>Your Assigned Subjects</h3>
                <?php if ($assigned_subjects->num_rows > 0): ?>
                    <?php while($subject = $assigned_subjects->fetch_assoc()): ?>
                        <div class="subject-item">
                            <h4><?php echo htmlspecialchars($subject['Subj_Name']); ?> (<?php echo $subject['Subj_code']; ?>)</h4>
                            <div class="subject-details">
                                <strong>Program:</strong> <?php echo htmlspecialchars($subject['Prog_Name']); ?> | 
                                <strong>Semester:</strong> <?php echo $subject['Semester']; ?> | 
                                <strong>Type:</strong> <?php echo $subject['Subj_Type']; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No subjects assigned yet. Please contact admin.</p>
                <?php endif; ?>
            </div>
            
            <!-- Student Filter Section -->
            <div class="filter-section" id="classes">
                <h3>Select Class to View Students</h3>
                <form id="filterForm">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>Institution</label>
                            <select id="filter_inst" onchange="loadFilterPrograms()">
                                <option value="">-- Select Institution --</option>
                                <?php
                                $result = $conn->query("SELECT DISTINCT i.Inst_ID, i.Inst_Name 
                                                       FROM institution i
                                                       JOIN faculty f ON i.Inst_ID = f.Inst_ID
                                                       WHERE f.Faculty_ID = $faculty_id
                                                       ORDER BY i.Inst_Name");
                                while($row = $result->fetch_assoc()) {
                                    $selected = ($row['Inst_ID'] == $faculty_info['Inst_ID']) ? 'selected' : '';
                                    echo "<option value='{$row['Inst_ID']}' $selected>{$row['Inst_Name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Program/Department</label>
                            <select id="filter_prog" onchange="loadYears()">
                                <option value="">-- Select Program --</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Year</label>
                            <select id="filter_year" onchange="loadSemesters()">
                                <option value="">-- Select Year --</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Semester</label>
                            <select id="filter_semester">
                                <option value="">-- Select Semester --</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-filter" onclick="loadStudents()">View Students</button>
                </form>
            </div>
            
            <!-- Students Table -->
            <div class="students-table" id="studentsSection">
                <h3>Students List</h3>
                <div id="studentsTableContent">
                    <!-- Students will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Load programs based on institution
        function loadFilterPrograms() {
            var instId = document.getElementById('filter_inst').value;
            var progSelect = document.getElementById('filter_prog');
            
            progSelect.innerHTML = '<option value="">-- Loading... --</option>';
            
            if (instId) {
                fetch('../ajax/get_programs.php?inst_id=' + instId + '&faculty_id=<?php echo $faculty_id; ?>')
                    .then(response => response.json())
                    .then(data => {
                        progSelect.innerHTML = '<option value="">-- Select Program --</option>';
                        data.forEach(program => {
                            var selected = program.Prog_ID == <?php echo $faculty_info['Prog_ID']; ?> ? 'selected' : '';
                            progSelect.innerHTML += `<option value="${program.Prog_ID}" ${selected}>${program.Prog_Name}</option>`;
                        });
                        if (data.length > 0) {
                            loadYears();
                        }
                    });
            } else {
                progSelect.innerHTML = '<option value="">-- Select Program --</option>';
            }
        }
        
        // Load years based on program
        function loadYears() {
            var progId = document.getElementById('filter_prog').value;
            var yearSelect = document.getElementById('filter_year');
            
            if (progId) {
                fetch('../ajax/get_program_years.php?prog_id=' + progId)
                    .then(response => response.json())
                    .then(data => {
                        yearSelect.innerHTML = '<option value="">-- Select Year --</option>';
                        for(var i = 1; i <= data.total_years; i++) {
                            yearSelect.innerHTML += `<option value="${i}">Year ${i}</option>`;
                        }
                    });
            } else {
                yearSelect.innerHTML = '<option value="">-- Select Year --</option>';
            }
            
            document.getElementById('filter_semester').innerHTML = '<option value="">-- Select Semester --</option>';
        }
        
        // Load semesters based on year
        function loadSemesters() {
            var progId = document.getElementById('filter_prog').value;
            var year = document.getElementById('filter_year').value;
            var semesterSelect = document.getElementById('filter_semester');
            
            if (progId && year) {
                fetch('../ajax/get_semesters.php?prog_id=' + progId + '&year=' + year)
                    .then(response => response.json())
                    .then(data => {
                        semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
                        data.forEach(semester => {
                            semesterSelect.innerHTML += `<option value="${semester}">Semester ${semester}</option>`;
                        });
                    });
            } else {
                semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
            }
        }
        
        // Load students
        function loadStudents() {
            var instId = document.getElementById('filter_inst').value;
            var progId = document.getElementById('filter_prog').value;
            var year = document.getElementById('filter_year').value;
            var semester = document.getElementById('filter_semester').value;
            
            if (!instId || !progId) {
                alert('Please select Institution and Program');
                return;
            }
            
            var params = `inst_id=${instId}&prog_id=${progId}`;
            if (year) params += `&year=${year}`;
            if (semester) params += `&semester=${semester}`;
            
            fetch('../ajax/get_students.php?' + params)
                .then(response => response.json())
                .then(data => {
                    var table = '<table><thead><tr>';
                    table += '<th>Roll No</th><th>Name</th><th>Father Name</th><th>Gender</th><th>Phone</th><th>Actions</th>';
                    table += '</tr></thead><tbody>';
                    
                    if (data.length > 0) {
                        data.forEach(student => {
                            table += '<tr>';
                            table += `<td>${student.Roll_No}</td>`;
                            table += `<td>${student.Name}</td>`;
                            table += `<td>${student.F_name}</td>`;
                            table += `<td>${student.Gender}</td>`;
                            table += `<td>${student.Mobile_No}</td>`;
                            table += `<td class="action-btns">
                                <a href="view_student.php?id=${student.Student_ID}" class="btn-small">View</a>
                                <a href="marks.php?student_id=${student.Student_ID}" class="btn-small btn-success">Marks</a>
                                <a href="attendance.php?student_id=${student.Student_ID}" class="btn-small btn-warning">Attendance</a>
                            </td>`;
                            table += '</tr>';
                        });
                    } else {
                        table += '<tr><td colspan="6" style="text-align:center;">No students found</td></tr>';
                    }
                    
                    table += '</tbody></table>';
                    
                    document.getElementById('studentsTableContent').innerHTML = table;
                    document.getElementById('studentsSection').style.display = 'block';
                });
        }
        
        // Auto-load on page load if institution is pre-selected
        window.onload = function() {
            var instId = document.getElementById('filter_inst').value;
            if (instId) {
                loadFilterPrograms();
            }
        };
    </script>
</body>
</html>
<?php $conn->close(); ?>
