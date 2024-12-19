<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

if ($_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$training_session_id = $_GET['training_session_id'];

$stmt_training_session = $conn->prepare('SELECT * FROM training_sessions WHERE ID = ?');
$stmt_training_session->bind_param('i', $training_session_id);
$stmt_training_session->execute();
$training_session_result = $stmt_training_session->get_result();
$training_session = $training_session_result->fetch_assoc();
$stmt_training_session->close();

$stmt_participants = $conn->prepare('
    SELECT u.ID, u.username FROM members u
    JOIN members_training_sessions um ON u.ID = um.user_id
    WHERE um.training_session_id = ?
');
$stmt_participants->bind_param('i', $training_session_id);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();
$participants = $result_participants->fetch_all(MYSQLI_ASSOC);
$stmt_participants->close();

if (isset($_POST['delete_participant'])) {
    $participant_id = $_POST['participant_id'];

    if ($participant_id == $_SESSION['user_id']) {
        echo "<script>alert('Вы не можете удалить себя, поскольку являетесь создателем встречи.');</script>";
    } else {
        $stmt_delete = $conn->prepare('DELETE FROM members_training_sessions WHERE user_id = ? AND training_session_id = ?');
        $stmt_delete->bind_param('ii', $participant_id, $training_session_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        header('Location: manage_participants.php?training_session_id=' . $training_session_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants - <?= htmlspecialchars($training_session['title']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Управление участниками тренировки "<?= htmlspecialchars($training_session['title']) ?>"</h2>

    <h4>Участники:</h4>
    <ul class="list-group">
        <?php foreach ($participants as $participant) { ?>
            <li class="list-group-item">
                <?= htmlspecialchars($participant['username']) ?>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="delete_participant" class="btn btn-danger btn-sm"
                            value="<?= $participant['ID'] ?>">Удаление</button>
                    <input type="hidden" name="participant_id" value="<?= $participant['ID'] ?>">
                </form>
            </li>
        <?php } ?>
    </ul>

    <a href="get_all_training_sessions.php" class="btn btn-secondary mt-3">Вернуться к тренировкам</a>
</div>

<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>-->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>-->
</body>
</html>
