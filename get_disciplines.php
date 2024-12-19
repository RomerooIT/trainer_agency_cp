<?php
include 'includes/db.php';

$stmt = $conn->prepare('SELECT ID, title FROM disciplines');
$stmt->execute();
$result = $stmt->get_result();
$disciplines = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['disciplines' => $disciplines]);
?>
