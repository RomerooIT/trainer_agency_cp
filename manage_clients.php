<?php
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "У вас нет разрешения на просмотр этой страницы.";
    exit();
}

$query = "SELECT * FROM members";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клиенты агенства</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            color: #343a40;
        }
        .table th, .table td {
            text-align: center;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-container input {
            border-radius: 50px;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h1 class="text-center mb-4">Управление клиентами агенства</h1>

    <div class="search-container">
        <input type="text" id="searchInput" class="form-control" placeholder="Поиск по имени" />
    </div>

    <table id="userTable" class="table table-bordered table-hover table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Номер</th>
            <th>Имя пользователя</th>
            <th>Роль</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $user['ID']; ?></td>
                <td><?= $user['username']; ?></td>
                <td><?= $user['role']; ?></td>
                <td>
                    <a href="view_client.php?ID=<?= $user['ID']; ?>" class="btn btn-primary btn-sm">Просмотр</a>
                    <button class="deleteUserBtn btn btn-danger btn-sm" data-id="<?= $user['ID']; ?>">Удалить</button>
                    <a href="edit_client.php?ID=<?= $user['ID']; ?>" class="btn btn-warning btn-sm">Редактировать</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="add_client.php" class="btn btn-success btn-lg btn-block">Добавить нового клиента</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script>
    $(document).ready(function() {
        $('.deleteUserBtn').on('click', function(e) {
            e.preventDefault();

            var userId = $(this).data('id');

            if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                $.ajax({
                    type: 'GET',
                    url: 'delete_client.php',
                    data: { delete: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                            location.reload();
                        } else {
                            alert(response.error || 'Произошла ошибка.');
                        }
                    },
                    error: function() {
                        alert('Не удалось подключиться к серверу.');
                    }
                });
            }
        });

        $('#searchInput').on('keyup', function() {
            var searchValue = $(this).val().toLowerCase();
            $('#userTable tbody tr').each(function() {
                var username = $(this).find('td').eq(1).text().toLowerCase();
                if (username.indexOf(searchValue) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>

</body>
</html>
