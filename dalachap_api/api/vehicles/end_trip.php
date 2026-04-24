<?php
// =============================================
// API: End Current Trip
// Endpoint: POST /api/vehicles/end_trip.php
// Body: { "trip_id": 5, "passenger_count": 42, "end_latitude": -6.8000, "end_longitude": 39.2100 }
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

// Authenticate
$user = authenticate($conn);

// Get JSON input
$input = getJsonInput();

// Validate required fields
if (!isset($input['trip_id']) || empty($input['trip_id'])) {
    sendErrorResponse("trip_id is required", 400);
}

$trip_id = intval($input['trip_id']);
$passenger_count = isset($input['passenger_count']) ? intval($input['passenger_count']) : 0;
$end_lat = isset($input['end_latitude']) ? floatval($input['end_latitude']) : null;
$end_lng = isset($input['end_longitude']) ? floatval($input['end_longitude']) : null;

// Get trip details
$trip = $database->fetchOne(
    "SELECT t.*, v.registration_number 
     FROM trips t
     JOIN daladala_vehicles v ON t.vehicle_id = v.vehicle_id
     WHERE t.trip_id = ?",
    [$trip_id], "i"
);

if (!$trip) {
    sendErrorResponse("Trip not found", 404);
}

if ($trip['trip_status'] != 'ongoing') {
    sendErrorResponse("Trip is already " . $trip['trip_status'], 400);
}

// Verify permission (driver of that trip or admin)
if ($user['user_role'] != 'admin' && $trip['driver_id'] != $user['user_id']) {
    sendErrorResponse("You are not authorized to end this trip", 403);
}

// End the trip
$update_sql = "UPDATE trips 
               SET end_time = NOW(), trip_status = 'completed', 
                   passenger_count = ?, end_latitude = ?, end_longitude = ?
               WHERE trip_id = ?";
$result = $database->executeQuery($update_sql, 
    [$passenger_count, $end_lat, $end_lng, $trip_id], 
    "iddi"
);

if ($result['success']) {
    // Log the action
    $database->executeQuery(
        "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'END_TRIP', ?, ?)",
        [$user['user_id'], "Ended trip #$trip_id with $passenger_count passengers", $_SERVER['REMOTE_ADDR'] ?? 'unknown'],
        "iss"
    );
    
    sendSuccessResponse([
        "trip_id" => $trip_id,
        "vehicle_id" => $trip['vehicle_id'],
        "registration_number" => $trip['registration_number'],
        "passenger_count" => $passenger_count,
        "duration_minutes" => null, // Calculate if needed
        "end_time" => date('Y-m-d H:i:s')
    ], "Trip ended successfully");
} else {
    sendErrorResponse("Failed to end trip", 500);
}
?>