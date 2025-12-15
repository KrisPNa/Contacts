<?php
include_once 'config/database.php';
include_once 'includes/admin_check.php';

// Получаем статистику
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admins,
    (SELECT COUNT(*) FROM contacts) as total_contacts,
    (SELECT COUNT(*) FROM categories) as total_categories";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Контакты</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_dashboard.php" style="color: white; text-decoration: none;">Админ-панель</a></h1>
            <nav>
                <a href="admin_users.php" class="btn-add">Пользователи</a>
                <span class="user-info"><?= htmlspecialchars($_SESSION['username'] ?? 'Администратор') ?></span>
                <a href="logout.php" class="btn-cancel">Выйти</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Панель управления</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Всего пользователей</h3>
                <div style="font-size: 32px; font-weight: bold; color: #007bff;"><?= $stats['total_users'] ?></div>
            </div>
            
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Администраторов</h3>
                <div style="font-size: 32px; font-weight: bold; color: #28a745;"><?= $stats['total_admins'] ?></div>
            </div>
            
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Всего контактов</h3>
                <div style="font-size: 32px; font-weight: bold; color: #dc3545;"><?= $stats['total_contacts'] ?></div>
            </div>
            
            <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Категорий</h3>
                <div style="font-size: 32px; font-weight: bold; color: #ffc107;"><?= $stats['total_categories'] ?></div>
            </div>
        </div>
    </div>
</body>
</html>

