<?php
// =============================================
// API: Get All Vehicles (with filters)
// Endpoint: GET /api/vehicles/get_vehicles.php
// Query: ?status=active&association_id=1
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
$user = authenticate($db);

// Build query with filters
$sql = "SELECT v.vehicle_id, v.registration_number, v.owner_name, v.owner_phone, 
               v.capacity, v.status, v.last_maintenance_date,
               a.association_name, a.association_id
        FROM daladala_vehicles v
        LEFT JOIN associations a ON v.association_id = a.association_id
        WHERE 1=1";
$params = [];
$types = "";

// Filter by status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $sql .= " AND v.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Filter by association
if (isset($_GET['association_id']) && !empty($_GET['association_id'])) {
    $sql .= " AND v.association_id = ?";
    $params[] = intval($_GET['association_id']);
    $types .= "i";
}

// Role-based restrictions
if ($user['user_role'] == 'driver') {
    // Driver can only see their assigned vehicle
    $sql .= " AND v.vehicle_id IN (SELECT vehicle_id FROM driver_assignments WHERE driver_id = ? AND is_current = 1)";
    $params[] = $user['user_id'];
    $types .= "i";
} elseif ($user['user_role'] == 'association_leader') {
    // Association leader sees only their association's vehicles
    $sql .= " AND v.association_id IN (SELECT association_id FROM users WHERE user_id = ?)";
    $params[] = $user['user_id'];
    $types .= "i";
}

$sql .= " ORDER BY v.registration_number";

$vehicles = $database->fetchAll($sql, $params, $types);

// Get current location for each vehicle
foreach ($vehicles as &$vehicle) {
    $location = $database->fetchOne(
        "SELECT latitude, longitude, speed_kmh, recorded_at 
         FROM gps_locations 
         WHERE vehicle_id = ? 
         ORDER BY recorded_at DESC LIMIT 1",
        [$vehicle['vehicle_id']], "i"
    );
    $vehicle['current_location'] = $location;
    
    // Get current trip status
    $trip = $database->fetchOne(
        "SELECT trip_id, route_id, trip_status, start_time 
         FROM trips 
         WHERE vehicle_id = ? AND trip_status = 'ongoing'",
        [$vehicle['vehicle_id']], "i"
    );
    $vehicle['current_trip'] = $trip;
}

sendSuccessResponse([
    "vehicles" => $vehicles,
    "total" => count($vehicles)
], "Vehicles retrieved successfully");
?>