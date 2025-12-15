<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$contact_id = (int)$_GET['id'];
$contact = getContactById($contact_id, $user_id);

if ($contact) {
    // Создаем копию контакта
    $new_contact_data = [
        'first_name' => $contact['first_name'] . ' (копия)',
        'last_name' => $contact['last_name'],
        'email' => $contact['email'],
        'phone' => $contact['phone'],
        'address' => $contact['address'],
        'categories' => $contact['category_ids'] ? explode(',', $contact['category_ids']) : []
    ];
    
    try {
        $new_contact_id = addContact($new_contact_data, $user_id);
        header("Location: edit_contact.php?id=$new_contact_id");
        exit;
    } catch (Exception $e) {
        header("Location: index.php");
        exit;
    }
}

header("Location: index.php");
exit;
?>