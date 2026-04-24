<?php
// =============================================
// API: Check Route Status (for passengers)
// Endpoint: GET /api/passengers/check_route_status.php?route_id=1
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';

// Only GET method allowed (no authentication required for passengers)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Validate route_id
if (!isset($_GET['route_id']) || empty($_GET['route_id'])) {
    // If no route_id, return all routes with basic status
    $routes = $database->fetchAll(
        "SELECT route_id, route_code, route_name, starting_point, ending_point, base_fare 
         FROM routes 
         WHERE status = 'active' 
         ORDER BY route_name"
    );
    
    foreach ($routes as &$route) {
        // Get recent demand report
        $demand = $database->fetchOne(
            "SELECT report_type, passenger_waiting_count, reported_at 
             FROM demand_reports 
             WHERE route_id = ? 
             ORDER BY reported_at DESC LIMIT 1",
            [$route['route_id']], "i"
        );
        $route['current_demand'] = $demand ? $demand['report_type'] : 'normal';
        $route['waiting_passengers'] = $demand ? $demand['passenger_waiting_count'] : 0;
        
        // Count active vehicles on this route
        $vehicles = $database->fetchOne(
            "SELECT COUNT(*) as count FROM trips t 
             WHERE t.route_id = ? AND t.trip_status = 'ongoing'",
            [$route['route_id']], "i"
        );
        $route['active_vehicles'] = intval($vehicles['count']);
    }
    
    sendSuccessResponse([
        "routes" => $routes,
        "last_updated" => date('Y-m-d H:i:s')
    ], "All routes status retrieved");
}

$route_id = intval($_GET['route_id']);

// Get route details
$route = $database->fetchOne(
    "SELECT route_id, route_code, route_name, starting_point, ending_point, 
            distance_km, estimated_duration_minutes, base_fare 
     FROM routes 
     WHERE route_id = ? AND status = 'active'",
    [$route_id], "i"
);

if (!$route) {
    sendErrorResponse("Route not found", 404);
}

// Get recent demand report
$demand = $database->fetchOne(
    "SELECT report_type, passenger_waiting_count, estimated_wait_time_minutes, reported_at 
     FROM demand_reports 
     WHERE route_id = ? 
     ORDER BY reported_at DESC LIMIT 1",
    [$route_id], "i"
);

$route['current_demand'] = $demand ? $demand['report_type'] : 'normal';
$route['waiting_passengers'] = $demand ? $demand['passenger_waiting_count'] : 0;
$route['estimated_wait_time'] = $demand ? $demand['estimated_wait_time_minutes'] : $route['estimated_duration_minutes'];

// Get active vehicles on this route
$vehicles = $database->fetchAll(
    "SELECT DISTINCT v.vehicle_id, v.registration_number, 
            g.latitude, g.longitude, g.recorded_at
     FROM trips t
     JOIN daladala_vehicles v ON t.vehicle_id = v.vehicle_id
     LEFT JOIN gps_locations g ON v.vehicle_id = g.vehicle_id AND g.recorded_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
     WHERE t.route_id = ? AND t.trip_status = 'ongoing'
     LIMIT 10",
    [$route_id], "i"
);

$route['active_vehicles'] = count($vehicles);
$route['vehicles'] = $vehicles;

// Get route stops
$stops = $database->fetchAll(
    "SELECT stop_id, stop_name, stop_order, estimated_arrival_minutes 
     FROM route_stops 
     WHERE route_id = ? 
     ORDER BY stop_order",
    [$route_id], "i"
);
$route['stops'] = $stops;

sendSuccessResponse($route, "Route status retrieved successfully");
?>