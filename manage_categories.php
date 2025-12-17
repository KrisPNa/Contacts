<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();
$is_admin = isAdmin();
$error = '';
$success = '';

if ($_POST && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');
    
    if (empty($category_name)) {
        $error = 'Название категории не может быть пустым';
    } else {
        try {
            addCategoryByName($category_name);
            // Убираем сообщение об успехе, просто перезагружаем страницу
            header("Location: manage_categories.php");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    try {
        $check_sql = "SELECT COUNT(*) as count FROM contact_categories WHERE category_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$category_id]);
        $usage = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usage['count'] > 0) {
            $error = 'Категория используется в ' . $usage['count'] . ' контактах. Сначала удалите эти контакты или измените их категории.';
        } else {
            $delete_sql = "DELETE FROM categories WHERE id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([$category_id]);
            // Убираем сообщение об успехе, просто перезагружаем страницу
            header("Location: manage_categories.php");
            exit;
        }
    } catch (Exception $e) {
        $error = 'Ошибка удаления категории: ' . $e->getMessage();
    }
}

$categories = getAllCategories();

$usage_stats = [];
foreach ($categories as $category) {
    $sql = "SELECT COUNT(*) as count FROM contact_categories WHERE category_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category['id']]);
    $usage_stats[$category['id']] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Управление категориями</h1>
        <a href="index.php" class="btn btn-outline-secondary">
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
    
    <!-- Добавление категории -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">
                <i class="bi bi-plus-circle text-accent me-2"></i>Добавить новую категорию
            </h2>
            <form method="POST" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="category_name" class="form-label">Название категории</label>
                    <input type="text" class="form-control" id="category_name" name="category_name" required 
                           placeholder="Введите название категории" maxlength="50">
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_category" value="1" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Добавить категорию
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Список категорий -->
    <div class="card">
        <div class="card-body">
            <h2 class="h4 mb-3">
                <i class="bi bi-tags text-accent me-2"></i>Существующие категории
            </h2>
            
            <?php if (count($categories) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Используется в контактах</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="fw-semibold"><?= $category['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($category['name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="<?= $usage_stats[$category['id']] > 0 ? 'badge bg-primary' : 'text-muted' ?>">
                                            <?= $usage_stats[$category['id']] ?> контактов
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($usage_stats[$category['id']] == 0): ?>
                                            <a href="manage_categories.php?delete=<?= $category['id'] ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Вы уверены, что хотите удалить категорию \"<?= htmlspecialchars(addslashes($category['name'])) ?>\"?')"
                                               title="Удалить категорию">
                                               <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">
                                                <i class="bi bi-lock me-1"></i> Нельзя удалить
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-tags display-4"></i>
                        <p class="mt-3 fs-5">Пока нет категорий. Добавьте первую категорию с помощью формы выше.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
