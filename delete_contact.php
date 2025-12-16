
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

// Если подтверждено удаление
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    deleteContact($contact_id, $is_admin ? null : $user_id);
    
    // Убираем сообщение об успешном удалении
    // Просто перенаправляем
    if ($is_admin && $return_url) {
        header("Location: " . htmlspecialchars($return_url));
    } elseif ($is_admin) {
        header("Location: admin_users.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Показать страницу подтверждения
?>
<?php include 'includes/header.php'; ?>

<div class="container form-page">
    <div class="form-header mb-4">
        <h1 class="h3 text-danger">
            <i class="bi bi-exclamation-triangle"></i> Удаление контакта
        </h1>
        <a href="view_contact.php?id=<?= $contact_id ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>Назад
        </a>
    </div>
    
    <div class="card border-danger">
        <div class="card-body text-center">
            <div class="mb-4">
                <i class="bi bi-trash text-danger" style="font-size: 4rem;"></i>
            </div>
            
            <h2 class="h4 mb-3">Вы уверены, что хотите удалить этот контакт?</h2>
            
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle"></i>
                Это действие нельзя отменить. Все данные контакта будут удалены безвозвратно.
            </div>
            
            <div class="card mb-4">
                <div class="card-body text-left">
                    <h5 class="card-title">Информация о контакте:</h5>
                    <p class="mb-1"><strong>Имя:</strong> <?= htmlspecialchars($contact['first_name']) ?></p>
                    <p class="mb-1"><strong>Фамилия:</strong> <?= htmlspecialchars($contact['last_name']) ?></p>
                    <?php if ($contact['email']): ?>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($contact['email']) ?></p>
                    <?php endif; ?>
                    <?php if ($contact['phone']): ?>
                        <p class="mb-0"><strong>Телефон:</strong> <?= htmlspecialchars($contact['phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="delete_contact.php?id=<?= $contact_id ?>&confirm=yes<?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" 
                   class="btn btn-danger btn-lg">
                   <i class="bi bi-trash"></i> Да, удалить
                </a>
                <a href="view_contact.php?id=<?= $contact_id ?><?= $return_url ? '&return=' . urlencode($return_url) : '' ?>" 
                   class="btn btn-secondary btn-lg">
                   <i class="bi bi-x-circle"></i> Отмена
                </a>
            </div>
        </div>
    </div>
</div>
