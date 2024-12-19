<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: 403.php");
    exit();
}


if (isset($_GET['ID'])) {
    $user_id = $_GET['ID'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
} else {
    header("Location: manage_clients.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Данные клиентов</h2>
    <p><strong>Номер:</strong> <?php echo $user['ID']; ?></p>
    <p><strong>Имя пользователя:</strong> <?php echo $user['username']; ?></p>
    <p><strong>Роль:</strong> <?php echo $user['role']; ?></p>
    <p><strong>Пол:</strong> <?php echo $user['sex']; ?></p>
    <p><strong>Имя:</strong> <?php echo $user['name']; ?></p>
    <p><strong>Фамилия:</strong> <?php echo $user['surname']; ?></p>
    <p><strong>Дата рождения:</strong> <?php echo $user['date_of_birth']; ?></p>
    <p><strong>Время регистрации:</strong> <?php echo $user['date_of_registration']; ?></p>
    <a href="manage_clients.php" class="btn btn-primary">Вернуться к списку</a>
</div>
</body>
</html>

<?php
ob_end_flush();
?>