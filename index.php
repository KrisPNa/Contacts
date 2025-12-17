<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$contacts_data = getContactsWithPagination($search, $category_filter, $limit, $offset, $user_id);
$contacts = $contacts_data['contacts'];
$total_contacts = $contacts_data['total'];
$total_pages = ceil($total_contacts / $limit);

$categories = getAllCategories();
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Мои контакты</h1>
        <div class="text-muted">
            <i class="bi bi-person-lines-fill me-1"></i>
            Всего контактов: <strong><?= $total_contacts ?></strong>
        </div>
    </div>
    
    <!-- Форма поиска -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Поиск контактов</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="По имени, фамилии, email или телефону" 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
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
    
    <!-- Список контактов -->
    <div class="contacts-grid">
        <?php if (count($contacts) > 0): ?>
            <?php foreach ($contacts as $contact): ?>
                <a href="view_contact.php?id=<?= $contact['id'] ?>" class="contact-card hover-lift">
                    <div class="contact-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="contact-name">
                        <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                    </div>
                    
                    <?php if ($contact['email']): ?>
                        <div class="contact-info">
                            <i class="bi bi-envelope"></i>
                            <?= htmlspecialchars($contact['email']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['phone']): ?>
                        <div class="contact-info">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($contact['phone']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['categories']): ?>
                        <div class="contact-categories">
                            <?php 
                            $cat_names = explode(',', $contact['categories']);
                            foreach (array_slice($cat_names, 0, 3) as $cat_name):
                            ?>
                                <span class="category-badge"><?= htmlspecialchars(trim($cat_name)) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($cat_names) > 3): ?>
                                <span class="category-badge">+<?= count($cat_names) - 3 ?> еще</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card text-center" style="grid-column: 1 / -1;">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="bi bi-journal-x text-muted display-1"></i>
                    </div>
                    <h3 class="h4 text-muted mb-3">Контакты не найдены</h3>
                    <?php if ($search || $category_filter): ?>
                        <p class="text-muted mb-4">Попробуйте изменить параметры поиска</p>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Показать все контакты
                        </a>
                    <?php else: ?>
                        <p class="text-muted mb-4">У вас пока нет сохраненных контактов</p>
                        <a href="add_contact.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Добавить первый контакт
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Навигация по страницам">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>">
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
