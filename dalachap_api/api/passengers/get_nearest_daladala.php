<?php
// =============================================
// API: Get Nearest Daladala to Passenger Location
// Endpoint: GET /api/passengers/get_nearest_daladala.php?latitude=-6.7924&longitude=39.2083&radius=2
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/validation.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Validate coordinates
if (!isset($_GET['latitude']) || !isset($_GET['longitude'])) {
    sendErrorResponse("latitude and longitude are required", 400);
}

$lat = floatval($_GET['latitude']);
$lng = floatval($_GET['longitude']);
$radius = isset($_GET['radius']) ? floatval($_GET['radius']) : 2; // Default 2km

if (!validateLatitude($lat) || !validateLongitude($lng)) {
    sendErrorResponse("Invalid coordinates", 400);
}

// Haversine formula to calculate distance
// Find vehicles within radius with ongoing trips
$sql = "SELECT 
            v.vehicle_id,
            v.registration_number,
            g.latitude,
            g.longitude,
            g.speed_kmh,
            g.recorded_at,
            r.route_name,
            r.route_code,
            r.base_fare,
            (6371 * acos(cos(radians(?)) * cos(radians(g.latitude)) * 
            cos(radians(g.longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(g.latitude)))) AS distance_km
        FROM gps_locations g
        JOIN daladala_vehicles v ON g.vehicle_id = v.vehicle_id
        JOIN trips t ON v.vehicle_id = t.vehicle_id AND t.trip_status = 'ongoing'
        JOIN routes r ON t.route_id = r.route_id
        WHERE v.status = 'active'
        AND g.recorded_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        HAVING distance_km <= ?
        ORDER BY distance_km ASC
        LIMIT 10";

$vehicles = $database->fetchAll($sql, [$lat, $lng, $lat, $radius], "dddd");

sendSuccessResponse([
    "current_location" => [
        "latitude" => $lat,
        "longitude" => $lng
    ],
    "nearby_daladalas" => $vehicles,
    "count" => count($vehicles)
], "Nearby daladalas retrieved successfully");
?>