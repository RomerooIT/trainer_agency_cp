<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/navbar.php';
include 'rank_training_sessions.php';

if (!isset($_SESSION['role'])) {
    header("Location: 403.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'trainer' || $role === 'admin') {
    $stmt = $conn->prepare('
        SELECT m.*, r.location AS gym_location, r.number AS gym_number, u.username AS creator_name,
            GROUP_CONCAT(s.title) AS disciplines
        FROM training_sessions m
        JOIN members_training_sessions um ON m.ID = um.training_session_id
        JOIN gyms r ON m.gym_id = r.ID
        JOIN members u ON m.creator_id = u.ID
        JOIN training_sessions_disciplines ms ON m.ID = ms.training_session_id
        JOIN disciplines s ON ms.discipline_id = s.ID
        WHERE um.user_id = ?
        GROUP BY m.ID
    ');
    $stmt->bind_param('i', $user_id);
} else {
    $stmt = $conn->prepare('
        SELECT m.*, r.location AS gym_location, r.number AS gym_number, 
            IF(um.user_id IS NOT NULL, 1, 0) AS is_participant, u.username AS creator_name,
            GROUP_CONCAT(s.title) AS disciplines
        FROM training_sessions m
        LEFT JOIN members_training_sessions um ON m.ID = um.training_session_id AND um.user_id = ?
        JOIN gyms r ON m.gym_id = r.ID
        JOIN members u ON m.creator_id = u.ID
        LEFT JOIN training_sessions_disciplines ms ON m.ID = ms.training_session_id
        LEFT JOIN disciplines s ON ms.discipline_id = s.ID
        GROUP BY m.ID
    ');
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$training_sessions = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

$ranked_training_sessions = rank_training_sessions($training_sessions, $user_id, $conn);

if (isset($_GET['action'])) {
    $training_session_id = $_GET['training_session_id'];

    if ($_GET['action'] == 'join') {
        $stmt = $conn->prepare('INSERT INTO members_training_sessions (user_id, training_session_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $user_id, $training_session_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($_GET['action'] == 'leave') {
        $stmt = $conn->prepare('DELETE FROM members_training_sessions WHERE user_id = ? AND training_session_id = ?');
        $stmt->bind_param('ii', $user_id, $training_session_id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тренировки</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-custom-success {
            background-color: #28a745;
            color: white;
        }

        .btn-custom-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-custom-info {
            background-color: #17a2b8;
            color: white;
        }

        .table-custom {
            border: 1px solid #ddd;
        }

        .table-custom th, .table-custom td {
            padding: 10px;
            text-align: center;
        }

        .table-custom th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 text-center">Активные записи на тренировку</h2>

            <table class="table table-custom table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Название</th>
                        <th>Информация</th>
                        <th>Начало</th>
                        <th>Тренажёрный зал</th>
                        <th>Тренер</th>
                        <th>Дисциплина</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranked_training_sessions as $ranked_training_session) { ?>
                        <tr>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['title']) ?></td>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['description']) ?></td>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['start_time']) ?></td>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['gym_location'] . ' - ' . $ranked_training_session['training_session']['gym_number']) ?></td>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['creator_name']) ?></td>
                            <td><?= htmlspecialchars($ranked_training_session['training_session']['disciplines']) ?></td>
                            <td>
                                <?php if ($_SESSION['role'] == 'trainer' || $_SESSION['role'] == 'admin') { ?>
                                    <a href="manage_participants.php?training_session_id=<?= $ranked_training_session['training_session']['ID'] ?>" class="btn btn-custom-info">Посмотреть участников</a>
                                <?php } else { ?>
                                    <?php if ($ranked_training_session['training_session']['is_participant'] == 1) { ?>
                                        <a href="?action=leave&training_session_id=<?= $ranked_training_session['training_session']['ID'] ?>" class="btn btn-custom-danger">Выйти</a>
                                    <?php } else { ?>
                                        <a href="?action=join&training_session_id=<?= $ranked_training_session['training_session']['ID'] ?>" class="btn btn-custom-success">Вступить</a>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>