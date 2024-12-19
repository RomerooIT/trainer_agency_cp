<?php

session_start();
include('includes/db.php');

if (!isset($_SESSION['role'])) {
    header("Location: 403.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $training_session_id = intval($_POST['training_session_id']);

    if (!$training_session_id) {
        echo json_encode(['error' => 'Неверный идентификатор тренировки.']);
        exit;
    }

    $query = "DELETE FROM members_training_sessions WHERE user_id = ? AND training_session_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $training_session_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Не удалось отменить запись на тренировку.']);
    }
} else {
    echo json_encode(['error' => 'Неверный метод.']);
}

