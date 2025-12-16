
<?php
include_once 'config/database.php';
include_once 'includes/admin_check.php';

$stats = getSystemStats();

$error = '';
$success = '';

if (isset($_GET['delete']) && $_GET['delete']) {
    $user_id = (int)$_GET['delete'];
    
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Вы не можете удалить свой собственный аккаунт';
    } else {
        try {
            $pdo->beginTransaction();
            
            $delete_contacts_sql = "DELETE FROM contacts WHERE user_id = ?";
            $delete_contacts_stmt = $pdo->prepare($delete_contacts_sql);
            $delete_contacts_stmt->execute([$user_id]);
            
            $delete_user_sql = "DELETE FROM users WHERE id = ?";
            $delete_user_stmt = $pdo->prepare($delete_user_sql);
            $delete_user_stmt->execute([$user_id]);
            
            $pdo->commit();
            header("Location: admin_users.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка удаления пользователя: ' . $e->getMessage();
        }
    }
}

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

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM contacts WHERE user_id = u.id) as contacts_count
        FROM users u 
        $where_sql
        ORDER BY u.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <!-- Статистика -->
            <div class="admin-stats mb-5">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_contacts'] ?></div>
                    <div class="stat-label">Контактов</div>
                </div>
            </div>
            
            <!-- Заголовок -->
            <div class="form-header mb-4">
                <h1 class="h3">Управление пользователями</h1>
                <div></div>
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
                        <div style="flex: 1; min-width: 300px;">
                            <label for="search" class="form-label">Поиск пользователей</label>
                            <div class="d-flex">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="По имени пользователя или email" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i>Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Таблица пользователей -->
            <div class="table-container mb-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя пользователя</th>
                            <th>Email</th>
                            <th>Контактов</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?= $user['contacts_count'] ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="admin_user_contacts.php?user_id=<?= $user['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                               <i class="bi bi-person-lines-fill"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin_users.php?delete=<?= $user['id'] ?>" 
                                                   class="btn btn-secondary btn-sm"
                                                   onclick="return confirm('Вы уверены, что хотите удалить пользователя <?= htmlspecialchars($user['username']) ?>? Все его контакты также будут удалены.')">
                                                   <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-secondary btn-sm disabled">
                                                    <i class="bi bi-person-check"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Пользователи не найдены</p>
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
                        <a href="?page=1&search=<?= urlencode($search) ?>" class="page-link">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="page-link">
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
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                           class="page-link <?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <span class="page-link disabled">...</span>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="page-link">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>" class="page-link">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="pagination-info">
                    Страница <?= $page ?> из <?= $total_pages ?> • 
                    <?= $total_users ?> пользователей
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
