-- =============================================
-- Database: dalachap_db
-- Description: DalaChap Dynamic Route Transit System
-- WITHOUT STORED PROCEDURES (PHP will handle logic)
-- =============================================

-- Create database (run this first)
CREATE DATABASE IF NOT EXISTS dalachap_db;
USE dalachap_db;

-- =============================================
-- 1. USERS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_role ENUM('admin', 'traffic_officer', 'association_leader', 'driver', 'passenger') NOT NULL,
    profile_picture VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (user_role),
    INDEX idx_phone (phone_number)
);

-- =============================================
-- 2. ROUTES TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS routes (
    route_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    route_code VARCHAR(20) UNIQUE NOT NULL,
    route_name VARCHAR(100) NOT NULL,
    starting_point VARCHAR(100) NOT NULL,
    ending_point VARCHAR(100) NOT NULL,
    distance_km DECIMAL(10,2) NOT NULL,
    estimated_duration_minutes INT(11) NOT NULL,
    base_fare DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_by INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_route_code (route_code)
);

-- =============================================
-- 3. ROUTE_STOPS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS route_stops (
    stop_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    route_id INT(11) NOT NULL,
    stop_name VARCHAR(100) NOT NULL,
    stop_order INT(11) NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    estimated_arrival_minutes INT(11) NULL,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    INDEX idx_route_stops (route_id, stop_order)
);

-- =============================================
-- 4. ASSOCIATIONS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS associations (
    association_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    association_name VARCHAR(100) NOT NULL,
    registration_number VARCHAR(50) UNIQUE NOT NULL,
    address TEXT NULL,
    phone_number VARCHAR(15) NOT NULL,
    email VARCHAR(100) NULL,
    chairman_name VARCHAR(100) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (association_name)
);

-- =============================================
-- 5. DALADALA_VEHICLES TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS daladala_vehicles (
    vehicle_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    owner_phone VARCHAR(15) NOT NULL,
    capacity INT(11) DEFAULT 30,
    association_id INT(11) NULL,
    status ENUM('active', 'inactive', 'maintenance', 'suspended') DEFAULT 'active',
    last_maintenance_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (association_id) REFERENCES associations(association_id) ON DELETE SET NULL,
    INDEX idx_registration (registration_number),
    INDEX idx_status (status)
);

-- =============================================
-- 6. DRIVER_VEHICLE ASSIGNMENT TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS driver_assignments (
    assignment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    driver_id INT(11) NOT NULL,
    vehicle_id INT(11) NOT NULL,
    route_id INT(11) NOT NULL,
    assigned_date DATE NOT NULL,
    end_date DATE NULL,
    is_current TINYINT(1) DEFAULT 1,
    FOREIGN KEY (driver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES daladala_vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    INDEX idx_current_driver (driver_id, is_current),
    INDEX idx_vehicle (vehicle_id)
);

-- =============================================
-- 7. TRIPS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS trips (
    trip_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT(11) NOT NULL,
    driver_id INT(11) NOT NULL,
    route_id INT(11) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    start_latitude DECIMAL(10,8) NULL,
    start_longitude DECIMAL(11,8) NULL,
    end_latitude DECIMAL(10,8) NULL,
    end_longitude DECIMAL(11,8) NULL,
    passenger_count INT(11) DEFAULT 0,
    trip_status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES daladala_vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    INDEX idx_trip_status (trip_status),
    INDEX idx_start_time (start_time),
    INDEX idx_driver (driver_id),
    INDEX idx_vehicle (vehicle_id)
);

-- =============================================
-- 8. GPS_LOCATIONS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS gps_locations (
    location_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT(11) NOT NULL,
    trip_id INT(11) NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    speed_kmh DECIMAL(10,2) NULL,
    heading INT(11) NULL,
    recorded_at DATETIME NOT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES daladala_vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(trip_id) ON DELETE SET NULL,
    INDEX idx_vehicle_time (vehicle_id, recorded_at),
    INDEX idx_trip (trip_id)
);

-- =============================================
-- 9. DEMAND_REPORTS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS demand_reports (
    report_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    route_id INT(11) NOT NULL,
    stop_id INT(11) NULL,
    reported_by INT(11) NULL,
    passenger_waiting_count INT(11) NOT NULL,
    estimated_wait_time_minutes INT(11) NULL,
    report_type ENUM('high_demand', 'low_demand', 'overcrowded', 'normal') DEFAULT 'normal',
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (stop_id) REFERENCES route_stops(stop_id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_route_demand (route_id, reported_at),
    INDEX idx_report_type (report_type)
);

-- =============================================
-- 10. ROUTE_AUTHORIZATIONS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS route_authorizations (
    authorization_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT(11) NOT NULL,
    original_route_id INT(11) NOT NULL,
    temporary_route_id INT(11) NULL,
    authorized_by INT(11) NOT NULL,
    reason TEXT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES daladala_vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (original_route_id) REFERENCES routes(route_id) ON DELETE CASCADE,
    FOREIGN KEY (temporary_route_id) REFERENCES routes(route_id) ON DELETE SET NULL,
    FOREIGN KEY (authorized_by) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_active_auth (status, start_datetime, end_datetime),
    INDEX idx_vehicle (vehicle_id)
);

-- =============================================
-- 11. NOTIFICATIONS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('demand_alert', 'route_change', 'authorization', 'system', 'general') DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    related_id INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
);

-- =============================================
-- 12. FEEDBACK TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    trip_id INT(11) NULL,
    rating INT(1) CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NULL,
    feedback_type ENUM('complaint', 'suggestion', 'compliment', 'general') DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(trip_id) ON DELETE SET NULL,
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
);

