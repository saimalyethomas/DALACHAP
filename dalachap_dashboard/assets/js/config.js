// =============================================
// API Configuration
// =============================================

// API Base URL (change to your server)
const API_BASE_URL = 'http://localhost/DALACHAP/dalachap_api/api/';

// Frontend Base URL for navigation
const APP_BASE_URL = 'http://localhost/DALACHAP/';

// App Configuration
const APP_CONFIG = {
    appName: 'DalaChap',
    appVersion: '1.0',
    tokenKey: 'dalachap_token',
    userKey: 'dalachap_user'
};

// API Endpoints
const API_ENDPOINTS = {
    // Auth
    login: API_BASE_URL + 'auth/login.php',
    register: API_BASE_URL + 'auth/register.php',
    logout: API_BASE_URL + 'auth/logout.php',
    
    // Dashboard
    adminDashboard: API_BASE_URL + 'dashboard/admin_dashboard.php',
    officerDashboard: API_BASE_URL + 'dashboard/officer_dashboard.php',
    associationDashboard: API_BASE_URL + 'dashboard/association_dashboard.php',
    
    // Routes
    getRoutes: API_BASE_URL + 'routes/get_routes.php',
    getRouteDetails: API_BASE_URL + 'routes/get_route_details.php',
    addRoute: API_BASE_URL + 'admin/add_route.php',
    
    // Vehicles
    getVehicles: API_BASE_URL + 'vehicles/get_vehicles.php',
    getVehicleLocation: API_BASE_URL + 'vehicles/get_vehicle_location.php',
    updateLocation: API_BASE_URL + 'vehicles/update_location.php',
    startTrip: API_BASE_URL + 'vehicles/start_trip.php',
    endTrip: API_BASE_URL + 'vehicles/end_trip.php',
    
    // Demand
    reportCongestion: API_BASE_URL + 'demand/report_congestion.php',
    getDemandStatus: API_BASE_URL + 'demand/get_demand_status.php',
    getCongestedRoutes: API_BASE_URL + 'demand/get_congested_routes.php',
    
    // Authorizations
    getAuthorizations: API_BASE_URL + 'authorizations/get_authorizations.php',
    requestAuth: API_BASE_URL + 'authorizations/request_auth.php',
    revokeAuth: API_BASE_URL + 'authorizations/revoke_auth.php',
    
    // Notifications
    getNotifications: API_BASE_URL + 'notifications/get_notifications.php',
    markNotificationRead: API_BASE_URL + 'notifications/mark_read.php',
    
    // Association
    getAssociationVehicles: API_BASE_URL + 'association/get_association_vehicles.php',
    getAssociationDrivers: API_BASE_URL + 'association/get_association_drivers.php',
    assignDriver: API_BASE_URL + 'association/assign_driver.php',
    
    // Admin
    getUsers: API_BASE_URL + 'admin/get_users.php',
    addUser: API_BASE_URL + 'admin/add_user.php',
    updateUser: API_BASE_URL + 'admin/update_user.php',
    deleteUser: API_BASE_URL + 'admin/delete_user.php',
    getSystemLogs: API_BASE_URL + 'admin/get_system_logs.php',
    getDashboardStats: API_BASE_URL + 'admin/get_dashboard_stats.php',
    
    // Associations
    getAssociations: API_BASE_URL + 'admin/get_associations.php',
    addAssociation: API_BASE_URL + 'admin/add_association.php',
    updateAssociation: API_BASE_URL + 'admin/update_association.php',
    deleteAssociation: API_BASE_URL + 'admin/delete_association.php'
};
