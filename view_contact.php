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
// Получаем URL для возврата (если администратор пришел из админ-панели)
$return_url = $_GET['return'] ?? null;

// Администратор может просматривать любые контакты
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

<div class="contact-view-page">
    <div class="container">
        <!-- Top Bar -->
        <div class="contact-view-topbar">
        <div class="topbar-actions-row">
    <?php if ($is_admin && $return_url): ?>
        <a href="<?= htmlspecialchars($return_url) ?>" class="text-btn">Назад</a>
    <?php else: ?>
        <a href="<?= $is_admin ? 'admin_users.php' : 'index.php' ?>" class="text-btn">Назад</a>
    <?php endif; ?>
    <a href="edit_contact.php?id=<?= $contact['id'] ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" class="text-btn">Редактировать</a>
    <a href="delete_contact.php?id=<?= $contact['id'] ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" class="text-btn btn-delete" 
       onclick="return confirm('Вы уверены?')">Удалить</a>
</div>
        </div>
        
        <!-- Contact Content -->
        <div class="contact-view-content">
            <!-- Name -->
            <div class="contact-view-name">
                <h1><?= htmlspecialchars($contact['first_name']) ?></h1>
                <h2><?= htmlspecialchars($contact['last_name']) ?></h2>
            </div>
            
            <!-- Phone Section -->
            <?php if ($contact['phone']): ?>
                <div class="contact-view-phone-section">
                    <div class="phone-info">
                    <div class="phone-label">Мобильный</div>
                        <div class="phone-number"><?= htmlspecialchars($contact['phone']) ?></div>
                        
                    </div>
                    
                </div>
            <?php endif; ?>
            
            <!-- Email Section -->
            <?php if ($contact['email']): ?>
                <div class="contact-view-section">
                    <div class="section-info">
                    <div class="section-label">Email</div>
                        <div class="section-value"><?= htmlspecialchars($contact['email']) ?></div>
                        
                    </div>
                    
                </div>
            <?php endif; ?>
            
            <!-- Address Section -->
            <?php if ($contact['address']): ?>
                <div class="contact-view-section">
                    <div class="section-info">
                    <div class="section-label">Адрес</div>
                        <div class="section-value"><?= nl2br(htmlspecialchars($contact['address'])) ?></div>
                        
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Categories Section -->
            <?php if (!empty($contact_categories)): ?>
                <div class="contact-view-section">
                    <div class="section-info">
                        <div class="section-label">Категория</div>
                        <div class="section-value"><?= htmlspecialchars(implode(', ', $contact_categories)) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>  