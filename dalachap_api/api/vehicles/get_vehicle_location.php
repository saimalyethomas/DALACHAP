<?php
// =============================================
// API: Get Vehicle Location History
// Endpoint: GET /api/vehicles/get_vehicle_location.php?vehicle_id=1&limit=10
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate user
$user = authenticate($conn);

// Validate vehicle_id
if (!isset($_GET['vehicle_id']) || empty($_GET['vehicle_id'])) {
    sendErrorResponse("vehicle_id is required", 400);
}

$vehicle_id = intval($_GET['vehicle_id']);
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;

// Get vehicle details
$vehicle = $database->fetchOne(
    "SELECT v.vehicle_id, v.registration_number, v.owner_name, a.association_name 
     FROM daladala_vehicles v
     LEFT JOIN associations a ON v.association_id = a.association_id
     WHERE v.vehicle_id = ?",
    [$vehicle_id], "i"
);

if (!$vehicle) {
    sendErrorResponse("Vehicle not found", 404);
}

// Get location history
$locations = $database->fetchAll(
    "SELECT location_id, latitude, longitude, speed_kmh, heading, recorded_at 
     FROM gps_locations 
     WHERE vehicle_id = ? 
     AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
     ORDER BY recorded_at DESC 
     LIMIT ?",
    [$vehicle_id, $hours, $limit], "iii"
);

// Get current trip info if any
$current_trip = $database->fetchOne(
    "SELECT t.trip_id, t.start_time, t.passenger_count, r.route_name, r.route_code
     FROM trips t
     LEFT JOIN routes r ON t.route_id = r.route_id
     WHERE t.vehicle_id = ? AND t.trip_status = 'ongoing'",
    [$vehicle_id], "i"
);

$vehicle['current_trip'] = $current_trip;
$vehicle['location_history'] = array_reverse($locations); // Oldest first for mapping
$vehicle['total_locations'] = count($locations);

sendSuccessResponse($vehicle, "Vehicle location history retrieved");
?>