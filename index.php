<?php
session_start();
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тренерское агентство</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Добро пожаловать в наше тренерское агентство</h1>
    
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <img src="images/trainer-min.jpg" alt="Тренеры агентства" class="img-fluid rounded shadow-sm">
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Мы поможем найти тренера для любых нужд</h4>
                    <p class="card-text text-center">
                        Наше тренерское агентство специализируется на подборе профессиональных тренеров для различных дисциплин:
                        <ul>
                            <li>Бодибилдинг</li>
                            <li>Пилатес</li>
                            <li>Похудение</li>
                            <li>Тренировки выносливости</li>
                            <li>Функциональные тренировки</li>
                            <li>Йога и многие другие</li>
                        </ul>
                        Мы понимаем, что каждый человек уникален, и потому подбираем тренера, который идеально подойдет именно вам. Наши тренеры — это настоящие профессионалы, готовые помочь вам достичь ваших целей.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
