
<?php
include_once 'config/database.php';
include_once 'includes/admin_check.php';
include_once 'includes/functions.php';

$stats = getSystemStats();

$error = '';
$success = '';

if (!isset($_GET['user_id'])) {
    header("Location: admin_users.php");
    exit;
}

$target_user_id = (int)$_GET['user_id'];

$user_sql = "SELECT id, username, email FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$target_user_id]);
$target_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$target_user) {
    header("Location: admin_users.php");
    exit;
}

if (isset($_GET['delete']) && $_GET['delete']) {
    $contact_id = (int)$_GET['delete'];
    try {
        $contact = getContactById($contact_id, $target_user_id);
        if ($contact) {
            deleteContact($contact_id, $target_user_id);
            header("Location: admin_user_contacts.php?user_id=" . $target_user_id);
            exit;
        } else {
            $error = 'Контакт не найден';
        }
    } catch (Exception $e) {
        $error = 'Ошибка удаления контакта: ' . $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

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
    <title>Контакты пользователя - Админ-панель</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="admin_users.php">
                        <i class="bi bi-shield-check"></i>Админ-панель
                    </a>
                </h1>
                <nav class="nav">
                    <span class="user-info">
                        <i class="bi bi-person-gear"></i>
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Администратор') ?>
                    </span>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="bi bi-box-arrow-right"></i>Выйти
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- Заголовок -->
            <div class="form-header mb-4">
                <div>
                    <h1 class="h3">Контакты пользователя</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-person"></i> 
                        <?= htmlspecialchars($target_user['username']) ?> • 
                        <i class="bi bi-envelope"></i> 
                        <?= htmlspecialchars($target_user['email']) ?>
                    </p>
                </div>
                <a href="admin_users.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>Назад
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Поиск -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="d-flex flex-wrap gap-3 align-items-end">
                        <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                        <div style="flex: 1; min-width: 250px;">
                            <label for="search" class="form-label">Поиск контактов</label>
                            <div class="d-flex">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Поиск по контактам" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div style="min-width: 200px;">
                            <label for="category" class="form-label">Категория</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                                </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i>Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Таблица контактов -->
            <div class="table-container mb-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($contacts) > 0): ?>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?= $contact['id'] ?></td>
                                    <td><?= htmlspecialchars($contact['first_name']) ?></td>
                                    <td><?= htmlspecialchars($contact['last_name']) ?></td>
                                    <td><?= htmlspecialchars($contact['email'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($contact['phone'] ?: '-') ?></td>
                                    <td>
                                        <a href="admin_user_contacts.php?user_id=<?= $target_user_id ?>&delete=<?= $contact['id'] ?>" 
                                           class="btn btn-secondary btn-sm"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот контакт?')">
                                           <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-journal-x" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Контакты не найдены</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?user_id=<?= $target_user_id ?>&page=1&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                        <a href="?user_id=<?= $target_user_id ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?user_id=<?= $target_user_id ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" 
                           class="page-link <?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?user_id=<?= $target_user_id ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="?user_id=<?= $target_user_id ?>&page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="pagination-info">
                    Страница <?= $page ?> из <?= $total_pages ?> • 
                    <?= $total_contacts ?> контактов
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
