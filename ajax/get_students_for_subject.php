<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$subj_id = isset($_GET['subj_id']) ? intval($_GET['subj_id']) : 0;
$prog_id = isset($_GET['prog_id']) ? intval($_GET['prog_id']) : 0;

if ($subj_id > 0 && $prog_id > 0) {
    $conn = getDBConnection();
    
    // Get subject details to determine which students to show
    $sql_subject = "SELECT * FROM subject WHERE Subj_ID = ?";
    $stmt = $conn->prepare($sql_subject);
    $stmt->bind_param("i", $subj_id);
    $stmt->execute();
    $subject = $stmt->get_result()->fetch_assoc();
    
    // Get all students in that program
    $sql = "SELECT Student_ID, Roll_No, Name, F_name, Mobile_No, Admission_Year
            FROM student
            WHERE Prog_ID = ?
            ORDER BY Roll_No";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prog_id);
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
