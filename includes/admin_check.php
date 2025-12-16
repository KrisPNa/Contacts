<?php
// Проверка авторизации администратора
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Если пользователь не администратор, перенаправляем на обычную страницу
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Функция для получения ID текущего администратора
function getCurrentAdminId() {
    return $_SESSION['user_id'] ?? null;
}

// Функция для проверки, является ли пользователь администратором
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Функция для получения статистики системы
function getSystemStats() {
    global $pdo;
    
    try {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM contacts) as total_contacts";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['total_users' => 0, 'total_contacts' => 0];
    }
}
?>