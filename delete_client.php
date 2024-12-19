<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'У вас нет прав для удаления пользователей.']);
    exit();
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $current_user_id = intval($_SESSION['user_id']);

    if ($user_id === $current_user_id) {
        echo json_encode(['error' => 'Вы не можете удалить самого себя!']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM `members` WHERE `ID` = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Пользователь успешно удалён!']);
    } else {
        echo json_encode(['error' => 'Ошибка при удалении пользователя. Пожалуйста, попробуйте снова.']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Неверный ID пользователя.']);
}

$conn->close();
?>
