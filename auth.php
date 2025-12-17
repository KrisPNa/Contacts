<?php
include_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'login';
$error = '';
$success = '';

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
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = 'Пользователь с таким именем или email уже существует';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([$username, $email, $hashed_password]);
                
                // Убираем сообщение об успехе, просто показываем форму входа
                $action = 'login';
            }
        } catch (Exception $e) {
            $error = 'Ошибка регистрации: ' . $e->getMessage();
        }
    }
}

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
                
                if ($user['role'] === 'admin') {
                    header("Location: admin_users.php");
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-icon">
            <i class="bi bi-person-circle"></i>
        </div>
        <h1 class="auth-title"><?= $action === 'register' ? 'Регистрация' : 'Вход' ?></h1>
        <p class="auth-subtitle">
            <?= $action === 'register' ? 'Создайте новый аккаунт' : 'Войдите в свой аккаунт' ?>
        </p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'register'): ?>
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
                
                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Подтвердите пароль *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required 
                               minlength="6" autocomplete="new-password" placeholder="Повторите пароль">
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Зарегистрироваться
                    </button>
                    <a href="?action=login" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Уже есть аккаунт? Войти
                    </a>
                </div>
            </form>
        <?php else: ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Имя пользователя или Email *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               autocomplete="username" placeholder="Введите имя пользователя или email">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Пароль *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required 
                               autocomplete="current-password" placeholder="Введите пароль">
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                    </button>
                    <a href="?action=register" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Нет аккаунта? Зарегистрироваться
                    </a>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="auth-footer">
            <a href="admin_login.php" class="text-accent">
                <i class="bi bi-shield-check me-1"></i>Вход для администратора
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>