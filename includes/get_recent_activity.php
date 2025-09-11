<?php
session_start();
require_once 'dbh.inc.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT timestamp, code_used 
    FROM clock_ins 
    WHERE employee_id = ?
    ORDER BY timestamp DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
?>
<li class="list-group-item d-flex justify-content-between align-items-center">
    <span><?= date('M j, Y H:i', strtotime($row['timestamp'])) ?></span>
    <span class="badge bg-primary"><?= htmlspecialchars($row['code_used']) ?></span>
</li>
<?php endwhile; ?>