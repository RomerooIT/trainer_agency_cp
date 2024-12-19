<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: 403.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ob_end_clean();
    header('Content-Type: application/json');
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $sex = $_POST['sex'];
    $date_of_birth = $_POST['date_of_birth'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (
        empty($username) ||
        empty($password) ||
        empty($role) ||
        empty($sex) ||
        empty($name) ||
        empty($surname) ||
        empty($date_of_birth)
    ) {
        echo json_encode(['error' => 'All fields are required!']);
        exit();
    }

    if (strlen($username) > 50) {
        echo json_encode(['error' => 'Имя пользователя не должно превышать 50 символов!']);
        exit();
    }
    if (strlen($password) > 255) {
        echo json_encode(['error' => 'Пароль не должен превышать 255 символов!']);
        exit();
    }
    if (strlen($role) > 50) {
        echo json_encode(['error' => 'Роль не должна превышать 50 символов!']);
        exit();
    }
    if (strlen($name) > 50) {
        echo json_encode(['error' => 'Имя не должно превышать 50 символов!']);
        exit();
    }
    if (strlen($surname) > 50) {
        echo json_encode(['error' => 'Фамилия не должна превышать 50 символов!']);
        exit();
    }
    if (strlen($sex) > 10) {
        echo json_encode(['error' => 'Пол не должен превышать 10 символов!']);
        exit();
    }
    
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        echo json_encode(['error' => 'Имя пользователя может содержать только буквы и цифры!']);
        exit();
    }
    
    if (!preg_match('/^[a-zA-Z0-9]+$/', $role)) {
        echo json_encode(['error' => 'Роль может содержать только буквы и цифры!']);
        exit();
    }
    
    if (!in_array($sex, ['male', 'female', 'other'])) {
        echo json_encode(['error' => 'Недопустимое значение для пола!']);
        exit();
    }
    
    $dateOfBirth = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    if (!$dateOfBirth || $dateOfBirth->format('Y-m-d') !== $date_of_birth) {
        echo json_encode(['error' => 'Неверный формат даты для даты рождения!']);
        exit();
    }    

    $currentYear = (int)date('Y');
    $birthYear = (int)$dateOfBirth->format('Y');
    $birthMonth = (int)$dateOfBirth->format('m');
    $birthDay = (int)$dateOfBirth->format('d');
    $currentDate = new DateTime();
    $age = $currentDate->diff($dateOfBirth)->y;

    if ($age < 1) {
        echo json_encode(['error' => 'User must be at least 1 year old to register!']);
        exit();
    }

    if ($birthYear < 1900 || $birthYear > $currentYear) {
        echo json_encode(['error' => 'Год рождения должен быть в диапазоне от 1900 до текущего года!']);
        exit();
    }
    
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
        $stmt = $conn->prepare("INSERT INTO members (username, password, role, name, surname, sex, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $hashed_password, $role, $name, $surname, $sex, $date_of_birth);
    
        $stmt->execute();
        echo json_encode(['success' => 'Пользователь успешно добавлен!']);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            echo json_encode(['error' => 'Имя пользователя уже существует!']);
            exit();
        } else {
            echo json_encode(['error' => 'Произошла непредвиденная ошибка: ' . $e->getMessage()]);
            exit();
        }
    } finally {
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        $conn->close();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Add User</h2>

    <div id="message" class="alert-danger" style="display: none"></div>

    <form id="addUserForm" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" maxlength="50" pattern="[a-zA-Z0-9]+" title="Имя пользователя может содержать только буквы и цифры" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" name="password" maxlength="50" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <input type="text" class="form-control" name="role" maxlength="50" pattern="[a-zA-Z0-9]+" title="Роль может содержать только буквы и цифры" required>
        </div>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" name="name" maxlength="50" required>
        </div>
        <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" class="form-control" name="surname" maxlength="50" required>
        </div>
        <div class="form-group">
            <label for="sex">Sex</label>
            <input type="text" class="form-control" name="sex" maxlength="10" pattern="male|female|other" title="Пол должен быть мужской, женский или другой." required>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of birth</label>
            <input type="date" class="form-control" name="date_of_birth" required>
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="manage_clients.php" class="btn btn-secondary">Cancel</a>
    </form>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function () {
        $('#addUserForm').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: $('#addUserForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.success);
                    } else if (response.error) {
                        alert(response.error);
                    }
                },
                error: function () {
                    let messageDiv = $('#message');
                    messageDiv.removeClass('d-none alert-success').addClass('alert alert-danger');
                    messageDiv.text('An unexpected error occurred!');
                    messageDiv.show();
                }
            });
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>