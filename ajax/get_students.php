<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$inst_id = isset($_GET['inst_id']) ? intval($_GET['inst_id']) : 0;
$prog_id = isset($_GET['prog_id']) ? intval($_GET['prog_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

if ($inst_id > 0 && $prog_id > 0) {
    $conn = getDBConnection();
    
    // Calculate admission year based on current year and selected year
    $current_year = date('Y');
    $admission_year = $current_year - ($year - 1);
    
    $sql = "SELECT Student_ID, Roll_No, Name, F_name, DOB, Gender, Mobile_No, Email, Admission_Year
            FROM student
            WHERE Inst_ID = ? AND Prog_ID = ?";
    
    // If year is selected, filter by admission year
    if ($year > 0) {
        $sql .= " AND Admission_Year = ?";
    }
    
    $sql .= " ORDER BY Roll_No";
    
    $stmt = $conn->prepare($sql);
    
    if ($year > 0) {
        $stmt->bind_param("iii", $inst_id, $prog_id, $admission_year);
    } else {
        $stmt->bind_param("ii", $inst_id, $prog_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = array();
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    echo json_encode($students);
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]);
}
?>
