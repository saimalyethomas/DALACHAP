<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin']);

try {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $limit = min($limit, 200);
    
    $stmt = $db->prepare("SELECT s.log_id, s.user_id, s.action, s.description, s.ip_address, s.created_at, u.full_name as user_name 
                          FROM system_logs s 
                          LEFT JOIN users u ON s.user_id = u.user_id 
                          ORDER BY s.created_at DESC 
                          LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    sendSuccessResponse(["logs" => $logs], "System logs retrieved successfully");
} catch (Exception $e) {
    sendErrorResponse("Failed to retrieve system logs: " . $e->getMessage(), 500);
}
?>