-- =============================================
-- 13. SYSTEM_LOGS TABLE
-- =============================================

CREATE TABLE IF NOT EXISTS system_logs (
    log_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
);

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert sample admin user
-- Note: Password is 'password123' (will be hashed properly in PHP)
INSERT INTO users (full_name, email, phone_number, password_hash, user_role, is_active) VALUES
('System Admin', 'admin@dalachap.com', '0712345678', 'temp_hash_use_php_password_hash()', 'admin', 1),
('John Traffic', 'traffic@dalachap.com', '0723456789', 'temp_hash_use_php_password_hash()', 'traffic_officer', 1);

-- Insert sample associations
INSERT INTO associations (association_name, registration_number, phone_number, email, chairman_name) VALUES
('Dar Rapid Transit Association', 'DRTA001', '0734567890', 'info@drta.co.tz', 'Mr. Hassan Juma'),
('Ubungo Daladala Owners', 'UDO002', '0745678901', 'ubungo@daladala.co.tz', 'Mrs. Fatma Omar');

-- Insert sample routes
INSERT INTO routes (route_code, route_name, starting_point, ending_point, distance_km, estimated_duration_minutes, base_fare, status) VALUES
('R001', 'Ubungo - Kivukoni', 'Ubungo Terminal', 'Kivukoni', 15.5, 45, 700, 'active'),
('R002', 'Gongo la Mboto - Posta', 'Gongo la Mboto', 'Posta', 22.0, 60, 1000, 'active'),
('R003', 'Kimara - Mwenge', 'Kimara', 'Mwenge', 8.5, 25, 500, 'active'),
('R004', 'Mbagala - Kariakoo', 'Mbagala', 'Kariakoo', 18.0, 55, 800, 'active');

-- Insert route stops for route R001
INSERT INTO route_stops (route_id, stop_name, stop_order, estimated_arrival_minutes) VALUES
(1, 'Ubungo Terminal', 1, 0),
(1, 'Ubungo Mwenge', 2, 10),
(1, 'Morocco', 3, 20),
(1, 'Mwenge', 4, 25),
(1, 'Magomeni', 5, 35),
(1, 'Posta', 6, 40),
(1, 'Kivukoni', 7, 45);

-- Insert sample vehicles
INSERT INTO daladala_vehicles (registration_number, owner_name, owner_phone, capacity, association_id, status) VALUES
('T123ABC', 'Hamza Mohamed', '0756789012', 30, 1, 'active'),
('T456DEF', 'Aisha Salim', '0767890123', 32, 1, 'active'),
('T789GHI', 'Juma Hassan', '0778901234', 30, 2, 'active');

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- View: Active trips with vehicle and driver info
CREATE OR REPLACE VIEW vw_active_trips AS
SELECT 
    t.trip_id,
    v.registration_number,
    u.full_name AS driver_name,
    r.route_name,
    t.start_time,
    TIMESTAMPDIFF(MINUTE, t.start_time, NOW()) AS duration_minutes,
    t.passenger_count
FROM trips t
INNER JOIN daladala_vehicles v ON t.vehicle_id = v.vehicle_id
INNER JOIN users u ON t.driver_id = u.user_id
INNER JOIN routes r ON t.route_id = r.route_id
WHERE t.trip_status = 'ongoing';

-- View: Route demand summary (last hour)
CREATE OR REPLACE VIEW vw_route_demand_summary AS
SELECT 
    r.route_id,
    r.route_name,
    COUNT(d.report_id) AS report_count,
    AVG(d.passenger_waiting_count) AS avg_waiting_passengers,
    MAX(d.reported_at) AS last_report_time
FROM routes r
LEFT JOIN demand_reports d ON r.route_id = d.route_id
WHERE d.reported_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY r.route_id, r.route_name;

-- View: Current vehicle locations (last 5 minutes)
CREATE OR REPLACE VIEW vw_current_vehicle_locations AS
SELECT 
    v.vehicle_id,
    v.registration_number,
    g.latitude,
    g.longitude,
    g.speed_kmh,
    g.recorded_at,
    r.route_name,
    t.trip_status
FROM daladala_vehicles v
INNER JOIN gps_locations g ON v.vehicle_id = g.vehicle_id
LEFT JOIN trips t ON v.vehicle_id = t.vehicle_id AND t.trip_status = 'ongoing'
LEFT JOIN routes r ON t.route_id = r.route_id
WHERE v.status = 'active'
AND g.recorded_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
ORDER BY g.recorded_at DESC;

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

CREATE INDEX idx_gps_locations_latest ON gps_locations(vehicle_id, recorded_at DESC);
CREATE INDEX idx_trips_active ON trips(trip_status, start_time);
CREATE INDEX idx_notifications_unread ON notifications(user_id, is_read, created_at DESC);
CREATE INDEX idx_demand_reports_recent ON demand_reports(route_id, reported_at DESC);

-- =============================================
-- END OF DATABASE SCHEMA
-- =============================================