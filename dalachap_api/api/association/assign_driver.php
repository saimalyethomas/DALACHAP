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
    
    if (!$data || empty($data['driver_id']) || empty($data['vehicle_id']) || empty($data['route_id'])) {
        sendErrorResponse("Driver ID, Vehicle ID, and Route ID are required", 422);
    }
    
    $driverId = intval($data['driver_id']);
    $vehicleId = intval($data['vehicle_id']);
    $routeId = intval($data['route_id']);
    
    // End any current assignment for this driver
    $endStmt = $db->prepare("UPDATE driver_assignments SET is_current = 0, end_date = CURDATE() WHERE driver_id = ? AND is_current = 1");
    $endStmt->bind_param("i", $driverId);
    $endStmt->execute();
    
    // Create new assignment
    $stmt = $db->prepare("INSERT INTO driver_assignments (driver_id, vehicle_id, route_id, assigned_date, is_current) VALUES (?, ?, ?, CURDATE(), 1)");
    $stmt->bind_param("iii", $driverId, $vehicleId, $routeId);
    
    if ($stmt->execute()) {
        sendSuccessResponse(["assignment_id" => $stmt->insert_id], "Driver assigned successfully");
    } else {
        sendErrorResponse("Failed to assign driver: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to assign driver: " . $e->getMessage(), 500);
}
?>

