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
    
    // Prevent self-deletion
    if ($data['user_id'] == $user['user_id']) {
        sendErrorResponse("Cannot delete your own account", 403);
    }
    
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $data['user_id']);
    
    if ($stmt->execute()) {
        sendSuccessResponse([], "User deleted successfully");
    } else {
        sendErrorResponse("Failed to delete user: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to delete user: " . $e->getMessage(), 500);
}
?>

