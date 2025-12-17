
<?php
include_once 'includes/auth_check.php';
session_start();
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
session_destroy();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выход - Контакты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <meta http-equiv="refresh" content="3;url=<?= $is_admin ? 'admin_login.php' : 'auth.php' ?>">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-icon">
            <i class="bi bi-box-arrow-right"></i>
        </div>
        <h1 class="auth-title">Выход из системы</h1>
        <p class="auth-subtitle">Вы успешно вышли из своей учетной записи</p>
        
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i>
            Вы будете перенаправлены на страницу входа через 3 секунды...
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="<?= $is_admin ? 'admin_login.php' : 'auth.php' ?>" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right"></i>Перейти к входу
            </a>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="bi bi-house"></i>На главную
            </a>
        </div>
    </div>
</body>
</html>