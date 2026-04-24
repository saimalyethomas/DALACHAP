<?php
// =============================================
// API: Get Association's Vehicles
// Endpoint: GET /api/association/get_association_vehicles.php
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Association leader only
$user = requireRole($db, ['association_leader', 'admin']);

// Get the association ID for this user
$association = $database->fetchOne(
    "SELECT association_id, association_name 
     FROM associations 
     WHERE association_id = (SELECT association_id FROM users WHERE user_id = ? LIMIT 1)
        OR association_id IN (SELECT association_id FROM daladala_vehicles WHERE owner_phone = ? LIMIT 1)",
    [$user['user_id'], $user['phone_number']], "is"
);

if (!$association && $user['user_role'] != 'admin') {
    sendErrorResponse("No association found for this user", 404);
}

$association_id = $user['user_role'] == 'admin' && isset($_GET['association_id']) 
                  ? intval($_GET['association_id']) 
                  : $association['association_id'];

// Get vehicles
$vehicles = $database->fetchAll(
    "SELECT v.*, 
            (SELECT COUNT(*) FROM trips WHERE vehicle_id = v.vehicle_id AND trip_status = 'ongoing') as is_active,
            (SELECT latitude FROM gps_locations WHERE vehicle_id = v.vehicle_id ORDER BY recorded_at DESC LIMIT 1) as current_latitude,
            (SELECT longitude FROM gps_locations WHERE vehicle_id = v.vehicle_id ORDER BY recorded_at DESC LIMIT 1) as current_longitude
     FROM daladala_vehicles v
     WHERE v.association_id = ?
     ORDER BY v.registration_number",
    [$association_id], "i"
);

// Get statistics
$stats = $database->fetchOne(
    "SELECT 
        COUNT(*) as total_vehicles,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_vehicles,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_vehicles,
        (SELECT COUNT(*) FROM trips WHERE vehicle_id IN (SELECT vehicle_id FROM daladala_vehicles WHERE association_id = ?) AND trip_status = 'ongoing') as active_trips
     FROM daladala_vehicles
     WHERE association_id = ?",
    [$association_id, $association_id], "ii"
);

sendSuccessResponse([
    "association" => $association,
    "statistics" => $stats,
    "vehicles" => $vehicles
], "Association vehicles retrieved successfully");
?>