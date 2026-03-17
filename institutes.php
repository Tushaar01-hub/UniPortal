<?php require_once 'config/database.php'; 

$conn = getDBConnection();

// Get institutes
$institutes_res = $conn->query("SELECT Inst_ID, Inst_Name FROM institution ORDER BY Inst_Name");

// Handle principal info request
$institute_info = null;
if (isset($_POST['inst_info'])) {
    $inst_id = (int) $_POST['inst_info'];
    $sql = "SELECT Inst_Name, Principal, Contact_No1, Contact_No2, Contact_No3 
            FROM institution WHERE Inst_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inst_id);
    $stmt->execute();
    $institute_info = $stmt->get_result()->fetch_assoc();
}

// Handle courses request
$courses = null;
if (isset($_POST['institute'])) {
    $inst_id = (int) $_POST['institute'];
    $sql = "SELECT p.Prog_Name, ip.Seats 
            FROM institution_program ip
            JOIN program p ON ip.Prog_ID = p.Prog_ID
            WHERE ip.Inst_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inst_id);
    $stmt->execute();
    $courses = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Institutes - BTE</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main style="padding: 40px 0;">
        <div class="container">
            <!-- Institute Info Section -->
            <div style="background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h2>View Institute Information</h2>
                <form method="POST" style="margin-bottom: 20px;">
                    <select name="inst_info" style="padding: 10px; margin-right: 10px;">
                        <option value="">-- Select Institute --</option>
                        <?php 
                        $institutes_res->data_seek(0);
                        while($row = $institutes_res->fetch_assoc()): 
                        ?>
                            <option value="<?= $row['Inst_ID'] ?>"><?= $row['Inst_Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 5px;">Show Info</button>
                </form>

                <?php if ($institute_info): ?>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <h3><?= htmlspecialchars($institute_info['Inst_Name']) ?></h3>
                        <p><strong>Principal:</strong> <?= htmlspecialchars($institute_info['Principal'] ?? '-') ?></p>
                        <p><strong>Contact:</strong> 
                            <?= htmlspecialchars($institute_info['Contact_No1'] ?? '-') ?>,
                            <?= htmlspecialchars($institute_info['Contact_No2'] ?? '-') ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Courses Section -->
            <div style="background: white; padding: 30px; border-radius: 10px;">
                <h2>View Courses by Institute</h2>
                <form method="POST">
                    <select name="institute" style="padding: 10px; margin-right: 10px;">
                        <option value="">-- Select Institute --</option>
                        <?php 
                        $institutes_res->data_seek(0);
                        while($row = $institutes_res->fetch_assoc()): 
                        ?>
                            <option value="<?= $row['Inst_ID'] ?>"><?= $row['Inst_Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 5px;">Show Courses</button>
                </form>

                <?php if ($courses && $courses->num_rows > 0): ?>
                    <table style="width: 100%; margin-top: 20px;">
                        <tr style="background: #0066cc; color: white;">
                            <th style="padding: 12px;">Program Name</th>
                            <th style="padding: 12px;">Seats</th>
                        </tr>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><?= $row['Prog_Name'] ?></td>
                                <td style="padding: 12px; border: 1px solid #ddd;"><?= $row['Seats'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>