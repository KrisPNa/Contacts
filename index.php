<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Контактов на страницу
$offset = ($page - 1) * $limit;

// Получаем контакты с пагинацией
$contacts_data = getContactsWithPagination($search, $category_filter, $limit, $offset, $user_id);
$contacts = $contacts_data['contacts'];
$total_contacts = $contacts_data['total'];
$total_pages = ceil($total_contacts / $limit);

$categories = getAllCategories();
?>
<?php include 'includes/header.php'; ?>
    
    <div class="container">
        <!-- Форма поиска -->
        <form method="GET" class="search-form">
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
        
        
        <!-- Информация о результатах -->
        <div class="search-info" id="searchInfo" style="display: none;">
            <span id="searchInfoText"></span>
        </div>
        
        <!-- Список контактов -->
        <div class="contacts-list" id="contactsList">
            <?php if (count($contacts) > 0): ?>
                <?php foreach ($contacts as $contact): ?>
                    <a href="view_contact.php?id=<?= $contact['id'] ?>" class="contact-card-link">
                        <div class="contact-card">
                            <div class="contact-info">
                                <h3><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></h3>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-contacts">
                    <p>Контакты не найдены.</p>
                    <?php if ($search || $category_filter): ?>
                        <p>Попробуйте изменить параметры поиска или <a href="index.php">показать все контакты</a>.</p>
                    <?php else: ?>
                        <p><a href="add_contact.php">Добавьте первый контакт</a> чтобы начать работу.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Пагинация -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">« Первая</a>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">‹ Назад</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">Вперед ›</a>
                    <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" class="page-link">Последняя »</a>
                <?php endif; ?>
            </div>
            <p class="pagination-info">Страница <?= $page ?> из <?= $total_pages ?> (всего контактов: <?= $total_contacts ?>)</p>
        <?php endif; ?>
    </div>
    
