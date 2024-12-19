<?php
// Начало сессии и подключение необходимых файлов
ob_start();
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

// Проверяем роль пользователя (доступ только для admin и trainer)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header("Location: 403.php");
    exit();
}

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    ob_end_clean();

    // Получение данных из формы
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $gym_id = trim($_POST['gym_id']);
    $discipline_ids = $_POST['discipline_ids'] ?? [];
    $start_time_input = $_POST['start_time'];

    $creator_id = $_SESSION['user_id']; // ID текущего пользователя

    // Проверки данных
    if (empty($title) || empty($description) || empty($gym_id) || empty($discipline_ids) || empty($start_time_input)) {
        echo json_encode(['error' => 'Все поля обязательны для заполнения!']);
        exit();
    }

    if (strlen($title) < 3 || strlen($title) > 100) {
        echo json_encode(['error' => 'Название должно содержать от 3 до 100 символов!']);
        exit();
    }
    if (strlen($description) < 3 || strlen($description) > 100) {
        echo json_encode(['error' => 'Описание должно содержать от 3 до 100 символов!']);
        exit();
    }

    $start_time = DateTime::createFromFormat('Y-m-d\TH:i', $start_time_input);
    if (!$start_time || $start_time->format('Y-m-d\TH:i') !== $start_time_input) {
        echo json_encode(['error' => 'Неверный формат времени начала!']);
        exit();
    }

    $currentDateTime = new DateTime();
    if ($start_time < $currentDateTime) {
        echo json_encode(['error' => 'Время начала не может быть в прошлом!']);
        exit();
    }

    try {
        // Добавление тренировки в таблицу
        $stmt = $conn->prepare('INSERT INTO training_sessions (title, description, gym_id, start_time, creator_id) VALUES(?, ?, ?, ?, ?)');
        $start_time_formatted = $start_time->format('Y-m-d H:i:s');
        $stmt->bind_param('ssisi', $title, $description, $gym_id, $start_time_formatted, $creator_id);
        $stmt->execute();
        $training_session_id = $stmt->insert_id;

        // Связывание тренировки с дисциплинами
        $stmt = $conn->prepare('INSERT INTO training_sessions_disciplines (training_session_id, discipline_id) VALUES (?, ?)');
        foreach ($discipline_ids as $discipline_id) {
            $stmt->bind_param('ii', $training_session_id, $discipline_id);
            $stmt->execute();
        }

        // Добавление создателя в таблицу участников
        $stmt = $conn->prepare('INSERT INTO members_training_sessions (training_session_id, user_id) VALUES(?, ?)');
        $stmt->bind_param('ii', $training_session_id, $creator_id);
        $stmt->execute();

        echo json_encode(['success' => 'Тренировка успешно добавлена!']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Ошибка сохранения тренировки: ' . $e->getMessage()]);
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
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать тренировку</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Создать тренировку</h4>
                </div>
                <div class="card-body">
                    <form id="createtraining_sessionForm" method="POST">
                        <div class="form-group mb-3">
                            <label for="title">Название</label>
                            <input type="text" id="title" name="title" class="form-control" maxlength="100" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description">Описание</label>
                            <textarea id="description" name="description" class="form-control" maxlength="100" rows="3" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="gym_id">Тренажёрный зал</label>
                            <select id="gym_id" name="gym_id" class="form-control" required>
                                <option value="">Выберите зал</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="discipline_ids">Дисциплины</label>
                            <select id="discipline_ids" name="discipline_ids[]" class="form-control" multiple required>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="start_time">Время начала</label>
                            <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
                        </div>
                        <div id="errorMessages" class="text-danger mb-3"></div>
                        <button type="submit" class="btn btn-primary w-100">Создать</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const gymSelect = document.getElementById('gym_id');
    const disciplineSelect = document.getElementById('discipline_ids');
    const errorMessages = document.getElementById('errorMessages');

    // Загрузка данных залов
    fetch('get_gyms.php')
        .then(res => res.json())
        .then(data => {
            if (data.gyms) {
                data.gyms.forEach(gym => {
                    const option = new Option(`${gym.location} - gym ${gym.number}`, gym.ID);
                    gymSelect.add(option);
                });
            }
        }).catch(console.error);

    // Загрузка данных дисциплин
    fetch('get_disciplines.php')
        .then(res => res.json())
        .then(data => {
            if (data.disciplines) {
                data.disciplines.forEach(discipline => {
                    const option = new Option(discipline.title, discipline.ID);
                    disciplineSelect.add(option);
                });
            }
        }).catch(console.error);

    // Обработка формы
    document.getElementById('createtraining_sessionForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        errorMessages.textContent = '';

        fetch('add_training_session.php', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                errorMessages.textContent = data.error;
            } else {
                alert(data.success);
                this.reset();
            }
        }).catch(console.error);
    });
});
</script>
</body>
</html>