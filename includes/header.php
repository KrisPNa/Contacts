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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: white; text-decoration: none;">Контакты</a></h1>
            <nav>
    <a href="add_contact.php" class="btn-add">+ Добавить</a>
    <span class="user-info"><?= htmlspecialchars($_SESSION['username'] ?? 'Пользователь') ?></span>
    <a href="logout.php" class="btn-cancel">Выйти</a>
</nav>
        </div>
    </header>

    <div class="container">
        <!-- Сообщения теперь показываются через браузерные уведомления -->