<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$subj_id = isset($_GET['subj_id']) ? intval($_GET['subj_id']) : 0;
$exam_session = isset($_GET['exam_session']) ? mysqli_real_escape_string(getDBConnection(), $_GET['exam_session']) : '';

if ($student_id > 0 && $subj_id > 0 && $exam_session) {
    $conn = getDBConnection();
    
    $sql = "SELECT * FROM result WHERE Student_ID = ? AND Subj_ID = ? AND Exam_Session = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $student_id, $subj_id, $exam_session);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $marks = $result->fetch_assoc();
        $marks['found'] = true;
        echo json_encode($marks);
    } else {
        echo json_encode([
            'found' => false,
            'Obt_Int_Th' => 0,
            'Obt_Int_Pr' => 0,
            'Obt_Ext_Th' => 0,
            'Obt_Ext_Pr' => 0
        ]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['found' => false]);
}
?>
