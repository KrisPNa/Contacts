<?php
// Запускаем сессию для сообщений
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система управления контактами</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Ваш кастомный CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="index.php">
                        <i class="bi bi-journal-text"></i>Контакты
                    </a>
                </h1>
                <nav class="nav">
                    <?php if (isset($_SESSION['username'])): ?>
                        <a href="add_contact.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Добавить
                        </a>
                        <span class="user-info">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? 'Пользователь') ?>
                        </span>
                        <a href="logout.php" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-right me-1"></i>Выйти
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- Сообщения -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>