<?php
include_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ADMIN_REGISTRATION_KEY = 'ADMIN_SECRET_KEY_2024';

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $admin_key = trim($_POST['admin_key'] ?? '');
    
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
    } elseif ($admin_key !== $ADMIN_REGISTRATION_KEY) {
        $error = 'Неверный административный ключ';
    } else {
        try {
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = 'Пользователь с таким именем или email уже существует';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([$username, $email, $hashed_password]);
                
                // Убираем сообщение об успехе, просто перенаправляем на страницу входа
                header("Location: admin_login.php");
                exit;
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-icon">
            <i class="bi bi-shield-plus"></i>
        </div>
        <h1 class="auth-title">Регистрация администратора</h1>
        <p class="auth-subtitle">Только для администраторов системы</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Имя пользователя *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           minlength="3" autocomplete="username" placeholder="Введите имя пользователя">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           autocomplete="email" placeholder="email@example.com">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Пароль *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required 
                           minlength="6" autocomplete="new-password" placeholder="Минимум 6 символов">
                </div>
                <div class="form-text">Минимум 6 символов</div>
            </div>
            
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Подтвердите пароль *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required 
                           minlength="6" autocomplete="new-password" placeholder="Повторите пароль">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="admin_key" class="form-label">Административный ключ *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="admin_key" name="admin_key" required 
                           placeholder="Введите секретный ключ для регистрации администратора">
                </div>
                <div class="form-text">Только для регистрации администраторов</div>
            </div>
            
            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Зарегистрироваться как администратор
                </button>
                <a href="admin_login.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Уже есть аккаунт? Войти
                </a>
            </div>
        </form>
        
        <div class="auth-footer">
            <a href="auth.php" class="text-accent">
                <i class="bi bi-person me-1"></i>Регистрация обычных пользователей
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>