<?php
// Проверка авторизации пользователя
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Если администратор пытается зайти в обычную часть, разрешаем (администратор может работать как обычный пользователь)
// Но если это страница, требующая только пользователя, можно добавить проверку

// Функция для получения ID текущего пользователя
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Функция для проверки, является ли пользователь администратором
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Функция для выхода
function logout() {
    session_start();
    $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    session_destroy();
    header("Location: " . ($is_admin ? "admin_login.php" : "auth.php"));
    exit;
}
?>

