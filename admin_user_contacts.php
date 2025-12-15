<?php
include_once 'config/database.php';
include_once 'includes/admin_check.php';
include_once 'includes/functions.php';

$error = '';
$success = '';

// Получаем ID пользователя
if (!isset($_GET['user_id'])) {
    header("Location: admin_users.php");
    exit;
}

$target_user_id = (int)$_GET['user_id'];

// Получаем информацию о пользователе
$user_sql = "SELECT id, username, email FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$target_user_id]);
$target_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$target_user) {
    header("Location: admin_users.php");
    exit;
}

// Обработка удаления контакта
if (isset($_GET['delete']) && $_GET['delete']) {
    $contact_id = (int)$_GET['delete'];
    try {
        // Проверяем, что контакт принадлежит этому пользователю
        $contact = getContactById($contact_id, $target_user_id);
        if ($contact) {
            deleteContact($contact_id, $target_user_id);
            $success = 'Контакт успешно удален';
        } else {
            $error = 'Контакт не найден';
        }
    } catch (Exception $e) {
        $error = 'Ошибка удаления контакта: ' . $e->getMessage();
    }
}

// Получаем контакты пользователя
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Используем функцию с конкретным user_id для получения контактов пользователя
$contacts_data = getContactsWithPagination($search, $category_filter, $limit, $offset, $target_user_id);
$contacts = $contacts_data['contacts'];
$total_contacts = $contacts_data['total'];
$total_pages = ceil($total_contacts / $limit);

$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты пользователя <?= htmlspecialchars($target_user['username']) ?> - Админ-панель</title>
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
        <h1>Контакты пользователя: <?= htmlspecialchars($target_user['username']) ?></h1>
        <p style="color: #666; margin-bottom: 15px;">Email: <?= htmlspecialchars($target_user['email']) ?></p>
        
        <?php if ($error): ?>
            <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Форма поиска -->
        <form method="GET" class="search-form">
            <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
            <div class="search-form-row">
                <input type="text" name="search" placeholder="Поиск контактов" 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                <select name="category" class="search-category-select">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        
        <!-- Список контактов -->
        <div style="background: white; padding: 15px; border: 1px solid #ddd; margin-top: 15px;">
            <?php if (count($contacts) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; background: white;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px; text-align: left; font-weight: bold;">ID</th>
                            <th style="padding: 10px; text-align: left; font-weight: bold;">Имя</th>
                            <th style="padding: 10px; text-align: left; font-weight: bold;">Фамилия</th>
                            <th style="padding: 10px; text-align: left; font-weight: bold;">Email</th>
                            <th style="padding: 10px; text-align: left; font-weight: bold;">Телефон</th>
                            <th style="padding: 10px; text-align: left; font-weight: bold;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?= $contact['id'] ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($contact['first_name']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($contact['last_name']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($contact['email'] ?: '-') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($contact['phone'] ?: '-') ?></td>
                                <td style="padding: 10px;">
                                    <a href="admin_user_contacts.php?user_id=<?= $target_user_id ?>&delete=<?= $contact['id'] ?>" 
                                       class="btn-delete" 
                                       style="text-decoration: none; padding: 4px 8px; font-size: 12px;"
                                       onclick="return confirm('Вы уверены, что хотите удалить контакт <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>?')">
                                       Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Контакты не найдены.</p>
            <?php endif; ?>
        </div>
        
        <!-- Пагинация -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?user_id=<?= $target_user_id ?>&page=1&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">« Первая</a>
                    <a href="?user_id=<?= $target_user_id ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">‹ Назад</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?user_id=<?= $target_user_id ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?user_id=<?= $target_user_id ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">Вперед ›</a>
                    <a href="?user_id=<?= $target_user_id ?>&page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">Последняя »</a>
                <?php endif; ?>
            </div>
            <p class="pagination-info">Страница <?= $page ?> из <?= $total_pages ?> (всего контактов: <?= $total_contacts ?>)</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="admin_users.php" class="btn-cancel">Назад к списку пользователей</a>
        </div>
    </div>
</body>
</html>

