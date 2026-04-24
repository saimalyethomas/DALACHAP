<?php
// =============================================
// API: Get Route Details with Stops
// Endpoint: GET /api/routes/get_route_details.php?route_id=1
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Validate route_id
if (!isset($_GET['route_id']) || empty($_GET['route_id'])) {
    sendErrorResponse("route_id is required", 400);
}

$route_id = intval($_GET['route_id']);

// Get route details
$route_sql = "SELECT route_id, route_code, route_name, starting_point, ending_point, 
                     distance_km, estimated_duration_minutes, base_fare, status 
              FROM routes 
              WHERE route_id = ?";
$route = $database->fetchOne($route_sql, [$route_id], "i");

if (!$route) {
    sendErrorResponse("Route not found", 404);
}

// Get route stops
$stops_sql = "SELECT stop_id, stop_name, stop_order, latitude, longitude, 
                     estimated_arrival_minutes 
              FROM route_stops 
              WHERE route_id = ? 
              ORDER BY stop_order";
$stops = $database->fetchAll($stops_sql, [$route_id], "i");

$route['stops'] = $stops;

sendSuccessResponse($route, "Route details retrieved successfully");
?>