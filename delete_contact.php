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

// Администратор может удалять любые контакты
$contact = getContactById($contact_id, $is_admin ? null : $user_id);

if (!$contact) {
    header("Location: index.php");
    exit;
}

// Удаляем контакт (администратор может удалять любые контакты)
deleteContact($contact_id, $is_admin ? null : $user_id);

// Перенаправляем в зависимости от роли и источника
if ($is_admin && $return_url) {
    header("Location: " . htmlspecialchars($return_url));
} elseif ($is_admin) {
    header("Location: admin_users.php");
} else {
    header("Location: index.php");
}
exit;
?>