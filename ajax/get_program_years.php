<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$prog_id = isset($_GET['prog_id']) ? intval($_GET['prog_id']) : 0;

if ($prog_id > 0) {
    $conn = getDBConnection();
    
    $sql = "SELECT Duration_Type, Total_Sem_Year FROM program WHERE Prog_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $duration_type = $row['Duration_Type'];
        $total_periods = $row['Total_Sem_Year'];
        
        // Calculate years
        if ($duration_type == 'Semester') {
            $total_years = ceil($total_periods / 2);
        } else {
            $total_years = $total_periods;
        }
        
        echo json_encode([
            'duration_type' => $duration_type,
            'total_years' => $total_years,
            'total_periods' => $total_periods
        ]);
    } else {
        echo json_encode(['total_years' => 0]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['total_years' => 0]);
}
?>
