<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['association_id'])) {
        sendErrorResponse("Association ID is required", 422);
    }
    
    $stmt = $db->prepare("DELETE FROM associations WHERE association_id = ?");
    $stmt->bind_param("i", $data['association_id']);
    
    if ($stmt->execute()) {
        sendSuccessResponse([], "Association deleted successfully");
    } else {
        sendErrorResponse("Failed to delete association: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to delete association: " . $e->getMessage(), 500);
}
?>

