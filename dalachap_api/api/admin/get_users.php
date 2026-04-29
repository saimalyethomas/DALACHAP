<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin', 'association_leader']);

try {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $role = isset($_GET['role']) ? $_GET['role'] : null;
    
    if ($userId) {
        $stmt = $db->prepare("SELECT user_id, full_name, email, phone_number, user_role, is_active, created_at FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if ($userData) {
            sendSuccessResponse(["user" => $userData], "User retrieved successfully");
        } else {
            sendErrorResponse("User not found", 404);
        }
    } else {
        $sql = "SELECT user_id, full_name, email, phone_number, user_role, is_active, created_at FROM users WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($role) {
            $sql .= " AND user_role = ?";
            $params[] = $role;
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        sendSuccessResponse(["users" => $users], "Users retrieved successfully");
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to retrieve users: " . $e->getMessage(), 500);
}
?>

