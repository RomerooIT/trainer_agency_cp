<?php
session_start();
include('includes/db.php');
include('includes/navbar.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM members WHERE ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$query = "SELECT * FROM members_weights WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$index_weights = $stmt->get_result()->fetch_assoc();

$query = "SELECT m.*, um.user_id FROM training_sessions m
          JOIN members_training_sessions um ON m.ID = um.training_session_id
          WHERE um.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$training_sessions = $stmt->get_result();

if (!$index_weights) {
    $query = "INSERT INTO members_weights (user_id, author_weight, date_weight, popularity_weight, discipline_weight) VALUES (?, 5, 5, 5, 5)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $index_weights = [
        'author_weight' => 0,
        'date_weight' => 0,
        'popularity_weight' => 0,
        'discipline_weight' => 0
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_weight = intval($_POST['author_weight']);
    $date_weight = intval($_POST['date_weight']);
    $popularity_weight = intval($_POST['popularity_weight']);
    $discipline_weight = intval($_POST['discipline_weight']);

    $query = "UPDATE members_weights SET author_weight = ?, date_weight = ?, popularity_weight = ?, discipline_weight = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $author_weight, $date_weight, $popularity_weight, $discipline_weight, $user_id);
    $stmt->execute();

    $index_weights = [
        'author_weight' => $author_weight,
        'date_weight' => $date_weight,
        'popularity_weight' => $popularity_weight,
        'discipline_weight' => $discipline_weight
    ];

    echo "<script>alert('Приоритеты успешно обновлены!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #6c757d;
            color: white;
        }
        .section-header {
            margin-top: 40px;
            font-size: 24px;
            color: #007bff;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container mt-5">

    <!-- User profile card -->
    <div class="card">
        <div class="card-header">
            <h1>ДОБРО ПОЖАЛОВАТЬ В ЛИЧНЫЙ КАБИНЕТ!</h1>
        </div>
        <div class="card-body">
            <p>Ваша роль в системе: <strong><?php echo htmlspecialchars($user['role']); ?></strong></p>
        </div>
    </div>

    <!-- Priority settings -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h3>Настройка приоритетов</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="author_weight" class="form-label">Приоритет по тренерам (0-10):</label>
                    <input type="number" class="form-control" id="author_weight" name="author_weight" min="0" max="10" value="<?php echo htmlspecialchars($index_weights['author_weight']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="date_weight" class="form-label">Приоритет по дате (0-10):</label>
                    <input type="number" class="form-control" id="date_weight" name="date_weight" min="0" max="10" value="<?php echo htmlspecialchars($index_weights['date_weight']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="popularity_weight" class="form-label">Приоритет по популярности (0-10):</label>
                    <input type="number" class="form-control" id="popularity_weight" name="popularity_weight" min="0" max="10" value="<?php echo htmlspecialchars($index_weights['popularity_weight']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="discipline_weight" class="form-label">Приоритет по дисциплинам (0-10):</label>
                    <input type="number" class="form-control" id="discipline_weight" name="discipline_weight" min="0" max="10" value="<?php echo htmlspecialchars($index_weights['discipline_weight']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Обновить приоритеты</button>
            </form>
        </div>
    </div>

    <!-- Training sessions list -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h3>Ваши тренировки</h3>
        </div>
        <div class="card-body">
            <?php if ($training_sessions->num_rows > 0): ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Тренировка</th>
                        <th>Действие</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($training_session = $training_sessions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($training_session['title']); ?></td>
                            <td>
                                <button class="btn btn-danger leave-training_session" data-training_session-id="<?php echo $training_session['ID']; ?>">
                                    Выйти
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Не найдено тренировок для вас.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.leave-training_session', function () {
        const training_sessionId = $(this).data('training_session-id');

        if (confirm('Вы уверены, что хотите выйти из этой тренировки?')) {
            $.ajax({
                url: 'leave_training_session.php',
                type: 'POST',
                data: { training_session_id: training_sessionId },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        alert('Вы успешно покинули тренировку!');
                        location.reload();
                    } else {
                        alert(data.error || 'Произошла ошибка. Попробуйте снова.');
                    }
                },
                error: function () {
                    alert('Не удалось подключиться к серверу.');
                }
            });
        }
    });
</script>
</body>
</html>
