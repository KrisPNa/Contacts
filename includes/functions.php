<?php
function getAllContacts($search = "", $user_id = null) {
    global $pdo;
    
    $where_conditions = [];
    $params = [];
    
    if ($user_id !== null) {
        $where_conditions[] = "c.user_id = ?";
        $params[] = $user_id;
    }
    
    if (!empty($search)) {
        $search_condition = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
        $where_conditions[] = $search_condition;
        $searchTerm = "%" . $search . "%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    $where_sql = "";
    if (!empty($where_conditions)) {
        $where_sql = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    $sql = "SELECT c.*, GROUP_CONCAT(cat.name) as categories 
            FROM contacts c 
            LEFT JOIN contact_categories cc ON c.id = cc.contact_id 
            LEFT JOIN categories cat ON cc.category_id = cat.id 
            $where_sql
            GROUP BY c.id 
            ORDER BY c.first_name, c.last_name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getContactsWithPagination($search = "", $category_filter = "", $limit = 10, $offset = 0, $user_id = null) {
    global $pdo;
    
    $where_conditions = [];
    $params = [];
    
    if ($user_id !== null) {
        $where_conditions[] = "c.user_id = ?";
        $params[] = $user_id;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($category_filter)) {
        $where_conditions[] = "cat.id = ?";
        $params[] = $category_filter;
    }
    
    $where_sql = "";
    if (!empty($where_conditions)) {
        $where_sql = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Получаем контакты
    $sql = "SELECT c.*, GROUP_CONCAT(cat.name) as categories 
            FROM contacts c 
            LEFT JOIN contact_categories cc ON c.id = cc.contact_id 
            LEFT JOIN categories cat ON cc.category_id = cat.id 
            $where_sql
            GROUP BY c.id 
            ORDER BY c.first_name, c.last_name 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем общее количество
    $count_sql = "SELECT COUNT(DISTINCT c.id) as total 
                  FROM contacts c 
                  LEFT JOIN contact_categories cc ON c.id = cc.contact_id 
                  LEFT JOIN categories cat ON cc.category_id = cat.id 
                  $where_sql";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return [
        'contacts' => $contacts,
        'total' => $total
    ];
}

function getContactById($id, $user_id = null) {
    global $pdo;
    
    $where_conditions = ["c.id = ?"];
    $params = [$id];
    
    if ($user_id !== null) {
        $where_conditions[] = "c.user_id = ?";
        $params[] = $user_id;
    }
    
    $sql = "SELECT c.*, GROUP_CONCAT(cc.category_id) as category_ids 
            FROM contacts c 
            LEFT JOIN contact_categories cc ON c.id = cc.contact_id 
            WHERE " . implode(" AND ", $where_conditions) . "
            GROUP BY c.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllCategories() {
    global $pdo;
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCategory($name) {
    global $pdo;
    
    try {
        // Проверяем, не существует ли уже категория с таким именем
        $check_sql = "SELECT id FROM categories WHERE name = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([trim($name)]);
        
        if ($check_stmt->fetch()) {
            throw new Exception('Категория с таким именем уже существует');
        }
        
        // Добавляем категорию
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([trim($name)]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        throw $e;
    }
}

function addContact($data, $user_id = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Добавляем контакт
        $sql = "INSERT INTO contacts (first_name, last_name, email, phone, address, user_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $user_id
        ]);
        
        $contact_id = $pdo->lastInsertId();
        
        // Добавляем категории
        if (!empty($data['categories'])) {
            foreach ($data['categories'] as $category_id) {
                $sql = "INSERT INTO contact_categories (contact_id, category_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$contact_id, $category_id]);
            }
        }
        
        $pdo->commit();
        return $contact_id;
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateContact($id, $data, $user_id = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $where_conditions = ["id = ?"];
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address']
        ];
        
        if ($user_id !== null) {
            $where_conditions[] = "user_id = ?";
            $params[] = $user_id;
        }
        
        $params[] = $id;
        
        // Обновляем контакт
        $sql = "UPDATE contacts SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? 
                WHERE " . implode(" AND ", $where_conditions);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Удаляем старые категории
        $sql = "DELETE FROM contact_categories WHERE contact_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Добавляем новые категории
        if (!empty($data['categories'])) {
            foreach ($data['categories'] as $category_id) {
                $sql = "INSERT INTO contact_categories (contact_id, category_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $category_id]);
            }
        }
        
        $pdo->commit();
        return true;
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteContact($id, $user_id = null) {
    global $pdo;
    
    $where_conditions = ["id = ?"];
    $params = [$id];
    
    if ($user_id !== null) {
        $where_conditions[] = "user_id = ?";
        $params[] = $user_id;
    }
    
    $sql = "DELETE FROM contacts WHERE " . implode(" AND ", $where_conditions);
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}


?>