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
                    <a href="logout.php" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-right me-1"></i>Выйти
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Контакты пользователя</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-person me-1"></i> 
                        <?= htmlspecialchars($target_user['username']) ?> • 
                        <i class="bi bi-envelope me-1"></i> 
                        <?= htmlspecialchars($target_user['email']) ?>
                    </p>
                </div>
                <a href="admin_users.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
                </a>
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
                        <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                        <div class="col-md-5">
                            <label for="search" class="form-label">Поиск контактов</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Поиск по контактам" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="category" class="form-label">Категория</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Таблица контактов -->
            <div class="table-responsive card mb-5 p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                                    <td class="fw-semibold"><?= $contact['id'] ?></td>
                                    <td><?= htmlspecialchars($contact['first_name']) ?></td>
                                    <td><?= htmlspecialchars($contact['last_name']) ?></td>
                                    <td><?= htmlspecialchars($contact['email'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($contact['phone'] ?: '-') ?></td>
                                    <td>
                                        <a href="admin_user_contacts.php?user_id=<?= $target_user_id ?>&delete=<?= $contact['id'] ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот контакт?')"
                                           title="Удалить контакт">
                                           <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-journal-x display-4"></i>
                                        <p class="mt-3 fs-5">Контакты не найдены</p>
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
                                <a class="page-link" href="?user_id=<?= $target_user_id ?>&page=1&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?user_id=<?= $target_user_id ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
                                <a class="page-link" href="?user_id=<?= $target_user_id ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
                                <a class="page-link" href="?user_id=<?= $target_user_id ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?user_id=<?= $target_user_id ?>&page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
                    <?= $total_contacts ?> контактов
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>