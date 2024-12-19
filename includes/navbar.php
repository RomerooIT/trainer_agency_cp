<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Главная</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <!-- <li class="nav-item">
                <a class="nav-link" href="analytics.php">Аналитика</a>
            </li> -->

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="disciplinesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Управление дисциплинами
                        </a>
                        <div class="dropdown-menu" aria-labelledby="disciplinesDropdown">
                            <a class="dropdown-item" href="add_discipline.php">Добавить дисциплину</a>
                            <a class="dropdown-item" href="get_all_disciplines.php">Все дисциплины</a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="manage_clients.php">Управление клиентами</a>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link" href="analytics.php">Аналитика</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gymsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Управление тренажёрными залами
                        </a>
                        <div class="dropdown-menu" aria-labelledby="gymsDropdown">
                            <a class="dropdown-item" href="add_gym.php">Добавить зал</a>
<!--                            <a class="dropdown-item" href="view_gyms.php">View gyms</a>-->
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="training_sessionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Тренировки
                        </a>
                        <div class="dropdown-menu" aria-labelledby="training_sessionsDropdown">
                            <a class="dropdown-item" href="add_training_session.php">Добавить тренировку</a>
                            <a class="dropdown-item" href="get_all_training_sessions.php">Все тренировки</a>
                        </div>
                    </li>

                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'trainer'): ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="training_sessionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Тренировки
                        </a>
                        <div class="dropdown-menu" aria-labelledby="training_sessionsDropdown">
                            <a class="dropdown-item" href="add_training_session.php">Добавить тренировку</a>
                            <a class="dropdown-item" href="get_all_training_sessions.php">Все тренировки</a>
                        </div>
                    </li>

                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="get_all_training_sessions.php">Все тренировки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="personal_cabinet.php">Профиль</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Выйти</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Авторизация</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#registerModal">Регистрация</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Авторизация</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="loginForm">
                    <div class="form-group">
                        <label for="username">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username" maxlength="50" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" maxlength="50" required>
                    </div>
                    <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Регистрация</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

            <form id="registerForm" method="POST" action="../register.php">
    <div class="form-group">
        <label for="register_username">Имя пользователя</label>
        <input type="text" class="form-control" id="register_username" name="username" maxlength="50" required>
        <small id="usernameHelp" class="form-text text-danger" style="display: none;">Пользователь с таким именем уже существует</small>
    </div>
    <div class="form-group">
        <label for="register_password">Пароль</label>
        <input type="password" class="form-control" id="register_password" name="password" maxlength="50" required>
    </div>
    <div class="form-group">
        <label for="confirm_password">Подтвердить пароль</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" maxlength="50" required>
        <small id="passwordHelp" class="form-text text-danger" style="display: none;">Пароли не совпадают</small>
    </div>
    <div id="errorMessageSignUp" class="alert alert-danger" style="display: none;"></div>
    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
</form>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var registerForm = document.getElementById('registerForm');

        var username = document.getElementById('register_username').value;
        var password = document.getElementById('register_password').value;
        var confirmPassword = document.getElementById('confirm_password').value;

        var passwordHelp = document.getElementById('passwordHelp');
        var usernameHelp = document.getElementById('usernameHelp');
        var errorMessage = document.getElementById('errorMessageSignUp');
        // console.log(username)
        // console.log(password)
        // console.log(confirmPassword)
        // console.log(registerForm)


        passwordHelp.style.display = 'none';
        usernameHelp.style.display = 'none';
        errorMessage.style.display = 'none';

        if (password !== confirmPassword) {
            passwordHelp.textContent = "Password do not match";
            passwordHelp.style.display = 'block';
            return;
        }

        var formData = new FormData(registerForm);
        // console.log(formData)

        fetch('../register.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Регистрация прошла успешно!');
                    $('#registerModal').modal('hide');
                    $('.modal-backdrop').remove();
                    registerForm.reset();
                } else {
                    if (data.error.includes('Username')) {
                        usernameHelp.textContent = data.error;
                        usernameHelp.style.display = 'block';
                    } else {
                        errorMessage.textContent = data.error;
                        errorMessage.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = 'Произошла ошибка при подключении к серверу. Пожалуйста, попробуйте еще раз.';
                errorMessage.style.display = 'block';
            });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(loginForm);

            fetch('../login.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        errorMessage.textContent = data.error;
                        errorMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    errorMessage.textContent = 'Произошла непредвиденная ошибка. Пожалуйста, попробуйте еще раз.';
                    errorMessage.style.display = 'block';
                });
        });
    });
</script>


</body>
</html>
