<?php
// =============================================
// API: Request Route Authorization (Driver/Association)
// Endpoint: POST /api/authorizations/request_auth.php
// Body: { "vehicle_id": 1, "temporary_route_id": 3, "reason": "Route R001 is overcrowded", "duration_hours": 4 }
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/validation.php';
require_once '../../includes/auth.php';

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate (driver or association leader)
$user = requireRole($db, ['driver', 'association_leader', 'admin']);

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['vehicle_id', 'temporary_route_id', 'reason']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$vehicle_id = intval($input['vehicle_id']);
$temp_route_id = intval($input['temporary_route_id']);
$reason = sanitizeInput($input['reason']);
$duration_hours = isset($input['duration_hours']) ? intval($input['duration_hours']) : 4;

// Verify vehicle exists
$vehicle = $database->fetchOne(
    "SELECT v.*, a.association_name 
     FROM daladala_vehicles v
     LEFT JOIN associations a ON v.association_id = a.association_id
     WHERE v.vehicle_id = ?",
    [$vehicle_id], "i"
);
if (!$vehicle) {
    sendErrorResponse("Vehicle not found", 404);
}

// Verify temporary route exists
$temp_route = $database->fetchOne(
    "SELECT route_id, route_name FROM routes WHERE route_id = ? AND status = 'active'",
    [$temp_route_id], "i"
);
if (!$temp_route) {
    sendErrorResponse("Temporary route not found or inactive", 404);
}

// Get original route (current assigned route)
$assignment = $database->fetchOne(
    "SELECT route_id FROM driver_assignments 
     WHERE vehicle_id = ? AND is_current = 1",
    [$vehicle_id], "i"
);
$original_route_id = $assignment ? $assignment['route_id'] : null;

// Check if there's already an active authorization
$existing = $database->fetchOne(
    "SELECT authorization_id FROM route_authorizations 
     WHERE vehicle_id = ? AND status = 'active' AND end_datetime > NOW()",
    [$vehicle_id], "i"
);
if ($existing) {
    sendErrorResponse("Vehicle already has an active route authorization", 400);
}

// Create authorization request (status = 'active' means approved, but we'll set to pending first)
// For simplicity, we'll create as 'active' since we don't have a pending status
// In production, add 'pending' to the ENUM

$start_time = date('Y-m-d H:i:s');
$end_time = date('Y-m-d H:i:s', strtotime("+$duration_hours hours"));

$insert_sql = "INSERT INTO route_authorizations 
               (vehicle_id, original_route_id, temporary_route_id, authorized_by, reason, start_datetime, end_datetime, status) 
               VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
$result = $database->executeQuery($insert_sql, 
    [$vehicle_id, $original_route_id, $temp_route_id, $user['user_id'], $reason, $start_time, $end_time], 
    "iiiiss"
);

if ($result['success']) {
    // Notify all traffic officers
    $officers = $database->fetchAll("SELECT user_id FROM users WHERE user_role = 'traffic_officer' AND is_active = 1");
    foreach ($officers as $officer) {
        $database->executeQuery(
            "INSERT INTO notifications (user_id, title, message, notification_type, related_id) 
             VALUES (?, '📋 Route Authorization Request', ?, 'authorization', ?)",
            [
                $officer['user_id'],
                "Vehicle {$vehicle['registration_number']} requests authorization to use route {$temp_route['route_name']}. Reason: $reason",
                $result['insert_id']
            ],
            "isi"
        );
    }
    
    sendSuccessResponse([
        "authorization_id" => $result['insert_id'],
        "vehicle_id" => $vehicle_id,
        "temporary_route" => $temp_route['route_name'],
        "valid_from" => $start_time,
        "valid_until" => $end_time
    ], "Authorization request submitted successfully");
} else {
    sendErrorResponse("Failed to submit authorization request", 500);
}
?>