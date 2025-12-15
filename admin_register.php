<?php
include_once 'config/database.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

// Обработка регистрации администратора
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $admin_key = trim($_POST['admin_key'] ?? ''); // Секретный ключ для регистрации администратора
    
    if (empty($username) || empty($email) || empty($password) || empty($admin_key)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (strlen($username) < 3) {
        $error = 'Имя пользователя должно содержать минимум 3 символа';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif ($admin_key !== 'admin_secret_2024') { // Секретный ключ для регистрации администратора
        $error = 'Неверный административный ключ';
    } else {
        try {
            // Проверяем, существует ли пользователь
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = 'Пользователь с таким именем или email уже существует';
            } else {
                // Создаем нового администратора
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Регистрация администратора успешна! Теперь вы можете войти.';
            }
        } catch (Exception $e) {
            $error = 'Ошибка регистрации: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация администратора - Контакты</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Регистрация администратора</h1>
            
            <?php if ($error): ?>
                <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert-message success"><?= htmlspecialchars($success) ?></div>
                <div style="margin-top: 15px;">
                    <a href="admin_login.php" class="btn-save">Перейти к входу</a>
                </div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="username">Имя пользователя *</label>
                        <input type="text" id="username" name="username" required 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               minlength="3" autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                               autocomplete="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль *</label>
                        <input type="password" id="password" name="password" required 
                               minlength="6" autocomplete="new-password">
                        <small>Минимум 6 символов</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Подтвердите пароль *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required 
                               minlength="6" autocomplete="new-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_key">Административный ключ *</label>
                        <input type="password" id="admin_key" name="admin_key" required 
                               placeholder="Введите секретный ключ">
                        <small>Секретный ключ для регистрации администратора</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Зарегистрироваться</button>
                        <a href="admin_login.php" class="btn-cancel">Уже есть аккаунт? Войти</a>
                    </div>
                </form>
            <?php endif; ?>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="auth.php" style="color: #007bff; text-decoration: none; font-size: 14px;">Вход для обычных пользователей</a>
            </div>
        </div>
    </div>
</body>
</html>

