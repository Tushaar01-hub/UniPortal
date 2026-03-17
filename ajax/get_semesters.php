<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$prog_id = isset($_GET['prog_id']) ? intval($_GET['prog_id']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($prog_id > 0 && $year > 0) {
    $conn = getDBConnection();
    
    $sql = "SELECT Duration_Type, Total_Sem_Year FROM program WHERE Prog_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $semesters = array();
    
    if ($row = $result->fetch_assoc()) {
        $duration_type = $row['Duration_Type'];
        
        if ($duration_type == 'Semester') {
            // For semester system, each year has 2 semesters
            $sem1 = ($year * 2) - 1;
            $sem2 = $year * 2;
            $semesters = [$sem1, $sem2];
        } else {
            // For annual system, semester = year
            $semesters = [$year];
        }
    }
    
    echo json_encode($semesters);
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]);
}
?>
