<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$inst_id = isset($_GET['inst_id']) ? intval($_GET['inst_id']) : 0;
$faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

if ($inst_id > 0) {
    $conn = getDBConnection();
    
    $sql = "SELECT DISTINCT p.Prog_ID, p.Prog_Name 
            FROM institution_program ip
            JOIN program p ON ip.Prog_ID = p.Prog_ID
            WHERE ip.Inst_ID = ?
            ORDER BY p.Prog_Name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inst_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = array();
    while($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    echo json_encode($programs);
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]);
}
?>
