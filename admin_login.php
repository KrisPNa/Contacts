<?php
include_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_users.php");
    exit;
}

$error = '';

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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-icon">
            <i class="bi bi-shield-check"></i>
        </div>
        <h1 class="auth-title">Вход администратора</h1>
        <p class="auth-subtitle">Доступ только для администраторов системы</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
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
                <a href="admin_register.php" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus me-2"></i>Нет аккаунта? Зарегистрироваться
                </a>
            </div>
        </form>
        
        <div class="auth-footer">
            <a href="auth.php" class="text-accent">
                <i class="bi bi-person me-1"></i>Вход для обычных пользователей
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>