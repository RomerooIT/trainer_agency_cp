<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Пожалуйста, введите имя пользователя и пароль.']);
        exit();
    }
    
    if (strlen($username) > 50) {
        echo json_encode(['error' => 'Имя пользователя не должно превышать 50 символов!']);
        exit();
    }
    if (strlen($password) > 50) {
        echo json_encode(['error' => 'Пароль не должен превышать 50 символов!']);
        exit();
    }    

    try {
        $stmt = $conn->prepare("SELECT ID, password, role FROM members WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Неверное имя пользователя или пароль.']);
        } else {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['role'] = $user['role'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Неверное имя пользователя или пароль.']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Произошла непредвиденная ошибка.']);
    } finally {
        $stmt->close();
        $conn->close();
    }
}
?>
