<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin', 'association_leader']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        sendErrorResponse("Invalid JSON data", 400);
    }
    
    $required = ['full_name', 'email', 'phone', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendErrorResponse("Missing required field: $field", 422);
        }
    }
    
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    $role = $data['role'] ?? 'driver';
    
    $stmt = $db->prepare("INSERT INTO users (full_name, email, phone_number, password_hash, user_role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param(
        "sssss",
        $data['full_name'],
        $data['email'],
        $data['phone'],
        $passwordHash,
        $role
    );
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        sendSuccessResponse(["user_id" => $userId], "User created successfully", 201);
    } else {
        if ($db->errno == 1062) {
            sendErrorResponse("Email already exists", 409);
        }
        sendErrorResponse("Failed to create user: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to create user: " . $e->getMessage(), 500);
}
?>

