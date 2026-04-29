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
    
    if (!$data || empty($data['user_id'])) {
        sendErrorResponse("User ID is required", 422);
    }
    
    $fields = [];
    $types = "";
    $values = [];
    
    if (isset($data['full_name'])) {
        $fields[] = "full_name = ?";
        $types .= "s";
        $values[] = $data['full_name'];
    }
    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $types .= "s";
        $values[] = $data['email'];
    }
    if (isset($data['phone'])) {
        $fields[] = "phone_number = ?";
        $types .= "s";
        $values[] = $data['phone'];
    }
    if (isset($data['license_number'])) {
        $fields[] = "profile_picture = ?";
        $types .= "s";
        $values[] = $data['license_number'];
    }
    if (isset($data['is_active'])) {
        $fields[] = "is_active = ?";
        $types .= "i";
        $values[] = $data['is_active'];
    }
    
    if (empty($fields)) {
        sendErrorResponse("No fields to update", 422);
    }
    
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = ?";
    $types .= "i";
    $values[] = $data['user_id'];
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        sendSuccessResponse([], "User updated successfully");
    } else {
        sendErrorResponse("Failed to update user: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to update user: " . $e->getMessage(), 500);
}
?>

