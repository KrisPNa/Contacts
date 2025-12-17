<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();
$is_admin = isAdmin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$contact_id = (int)$_GET['id'];
$return_url = $_GET['return'] ?? null;

$contact = getContactById($contact_id, $is_admin ? null : $user_id);

if (!$contact) {
    header("Location: index.php");
    exit;
}

$selected_categories = $contact['category_ids'] ? explode(',', $contact['category_ids']) : [];
$categories = getAllCategories();
$contact_categories = [];
foreach ($categories as $cat) {
    if (in_array($cat['id'], $selected_categories)) {
        $contact_categories[] = $cat['name'];
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4 contact-view">
    <!-- Панель действий -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div class="d-flex gap-2">
            <?php if ($is_admin && $return_url): ?>
                <a href="<?= htmlspecialchars($return_url) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
                </a>
            <?php else: ?>
                <a href="<?= $is_admin ? 'admin_users.php' : 'index.php' ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
                </a>
            <?php endif; ?>
            <a href="edit_contact.php?id=<?= $contact['id'] ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" 
               class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Редактировать
            </a>
            <a href="delete_contact.php?id=<?= $contact['id'] ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" 
               class="btn btn-outline-danger"
               onclick="return confirm('Вы уверены, что хотите удалить этот контакт?')">
                <i class="bi bi-trash me-1"></i>Удалить
            </a>
        </div>
    </div>
    
    <!-- Информация о контакте -->
    <div class="card">
        <div class="contact-header">
            <div class="contact-avatar-large">
                <i class="bi bi-person"></i>
            </div>
            <div class="contact-fullname">
                <h1 class="mb-0"><?= htmlspecialchars($contact['first_name']) ?></h1>
                <h2 class="h3 text-muted"><?= htmlspecialchars($contact['last_name']) ?></h2>
            </div>
        </div>
        
        <div class="contact-details">
            <?php if ($contact['phone']): ?>
                <div class="contact-section">
                    <div class="section-label">
                        <i class="bi bi-telephone me-2"></i> Телефон
                    </div>
                    <div class="section-value fs-5">
                        <?= htmlspecialchars($contact['phone']) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($contact['email']): ?>
                <div class="contact-section">
                    <div class="section-label">
                        <i class="bi bi-envelope me-2"></i> Email
                    </div>
                    <div class="section-value fs-5">
                        <?= htmlspecialchars($contact['email']) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($contact['address']): ?>
                <div class="contact-section">
                    <div class="section-label">
                        <i class="bi bi-house-door me-2"></i> Адрес
                    </div>
                    <div class="section-value">
                        <?= nl2br(htmlspecialchars($contact['address'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($contact_categories)): ?>
                <div class="contact-section">
                    <div class="section-label">
                        <i class="bi bi-tags me-2"></i> Категории
                    </div>
                    <div class="section-value">
                        <div class="contact-categories">
                            <?php foreach ($contact_categories as $cat_name): ?>
                                <span class="category-badge"><?= htmlspecialchars($cat_name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
