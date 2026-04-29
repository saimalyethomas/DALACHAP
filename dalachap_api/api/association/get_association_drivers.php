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
    $sql = "SELECT u.user_id, u.full_name, u.email, u.phone_number, u.is_active, u.created_at,
                   da.assignment_id, v.registration_number as assigned_vehicle, v.vehicle_id,
                   r.route_name, da.assigned_date, da.is_current
            FROM users u
            LEFT JOIN driver_assignments da ON u.user_id = da.driver_id AND da.is_current = 1
            LEFT JOIN daladala_vehicles v ON da.vehicle_id = v.vehicle_id
            LEFT JOIN routes r ON da.route_id = r.route_id
            WHERE u.user_role = 'driver'
            ORDER BY u.created_at DESC";
    
    $result = $db->query($sql);
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    
    sendSuccessResponse(["drivers" => $drivers], "Drivers retrieved successfully");
} catch (Exception $e) {
    sendErrorResponse("Failed to retrieve drivers: " . $e->getMessage(), 500);
}
?>

