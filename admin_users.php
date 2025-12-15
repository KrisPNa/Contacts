<?php
include_once 'config/database.php';
include_once 'includes/admin_check.php';

$error = '';
$success = '';

// Обработка удаления пользователя
if (isset($_GET['delete']) && $_GET['delete']) {
    $user_id = (int)$_GET['delete'];
    
    // Нельзя удалить самого себя
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Вы не можете удалить свой собственный аккаунт';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Удаляем контакты пользователя
            $delete_contacts_sql = "DELETE FROM contacts WHERE user_id = ?";
            $delete_contacts_stmt = $pdo->prepare($delete_contacts_sql);
            $delete_contacts_stmt->execute([$user_id]);
            
            // Удаляем пользователя
            $delete_user_sql = "DELETE FROM users WHERE id = ?";
            $delete_user_stmt = $pdo->prepare($delete_user_sql);
            $delete_user_stmt->execute([$user_id]);
            
            $pdo->commit();
            $success = 'Пользователь успешно удален';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка удаления пользователя: ' . $e->getMessage();
        }
    }
}

// Получаем список пользователей
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where_conditions = ["role = 'user'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// Получаем пользователей (только обычные пользователи, без администраторов)
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM contacts WHERE user_id = u.id) as contacts_count
        FROM users u 
        $where_sql
        ORDER BY u.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем общее количество
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - Админ-панель</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_users.php" style="color: white; text-decoration: none;">Админ-панель</a></h1>
            <nav>
                
                <span class="user-info"><?= htmlspecialchars($_SESSION['username'] ?? 'Администратор') ?></span>
                <a href="logout.php" class="btn-cancel">Выйти</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Управление пользователями</h1>
        
        <?php if ($error): ?>
            <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Форма поиска -->
        <form method="GET" class="search-form">
            <div class="search-form-row">
                <input type="text" name="search" placeholder="Поиск пользователей (имя, email)" 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
            </div>
        </form>
        
        <!-- Список пользователей -->
        <div style="background: white; padding: 15px; border: 1px solid #ddd; margin-top: 15px;">
            <?php if (count($users) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px; text-align: left;">ID</th>
                            <th style="padding: 10px; text-align: left;">Имя пользователя</th>
                            <th style="padding: 10px; text-align: left;">Email</th>
                            <th style="padding: 10px; text-align: left;">Контактов</th>
                            <th style="padding: 10px; text-align: left;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?= $user['id'] ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($user['username']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($user['email']) ?></td>
                                
                                <td style="padding: 10px;"><?= $user['contacts_count'] ?></td>
                                <td style="padding: 10px;">
                                    <div style="display: flex; gap: 5px; align-items: center;">
                                        <a href="admin_user_contacts.php?user_id=<?= $user['id'] ?>" 
                                           class="btn-save" 
                                           style="text-decoration: none; padding: 4px 8px; font-size: 12px;">
                                           Управлять контактами
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin_users.php?delete=<?= $user['id'] ?>" 
                                               class="btn-delete" 
                                               style="text-decoration: none; padding: 4px 8px; font-size: 12px;"
                                               onclick="return confirm('Вы уверены, что хотите удалить пользователя <?= htmlspecialchars($user['username']) ?>? Все его контакты также будут удалены.')">
                                               Удалить
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;">Вы</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Пользователи не найдены.</p>
            <?php endif; ?>
        </div>
        
        <!-- Пагинация -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1&search=<?= urlencode($search) ?>" class="page-link">« Первая</a>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="page-link">‹ Назад</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="page-link">Вперед ›</a>
                    <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>" class="page-link">Последняя »</a>
                <?php endif; ?>
            </div>
            <p class="pagination-info">Страница <?= $page ?> из <?= $total_pages ?> (всего пользователей: <?= $total_users ?>)</p>
        <?php endif; ?>
        
    
    </div>
</body>
</html>

