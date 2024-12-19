<?php
ob_start();
session_start();
include 'includes/db.php'; // Подключаем db.php
include 'includes/navbar.php'; 

$disciplineQuery = "SELECT d.title AS discipline, COUNT(tsd.training_session_id) AS training_count
                    FROM disciplines d
                    LEFT JOIN training_sessions_disciplines tsd ON d.ID = tsd.discipline_id
                    GROUP BY d.title;";  

$gymQuery = "SELECT g.location AS gym, COUNT(ts.ID) AS session_count
             FROM gyms g
             LEFT JOIN training_sessions ts ON g.id = ts.gym_id
             GROUP BY g.location
             ORDER BY session_count DESC;";

$totalClientsQuery = "SELECT COUNT(*) AS total_clients FROM members";

$activeClientsQuery = "SELECT COUNT(DISTINCT m.id) AS active_clients
                       FROM members m
                       JOIN members_training_sessions mts ON m.id = mts.user_id";

$disciplines = $conn->query($disciplineQuery)->fetch_all(MYSQLI_ASSOC);
$gyms = $conn->query($gymQuery)->fetch_all(MYSQLI_ASSOC);

$totalClients = $conn->query($totalClientsQuery)->fetch_assoc()['total_clients'];
$activeClients = $conn->query($activeClientsQuery)->fetch_assoc()['active_clients'];

$percentage = ($totalClients > 0) ? ($activeClients / $totalClients) * 100 : 0;

$selectedParameter = isset($_POST['parameter']) ? $_POST['parameter'] : 'discipline';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-header {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        .card-body {
            background-color: #f8f9fa;
        }
        .list-group-item {
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .badge {
            font-size: 1.2em;
            padding: 0.5em 1em;
        }
        .badge-primary {
            background-color: #007bff;
        }
        .badge-success {
            background-color: #28a745;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-center mb-4">Аналитика тренерского агенства</h1>

    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="parameter">Выберите параметр аналитики:</label>
            <select name="parameter" id="parameter" class="form-control" onchange="this.form.submit()">
                <option value="discipline" <?= $selectedParameter === 'discipline' ? 'selected' : '' ?>>Дисциплины</option>
                <option value="gym" <?= $selectedParameter === 'gym' ? 'selected' : '' ?>>Тренажёрные залы</option>
                <option value="clients" <?= $selectedParameter === 'clients' ? 'selected' : '' ?>>Клиенты</option>
            </select>
        </div>
    </form>

    <?php if ($selectedParameter === 'discipline'): ?>
        <div class="card mb-4">
            <div class="card-header">Распределение учебных занятий по дисциплинам</div>
            <div class="card-body">
                <canvas id="disciplineChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($selectedParameter === 'gym'): ?>
        <div class="card mb-4">
            <div class="card-header">Лучшие тренажёрные залы по количеству тренировок</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($gyms as $gym): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><?= htmlspecialchars($gym['gym']) ?></strong>
                            <span class="badge bg-primary rounded-pill"><?= $gym['session_count'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($selectedParameter === 'clients'): ?>
        <div class="card mb-4">
            <div class="card-header">Процент клиентов, записанных на тренировки</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?= round($percentage, 2) ?>% клиентов</h4>
                    <span class="badge bg-success rounded-pill"><?= $activeClients ?> из <?= $totalClients ?></span>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar" role="progressbar" style="width: <?= round($percentage, 2) ?>%" aria-valuenow="<?= round($percentage, 2) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<script>

    <?php if ($selectedParameter === 'discipline'): ?>
        const disciplineData = <?= json_encode(array_column($disciplines, 'training_count')) ?>;
        const disciplineLabels = <?= json_encode(array_column($disciplines, 'discipline')) ?>;

        const ctx = document.getElementById('disciplineChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: disciplineLabels,
                datasets: [{
                    label: 'Training Sessions',
                    data: disciplineData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    <?php endif; ?>
</script>

</body>
</html>