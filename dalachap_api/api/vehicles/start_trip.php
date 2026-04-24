<?php
// =============================================
// API: Start a New Trip
// Endpoint: POST /api/vehicles/start_trip.php
// Body: { "vehicle_id": 1, "route_id": 2, "start_latitude": -6.7924, "start_longitude": 39.2083 }
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

// Authenticate (driver or admin)
$user = requireRole($db, ['driver', 'admin', 'association_leader']);

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['vehicle_id', 'route_id']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$vehicle_id = intval($input['vehicle_id']);
$route_id = intval($input['route_id']);
$start_lat = isset($input['start_latitude']) ? floatval($input['start_latitude']) : null;
$start_lng = isset($input['start_longitude']) ? floatval($input['start_longitude']) : null;

// Verify vehicle exists and is active
$vehicle = $database->fetchOne(
    "SELECT vehicle_id, status FROM daladala_vehicles WHERE vehicle_id = ?",
    [$vehicle_id], "i"
);
if (!$vehicle) {
    sendErrorResponse("Vehicle not found", 404);
}
if ($vehicle['status'] != 'active') {
    sendErrorResponse("Vehicle is not active. Status: " . $vehicle['status'], 400);
}

// Verify route exists and is active
$route = $database->fetchOne(
    "SELECT route_id, route_name FROM routes WHERE route_id = ? AND status = 'active'",
    [$route_id], "i"
);
if (!$route) {
    sendErrorResponse("Route not found or inactive", 404);
}

// Check if vehicle already has an ongoing trip
$ongoing = $database->fetchOne(
    "SELECT trip_id FROM trips WHERE vehicle_id = ? AND trip_status = 'ongoing'",
    [$vehicle_id], "i"
);
if ($ongoing) {
    sendErrorResponse("Vehicle already has an ongoing trip. End current trip first.", 400);
}

// Verify driver is assigned to this vehicle
$assignment = $database->fetchOne(
    "SELECT assignment_id FROM driver_assignments 
     WHERE driver_id = ? AND vehicle_id = ? AND is_current = 1",
    [$user['user_id'], $vehicle_id], "ii"
);
if ($user['user_role'] != 'admin' && !$assignment) {
    sendErrorResponse("You are not assigned to this vehicle", 403);
}

// Start the trip
$insert_sql = "INSERT INTO trips (vehicle_id, driver_id, route_id, start_time, start_latitude, start_longitude, trip_status) 
               VALUES (?, ?, ?, NOW(), ?, ?, 'ongoing')";
$result = $database->executeQuery($insert_sql, 
    [$vehicle_id, $user['user_id'], $route_id, $start_lat, $start_lng], 
    "iiidd"
);

if ($result['success']) {
    $trip_id = $result['insert_id'];
    
    // Log the action
    $database->executeQuery(
        "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'START_TRIP', ?, ?)",
        [$user['user_id'], "Started trip on route {$route['route_name']}", $_SERVER['REMOTE_ADDR'] ?? 'unknown'],
        "iss"
    );
    
    sendSuccessResponse([
        "trip_id" => $trip_id,
        "vehicle_id" => $vehicle_id,
        "route_id" => $route_id,
        "route_name" => $route['route_name'],
        "start_time" => date('Y-m-d H:i:s')
    ], "Trip started successfully");
} else {
    sendErrorResponse("Failed to start trip: " . ($result['error'] ?? "Unknown error"), 500);
}
?>