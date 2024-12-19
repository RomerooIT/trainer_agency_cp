<?php
ob_start();
include 'includes/db.php';
//echo ($_SERVER["REQUEST_METHOD"]);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
////    var_dump($_POST);
//    echo($username);
//    echo($password);

    if (strlen($username) < 3 || strlen($username) > 20 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        echo json_encode(['success' => false, 'error' => 'Имя пользователя должно состоять из 3-20 символов и содержать только буквы и цифры.']);
        exit();
    }

    if (strlen($password) > 50) {
        echo json_encode(['error' => 'Пароль не должен превышать 50 символов!']);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


//    echo($username);
//    echo($hashed_password);
    $stmt = null;

    try {
//        echo($username);
//        echo($password);
//        echo($hashed_password);
        $stmt = $conn->prepare("INSERT INTO members (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        if ($stmt->execute()) {
//            $_SESSION['user_id'] = $conn->insert_id;
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('При регистрации произошла неизвестная ошибка');
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            echo json_encode(['success' => false, 'error' => 'Пользователь с таким именем уже существует']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Произошла непредвиденная ошибка']);
        }
    } finally {
        if ($stmt) {
            $stmt->close();
        }
        $conn->close();
    }
}
ob_end_flush();
?>
