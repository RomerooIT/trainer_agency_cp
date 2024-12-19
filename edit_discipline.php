<?php
ob_start();
session_start();
include('includes/db.php');
include 'includes/navbar.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header("Location: 403.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $discipline_id = $_POST['id'];
    $title = $_POST['title'];

    $stmt = $conn->prepare('UPDATE disciplines SET title = ? WHERE ID = ?');
    $stmt->bind_param('si', $title, $discipline_id);
    $stmt->execute();
    $stmt->close();

    header('Location: get_all_disciplines.php');
    exit;
}

$discipline_id = $_GET['id'];
$stmt = $conn->prepare('SELECT * FROM disciplines WHERE ID = ?');
$stmt->bind_param('i', $discipline_id);
$stmt->execute();
$result = $stmt->get_result();
$discipline = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit discipline</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Изменить дисциплину</h2>
    <form action="edit_discipline.php" method="POST">
        <input type="hidden" name="id" value="<?= $discipline['ID'] ?>">
        <div class="form-group">
            <label for="title">Название дисциплины:</label>
            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($discipline['title']) ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Обновить дисциплину</button>
    </form>
</div>

<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>-->

</body>
</html>
