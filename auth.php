<?php
include_once 'config/database.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'login';
$error = '';
$success = '';

// Обработка регистрации
if ($_POST && $action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (strlen($username) < 3) {
        $error = 'Имя пользователя должно содержать минимум 3 символа';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            // Проверяем, существует ли пользователь
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = 'Пользователь с таким именем или email уже существует';
            } else {
                // Создаем нового пользователя
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Регистрация успешна! Теперь вы можете войти.';
                $action = 'login';
            }
        } catch (Exception $e) {
            $error = 'Ошибка регистрации: ' . $e->getMessage();
        }
    }
}

// Обработка входа
if ($_POST && $action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Введите имя пользователя и пароль';
    } else {
        try {
            $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                // Администраторы перенаправляются в админ-панель
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = 'Неверное имя пользователя или пароль';
            }
        } catch (Exception $e) {
            $error = 'Ошибка входа: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'register' ? 'Регистрация' : 'Вход' ?> - Контакты</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1><?= $action === 'register' ? 'Регистрация' : 'Вход' ?></h1>
            
            <?php if ($error): ?>
                <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert-message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($action === 'register'): ?>
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
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Зарегистрироваться</button>
                        <a href="?action=login" class="btn-cancel">Уже есть аккаунт? Войти</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="username">Имя пользователя или Email *</label>
                        <input type="text" id="username" name="username" required 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль *</label>
                        <input type="password" id="password" name="password" required 
                               autocomplete="current-password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Войти</button>
                        <a href="?action=register" class="btn-cancel">Нет аккаунта? Зарегистрироваться</a>
                    </div>
                </form>
            <?php endif; ?>
            <div style="margin-top: 20px; text-align: center;">
                <a href="admin_login.php" style="color: #007bff; text-decoration: none; font-size: 14px;">Вход для администратора</a>
            </div>
        </div>
    </div>
</body>
</html>

