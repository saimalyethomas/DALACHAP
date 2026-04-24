<?php
// =============================================
// API: Update Vehicle GPS Location
// Endpoint: POST /api/vehicles/update_location.php
// Body: { "vehicle_id": 1, "latitude": -6.7924, "longitude": 39.2083, 
//         "speed": 45.5, "trip_id": 5 }
// Requires: Driver or Admin authentication
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

// Authenticate (driver or admin can update location)
$user = requireRole($db, ['driver', 'admin', 'association_leader']);

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['vehicle_id', 'latitude', 'longitude']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$vehicle_id = intval($input['vehicle_id']);
$latitude = floatval($input['latitude']);
$longitude = floatval($input['longitude']);
$speed = isset($input['speed']) ? floatval($input['speed']) : null;
$heading = isset($input['heading']) ? intval($input['heading']) : null;
$trip_id = isset($input['trip_id']) ? intval($input['trip_id']) : null;

// Validate coordinates
if (!validateLatitude($latitude)) {
    sendValidationError(['latitude' => 'Invalid latitude value']);
}
if (!validateLongitude($longitude)) {
    sendValidationError(['longitude' => 'Invalid longitude value']);
}

// Check if vehicle exists
$vehicle_check = $database->fetchOne("SELECT vehicle_id FROM daladala_vehicles WHERE vehicle_id = ?", [$vehicle_id], "i");
if (!$vehicle_check) {
    sendErrorResponse("Vehicle not found", 404);
}

// Insert GPS location
$insert_sql = "INSERT INTO gps_locations (vehicle_id, trip_id, latitude, longitude, speed_kmh, heading, recorded_at) 
               VALUES (?, ?, ?, ?, ?, ?, NOW())";
$result = $database->executeQuery($insert_sql, [$vehicle_id, $trip_id, $latitude, $longitude, $speed, $heading], "iiddii");

if ($result['success']) {
    sendSuccessResponse([
        "location_id" => $result['insert_id'],
        "vehicle_id" => $vehicle_id,
        "latitude" => $latitude,
        "longitude" => $longitude,
        "timestamp" => date('Y-m-d H:i:s')
    ], "Location updated successfully");
} else {
    sendErrorResponse("Failed to update location: " . ($result['error'] ?? "Unknown error"), 500);
}
?>