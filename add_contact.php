<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();
$is_admin = isAdmin();
$error = '';

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
        
        // Добавляем контакт
        $contact_id = addContact($contact_data, $user_id);
        
        // Перенаправляем в зависимости от роли
        header("Location: " . ($is_admin ? "admin_users.php" : "index.php"));
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = getAllCategories();
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Добавить новый контакт</h1>
    
    <?php if ($error): ?>
        <div class="alert-message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="contact-form">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Фамилия *</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <div class="validation-error" id="email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                <div class="validation-error" id="phone-error"></div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="address">Адрес</label>
            <textarea id="address" name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
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
                        <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>"
                            <?= (isset($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? 'checked' : '' ?>>
                        <span class="checkmark"></span>
                        <?= htmlspecialchars($category['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save">Сохранить контакт</button>
            <a href="index.php" class="btn-cancel">Отмена</a>
        </div>
    </form>
</div>