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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
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
                    <a href="auth.php" class="btn btn-secondary">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление пользователями</h1>
                <div></div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Поиск -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="search" class="form-label">Поиск пользователей</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="По имени пользователя или email" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Таблица пользователей -->
            <div class="table-responsive card mb-5 p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                                    <td class="fw-semibold"><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $user['contacts_count'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="admin_user_contacts.php?user_id=<?= $user['id'] ?>" 
                                               class="btn btn-outline-primary btn-sm"
                                               title="Просмотреть контакты">
                                               <i class="bi bi-person-lines-fill"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin_users.php?delete=<?= $user['id'] ?>" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('Вы уверены, что хотите удалить пользователя <?= htmlspecialchars(addslashes($user['username'])) ?>? Все его контакты также будут удалены.')"
                                                   title="Удалить пользователя">
                                                   <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-outline-secondary btn-sm disabled">
                                                    <i class="bi bi-person-check"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4"></i>
                                        <p class="mt-3 fs-5">Пользователи не найдены</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Навигация по страницам">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-double-left"></i></span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end < $total_pages): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-double-right"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="text-center text-muted mt-2">
                    Страница <?= $page ?> из <?= $total_pages ?> • 
                    <?= $total_users ?> пользователей
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>