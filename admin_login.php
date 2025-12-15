<?php
include_once 'config/database.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Если администратор уже авторизован, перенаправляем в админ-панель
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_users.php");
    exit;
}

$error = '';

// Обработка входа
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Введите имя пользователя и пароль';
    } else {
        try {
            $sql = "SELECT id, username, email, password, role FROM users WHERE (username = ? OR email = ?) AND role = 'admin'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: admin_users.php");
                exit;
            } else {
                $error = 'Неверное имя пользователя или пароль, либо вы не являетесь администратором';
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
    <title>Вход администратора - Контакты</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Вход администратора</h1>
            
            <?php if ($error): ?>
                <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
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
                    <a href="admin_register.php" class="btn-cancel">Нет аккаунта? Зарегистрироваться</a>
                </div>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="auth.php" style="color: #007bff; text-decoration: none; font-size: 14px;">Вход для обычных пользователей</a>
            </div>
        </div>
    </div>
</body>
</html>

