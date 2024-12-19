<?php
ob_start();
session_start();
include('includes/db.php');
include 'includes/navbar.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header("Location: 403.php");
    exit();
}

$stmt = $conn->prepare('SELECT * FROM disciplines');
$stmt->execute();
$result = $stmt->get_result();
$disciplines = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>disciplines</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table th, table td {
            text-align: center;
            color: black;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-dark">Доступные дисциплины</h2>
    <a href="add_discipline.php" class="btn btn-success mb-3">Добавить новую дисциплину</a>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
        <tr>
            <th>Номер</th>
            <th>Название</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($disciplines as $discipline) { ?>
            <tr>
                <td><?= htmlspecialchars($discipline['ID']) ?></td>
                <td><?= htmlspecialchars($discipline['title']) ?></td>
                <td>
                    <a href="edit_discipline.php?id=<?= $discipline['ID'] ?>" class="btn btn-warning btn-primary">Редактировать</a>
                    <a href="delete_discipline.php?id=<?= $discipline['ID'] ?>" class="btn btn-danger btn-primary" onclick="return confirm('Are you sure?')">Удалить</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
