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
$return_url = $_GET['return'] ?? ($_POST['return_url'] ?? null);

// Администратор может редактировать любые контакты
$contact = getContactById($contact_id, $is_admin ? null : $user_id);

if (!$contact) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    try {
        // Подготавливаем данные
        $contact_data = [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'categories' => $_POST['categories'] ?? []
        ];
        
        // Валидация
        if (empty($contact_data['first_name']) || empty($contact_data['last_name'])) {
            throw new Exception('Имя и фамилия обязательны для заполнения');
        }
        
        if ($contact_data['email'] && !filter_var($contact_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Некорректный формат email');
        }
        
        // Обновляем контакт (администратор может обновлять любые контакты)
        updateContact($contact_id, $contact_data, $is_admin ? null : $user_id);
        
        // Перенаправляем в зависимости от роли и источника
        if ($is_admin && $return_url) {
            header("Location: " . htmlspecialchars($return_url));
        } elseif ($is_admin) {
            header("Location: admin_users.php");
        } else {
            header("Location: index.php");
        }
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = getAllCategories();
$selected_categories = $contact['category_ids'] ? explode(',', $contact['category_ids']) : [];
$error = isset($error) ? $error : '';
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Редактировать контакт</h1>
    
    <?php if ($error): ?>
        <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="contact-form">
        <?php if ($return_url): ?>
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?= htmlspecialchars($contact['first_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?= htmlspecialchars($contact['last_name']) ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($contact['email']) ?>">
                <div class="validation-error" id="email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?= htmlspecialchars($contact['phone']) ?>">
                <div class="validation-error" id="phone-error"></div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="address">Адрес</label>
            <textarea id="address" name="address" rows="2"><?= htmlspecialchars($contact['address']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Категории</label>
            <div class="add-category-section">
                <div class="add-category-input">
                    <input type="text" id="new_category_name" placeholder="Название новой категории" maxlength="50">
                    <button type="button" id="add_category_btn" class="btn-add-category">➕ Добавить категорию</button>
                </div>
            </div>
            <div class="categories-list" id="categories_list">
    <?php foreach ($categories as $category): ?>
        <label class="category-checkbox">
            <input type="checkbox" 
                   id="cat_<?= $category['id'] ?>"
                   name="categories[]" 
                   value="<?= $category['id'] ?>"
                   <?= in_array($category['id'], $selected_categories) ? 'checked' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
        </label>
    <?php endforeach; ?>
</div>
        </div>
        
        <div class="form-meta">
            <p><strong>Создан:</strong> <?= date('d.m.Y H:i', strtotime($contact['created_at'])) ?><?php if ($contact['updated_at'] != $contact['created_at']): ?> | <strong>Обновлен:</strong> <?= date('d.m.Y H:i', strtotime($contact['updated_at'])) ?><?php endif; ?></p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save">Сохранить изменения</button>
            <?php if ($is_admin && $return_url): ?>
                <a href="<?= htmlspecialchars($return_url) ?>" class="btn-cancel">Отмена</a>
            <?php else: ?>
                <a href="index.php" class="btn-cancel">Отмена</a>
            <?php endif; ?>
        </div>
    </form>
</div>