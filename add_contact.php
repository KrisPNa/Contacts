
<?php
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

$user_id = getCurrentUserId();
$is_admin = isAdmin();
$error = '';

if ($_POST) {
    try {
        $contact_data = [
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'categories' => $_POST['categories'] ?? []
        ];
        
        if (!empty(trim($_POST['new_category']))) {
            $new_category_name = trim($_POST['new_category']);
            try {
                $new_category_id = addCategoryByName($new_category_name);
                $contact_data['categories'][] = $new_category_id;
            } catch (Exception $e) {
                $sql = "SELECT id FROM categories WHERE name = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_category_name]);
                $existing_category = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing_category) {
                    $contact_data['categories'][] = $existing_category['id'];
                }
            }
        }
        
        if (empty($contact_data['first_name']) || empty($contact_data['last_name'])) {
            throw new Exception('Имя и фамилия обязательны для заполнения');
        }
        
        if ($contact_data['email'] && !filter_var($contact_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Некорректный формат email');
        }
        
        $contact_id = addContact($contact_data, $user_id);
        
        // Убираем сообщение, просто перенаправляем
        header("Location: " . ($is_admin ? "admin_users.php" : "index.php"));
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = getAllCategories();
?>
<?php include 'includes/header.php'; ?>

<div class="container form-page">
    <div class="form-header mb-4">
        <h1 class="h3">Добавить новый контакт</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>Назад
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-4">
                    <div class="row">
                        <div class="col">
                            <label for="first_name" class="form-label">
                                <i class="bi bi-person text-accent"></i>
                                Имя *
                            </label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                   placeholder="Введите имя">
                        </div>
                        <div class="col">
                            <label for="last_name" class="form-label">
                                <i class="bi bi-person text-accent"></i>
                                Фамилия *
                            </label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                   placeholder="Введите фамилию">
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="row">
                        <div class="col">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope text-accent"></i>
                                Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="email@example.com">
                            <div class="form-text">Необязательное поле</div>
                        </div>
                        <div class="col">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone text-accent"></i>
                                Телефон *
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                   placeholder="+375 (99) 999-99-99">
                            
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="address" class="form-label">
                        <i class="bi bi-house-door text-accent"></i>
                        Адрес
                    </label>
                    <textarea class="form-control" id="address" name="address" rows="3" 
                              placeholder="Введите адрес"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-tags text-accent"></i>
                        Категории
                    </label>
                    
                    <!-- Новая категория -->
                    <div class="card bg-light-blue border-soft mb-3">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">
                                <i class="bi bi-plus-circle"></i>Добавить новую категорию
                            </h6>
                            <input type="text" class="form-control" id="new_category" name="new_category" 
                                   placeholder="Введите название новой категории" 
                                   value="<?= htmlspecialchars($_POST['new_category'] ?? '') ?>">
                            <div class="form-text">Оставьте пустым, если не нужно создавать новую категорию</div>
                        </div>
                    </div>
                    
                    <!-- Существующие категории -->
                    <div class="card bg-light-blue border-soft">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">
                                <i class="bi bi-list-check"></i>Выберите существующие категории
                            </h6>
                            
                            <?php if (count($categories) > 0): ?>
                                <div class="row">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="categories[]" value="<?= $category['id'] ?>"
                                                       id="cat_<?= $category['id'] ?>"
                                                       <?= (isset($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="cat_<?= $category['id'] ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Пока нет категорий. Создайте первую в поле выше.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i>Сохранить контакт
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i>Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
