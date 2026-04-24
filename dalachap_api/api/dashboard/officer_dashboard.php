<?php
// =============================================
// API: Traffic Officer Dashboard Data
// Endpoint: GET /api/dashboard/officer_dashboard.php
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate as traffic officer or admin
$user = requireRole($db, ['traffic_officer', 'admin']);

// Get dashboard statistics

// 1. Active vehicles count
$active_vehicles = $database->fetchOne("SELECT COUNT(*) as count FROM daladala_vehicles WHERE status = 'active'");

// 2. Ongoing trips count
$ongoing_trips = $database->fetchOne("SELECT COUNT(*) as count FROM trips WHERE trip_status = 'ongoing'");

// 3. Recent congestion reports (last 24 hours)
$congestion_reports = $database->fetchAll(
    "SELECT d.*, r.route_name, u.full_name as reported_by_name 
     FROM demand_reports d
     JOIN routes r ON d.route_id = r.route_id
     LEFT JOIN users u ON d.reported_by = u.user_id
     WHERE d.reported_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
     AND d.report_type IN ('high_demand', 'overcrowded')
     ORDER BY d.reported_at DESC
     LIMIT 20"
);

// 4. Active authorizations
$active_auths = $database->fetchAll(
    "SELECT a.*, v.registration_number, r.route_name as original_route
     FROM route_authorizations a
     JOIN daladala_vehicles v ON a.vehicle_id = v.vehicle_id
     JOIN routes r ON a.original_route_id = r.route_id
     WHERE a.status = 'active' AND a.end_datetime > NOW()
     ORDER BY a.end_datetime ASC
     LIMIT 10"
);

// 5. Route demand summary
$route_demand = $database->fetchAll(
    "SELECT route_id, route_name, avg_waiting_passengers, report_count 
     FROM vw_route_demand_summary 
     WHERE avg_waiting_passengers IS NOT NULL
     ORDER BY avg_waiting_passengers DESC"
);

// 6. Recent notifications for this officer
$notifications = $database->fetchAll(
    "SELECT * FROM notifications 
     WHERE user_id = ? 
     ORDER BY created_at DESC 
     LIMIT 10",
    [$user['user_id']], "i"
);

// Prepare response
$dashboard_data = [
    "stats" => [
        "active_vehicles" => intval($active_vehicles['count']),
        "ongoing_trips" => intval($ongoing_trips['count']),
        "congestion_reports_24h" => count($congestion_reports),
        "active_authorizations" => count($active_auths)
    ],
    "congestion_reports" => $congestion_reports,
    "active_authorizations" => $active_auths,
    "route_demand" => $route_demand,
    "recent_notifications" => $notifications,
    "last_updated" => date('Y-m-d H:i:s')
];

sendSuccessResponse($dashboard_data, "Dashboard data retrieved successfully");
?>