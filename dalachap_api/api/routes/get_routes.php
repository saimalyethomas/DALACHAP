<?php
// =============================================
// API: Get All Routes
// Endpoint: GET /api/routes/get_routes.php
// Query: ?status=active (optional)
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Optional status filter
$status = isset($_GET['status']) ? $_GET['status'] : 'active';

// Build query
$sql = "SELECT route_id, route_code, route_name, starting_point, ending_point, 
               distance_km, estimated_duration_minutes, base_fare, status 
        FROM routes 
        WHERE 1=1";
$params = [];
$types = "";

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY route_name";

$routes = $database->fetchAll($sql, $params, $types);

sendSuccessResponse([
    "routes" => $routes,
    "total" => count($routes)
], "Routes retrieved successfully");
?>