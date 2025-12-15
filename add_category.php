<?php
header('Content-Type: application/json');
include_once 'config/database.php';
include_once 'includes/functions.php';
include_once 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $category_name = $input['name'] ?? '';
    
    if (empty(trim($category_name))) {
        echo json_encode(['success' => false, 'message' => 'Название категории не может быть пустым']);
        exit;
    }
    
    try {
        $category_id = addCategory($category_name);
        $category = ['id' => $category_id, 'name' => trim($category_name)];
        echo json_encode(['success' => true, 'category' => $category]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>

