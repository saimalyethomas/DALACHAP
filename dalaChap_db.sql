-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2026 at 11:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dalachap_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `associations`
--

CREATE TABLE `associations` (
  `association_id` int(11) NOT NULL,
  `association_name` varchar(100) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `chairman_name` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associations`
--

INSERT INTO `associations` (`association_id`, `association_name`, `registration_number`, `address`, `phone_number`, `email`, `chairman_name`, `status`, `created_at`) VALUES
(1, 'Dar Rapid Transit Association', 'DRTA001', NULL, '0734567890', 'info@drta.co.tz', 'Mr. Hassan Juma', 'active', '2026-04-14 08:54:38'),
(2, 'Ubungo Daladala Owners', 'UDO002', NULL, '0745678901', 'ubungo@daladala.co.tz', 'Mrs. Fatma Omar', 'active', '2026-04-14 08:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `daladala_vehicles`
--

CREATE TABLE `daladala_vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_phone` varchar(15) NOT NULL,
  `capacity` int(11) DEFAULT 30,
  `association_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','maintenance','suspended') DEFAULT 'active',
  `last_maintenance_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daladala_vehicles`
--

INSERT INTO `daladala_vehicles` (`vehicle_id`, `registration_number`, `owner_name`, `owner_phone`, `capacity`, `association_id`, `status`, `last_maintenance_date`, `created_at`) VALUES
(1, 'T123ABC', 'Hamza Mohamed', '0756789012', 30, 1, 'active', NULL, '2026-04-14 08:54:38'),
(2, 'T456DEF', 'Aisha Salim', '0767890123', 32, 1, 'active', NULL, '2026-04-14 08:54:38'),
(3, 'T789GHI', 'Juma Hassan', '0778901234', 30, 2, 'active', NULL, '2026-04-14 08:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `demand_reports`
--

CREATE TABLE `demand_reports` (
  `report_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_id` int(11) DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `passenger_waiting_count` int(11) NOT NULL,
  `estimated_wait_time_minutes` int(11) DEFAULT NULL,
  `report_type` enum('high_demand','low_demand','overcrowded','normal') DEFAULT 'normal',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_assignments`
--

CREATE TABLE `driver_assignments` (
  `assignment_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `rating` int(1) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `feedback_type` enum('complaint','suggestion','compliment','general') DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gps_locations`
--

CREATE TABLE `gps_locations` (
  `location_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `speed_kmh` decimal(10,2) DEFAULT NULL,
  `heading` int(11) DEFAULT NULL,
  `recorded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('demand_alert','route_change','authorization','system','general') DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL,
  `route_code` varchar(20) NOT NULL,
  `route_name` varchar(100) NOT NULL,
  `starting_point` varchar(100) NOT NULL,
  `ending_point` varchar(100) NOT NULL,
  `distance_km` decimal(10,2) NOT NULL,
  `estimated_duration_minutes` int(11) NOT NULL,
  `base_fare` decimal(10,2) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `route_code`, `route_name`, `starting_point`, `ending_point`, `distance_km`, `estimated_duration_minutes`, `base_fare`, `status`, `created_by`, `created_at`) VALUES
(1, 'R001', 'Ubungo - Kivukoni', 'Ubungo Terminal', 'Kivukoni', 15.50, 45, 700.00, 'active', NULL, '2026-04-14 08:54:38'),
(2, 'R002', 'Gongo la Mboto - Posta', 'Gongo la Mboto', 'Posta', 22.00, 60, 1000.00, 'active', NULL, '2026-04-14 08:54:38'),
(3, 'R003', 'Kimara - Mwenge', 'Kimara', 'Mwenge', 8.50, 25, 500.00, 'active', NULL, '2026-04-14 08:54:38'),
(4, 'R004', 'Mbagala - Kariakoo', 'Mbagala', 'Kariakoo', 18.00, 55, 800.00, 'active', NULL, '2026-04-14 08:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `route_authorizations`
--

CREATE TABLE `route_authorizations` (
  `authorization_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `original_route_id` int(11) NOT NULL,
  `temporary_route_id` int(11) DEFAULT NULL,
  `authorized_by` int(11) NOT NULL,
  `reason` text NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('active','expired','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `route_stops`
--

CREATE TABLE `route_stops` (
  `stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_name` varchar(100) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `estimated_arrival_minutes` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_stops`
--

INSERT INTO `route_stops` (`stop_id`, `route_id`, `stop_name`, `stop_order`, `latitude`, `longitude`, `estimated_arrival_minutes`) VALUES
(1, 1, 'Ubungo Terminal', 1, NULL, NULL, 0),
(2, 1, 'Ubungo Mwenge', 2, NULL, NULL, 10),
(3, 1, 'Morocco', 3, NULL, NULL, 20),
(4, 1, 'Mwenge', 4, NULL, NULL, 25),
(5, 1, 'Magomeni', 5, NULL, NULL, 35),
(6, 1, 'Posta', 6, NULL, NULL, 40),
(7, 1, 'Kivukoni', 7, NULL, NULL, 45);

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'REGISTER', 'New user registered', '127.0.0.1', NULL, '2026-04-24 21:21:01'),
(2, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-24 21:23:31'),
(3, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-24 21:27:23'),
(4, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-24 21:28:39'),
(5, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-27 10:33:05'),
(6, 5, 'REGISTER', 'New user registered', '127.0.0.1', NULL, '2026-04-28 03:28:58'),
(7, 5, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 03:29:16'),
(8, 5, 'LOGOUT', 'User logged out', '127.0.0.1', NULL, '2026-04-28 03:29:55'),
(9, 5, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 03:31:11'),
(10, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 03:31:35'),
(11, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 03:43:34'),
(12, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 03:50:07'),
(13, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 04:15:32'),
(14, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 04:43:50'),
(15, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 05:02:30'),
(16, 4, 'LOGIN', 'User logged in', '127.0.0.1', NULL, '2026-04-28 18:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `trip_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `start_latitude` decimal(10,8) DEFAULT NULL,
  `start_longitude` decimal(11,8) DEFAULT NULL,
  `end_latitude` decimal(10,8) DEFAULT NULL,
  `end_longitude` decimal(11,8) DEFAULT NULL,
  `passenger_count` int(11) DEFAULT 0,
  `trip_status` enum('ongoing','completed','cancelled') DEFAULT 'ongoing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_role` enum('admin','traffic_officer','association_leader','driver','passenger') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone_number`, `password_hash`, `user_role`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'Theresia Luambano', 'theresialuamban@gmail.com', '0711111111', '$2y$10$oT2Cl6qgP94CMUG8UGrNXeok0XkEjaExm2fMGB79t78ue.M.McJ9m', 'admin', NULL, 1, '2026-04-24 21:21:01', '2026-04-24 21:22:00'),
(5, 'Test User', 'test@example.com', '0712345678', '$2y$10$x5Jncylmod896W4BNUk9DOV8Lw2hgB8UnCcSGvrSq7egRhI6CtfjK', 'passenger', NULL, 1, '2026-04-28 03:28:58', '2026-04-28 03:28:58');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_active_trips`
-- (See below for the actual view)
--
CREATE TABLE `vw_active_trips` (
`trip_id` int(11)
,`registration_number` varchar(20)
,`driver_name` varchar(100)
,`route_name` varchar(100)
,`start_time` datetime
,`duration_minutes` bigint(21)
,`passenger_count` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_current_vehicle_locations`
-- (See below for the actual view)
--
CREATE TABLE `vw_current_vehicle_locations` (
`vehicle_id` int(11)
,`registration_number` varchar(20)
,`latitude` decimal(10,8)
,`longitude` decimal(11,8)
,`speed_kmh` decimal(10,2)
,`recorded_at` datetime
,`route_name` varchar(100)
,`trip_status` enum('ongoing','completed','cancelled')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_route_demand_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_route_demand_summary` (
`route_id` int(11)
,`route_name` varchar(100)
,`report_count` bigint(21)
,`avg_waiting_passengers` decimal(14,4)
,`last_report_time` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `vw_active_trips`
--
DROP TABLE IF EXISTS `vw_active_trips`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_active_trips`  AS SELECT `t`.`trip_id` AS `trip_id`, `v`.`registration_number` AS `registration_number`, `u`.`full_name` AS `driver_name`, `r`.`route_name` AS `route_name`, `t`.`start_time` AS `start_time`, timestampdiff(MINUTE,`t`.`start_time`,current_timestamp()) AS `duration_minutes`, `t`.`passenger_count` AS `passenger_count` FROM (((`trips` `t` join `daladala_vehicles` `v` on(`t`.`vehicle_id` = `v`.`vehicle_id`)) join `users` `u` on(`t`.`driver_id` = `u`.`user_id`)) join `routes` `r` on(`t`.`route_id` = `r`.`route_id`)) WHERE `t`.`trip_status` = 'ongoing' ;

-- --------------------------------------------------------

--
-- Structure for view `vw_current_vehicle_locations`
--
DROP TABLE IF EXISTS `vw_current_vehicle_locations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_current_vehicle_locations`  AS SELECT `v`.`vehicle_id` AS `vehicle_id`, `v`.`registration_number` AS `registration_number`, `g`.`latitude` AS `latitude`, `g`.`longitude` AS `longitude`, `g`.`speed_kmh` AS `speed_kmh`, `g`.`recorded_at` AS `recorded_at`, `r`.`route_name` AS `route_name`, `t`.`trip_status` AS `trip_status` FROM (((`daladala_vehicles` `v` join `gps_locations` `g` on(`v`.`vehicle_id` = `g`.`vehicle_id`)) left join `trips` `t` on(`v`.`vehicle_id` = `t`.`vehicle_id` and `t`.`trip_status` = 'ongoing')) left join `routes` `r` on(`t`.`route_id` = `r`.`route_id`)) WHERE `v`.`status` = 'active' AND `g`.`recorded_at` >= current_timestamp() - interval 5 minute ORDER BY `g`.`recorded_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_route_demand_summary`
--
DROP TABLE IF EXISTS `vw_route_demand_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_route_demand_summary`  AS SELECT `r`.`route_id` AS `route_id`, `r`.`route_name` AS `route_name`, count(`d`.`report_id`) AS `report_count`, avg(`d`.`passenger_waiting_count`) AS `avg_waiting_passengers`, max(`d`.`reported_at`) AS `last_report_time` FROM (`routes` `r` left join `demand_reports` `d` on(`r`.`route_id` = `d`.`route_id`)) WHERE `d`.`reported_at` >= current_timestamp() - interval 1 hour GROUP BY `r`.`route_id`, `r`.`route_name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `associations`
--
ALTER TABLE `associations`
  ADD PRIMARY KEY (`association_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `idx_name` (`association_name`);

--
-- Indexes for table `daladala_vehicles`
--
ALTER TABLE `daladala_vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `association_id` (`association_id`),
  ADD KEY `idx_registration` (`registration_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `demand_reports`
--
ALTER TABLE `demand_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `stop_id` (`stop_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `idx_route_demand` (`route_id`,`reported_at`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_demand_reports_recent` (`route_id`,`reported_at`);

--
-- Indexes for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `idx_current_driver` (`driver_id`,`is_current`),
  ADD KEY `idx_vehicle` (`vehicle_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `gps_locations`
--
ALTER TABLE `gps_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_vehicle_time` (`vehicle_id`,`recorded_at`),
  ADD KEY `idx_trip` (`trip_id`),
  ADD KEY `idx_gps_locations_latest` (`vehicle_id`,`recorded_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_notifications_unread` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `route_code` (`route_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_route_code` (`route_code`);

--
-- Indexes for table `route_authorizations`
--
ALTER TABLE `route_authorizations`
  ADD PRIMARY KEY (`authorization_id`),
  ADD KEY `original_route_id` (`original_route_id`),
  ADD KEY `temporary_route_id` (`temporary_route_id`),
  ADD KEY `authorized_by` (`authorized_by`),
  ADD KEY `idx_active_auth` (`status`,`start_datetime`,`end_datetime`),
  ADD KEY `idx_vehicle` (`vehicle_id`);

--
-- Indexes for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `idx_route_stops` (`route_id`,`stop_order`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_action` (`user_id`,`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `idx_trip_status` (`trip_status`),
  ADD KEY `idx_start_time` (`start_time`),
  ADD KEY `idx_driver` (`driver_id`),
  ADD KEY `idx_vehicle` (`vehicle_id`),
  ADD KEY `idx_trips_active` (`trip_status`,`start_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`user_role`),
  ADD KEY `idx_phone` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `associations`
--
ALTER TABLE `associations`
  MODIFY `association_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `daladala_vehicles`
--
ALTER TABLE `daladala_vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `demand_reports`
--
ALTER TABLE `demand_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_locations`
--
ALTER TABLE `gps_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `route_authorizations`
--
ALTER TABLE `route_authorizations`
  MODIFY `authorization_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `trip_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daladala_vehicles`
--
ALTER TABLE `daladala_vehicles`
  ADD CONSTRAINT `daladala_vehicles_ibfk_1` FOREIGN KEY (`association_id`) REFERENCES `associations` (`association_id`) ON DELETE SET NULL;

--
-- Constraints for table `demand_reports`
--
ALTER TABLE `demand_reports`
  ADD CONSTRAINT `demand_reports_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demand_reports_ibfk_2` FOREIGN KEY (`stop_id`) REFERENCES `route_stops` (`stop_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `demand_reports_ibfk_3` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD CONSTRAINT `driver_assignments_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_assignments_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `daladala_vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_assignments_ibfk_3` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`) ON DELETE SET NULL;

--
-- Constraints for table `gps_locations`
--
ALTER TABLE `gps_locations`
  ADD CONSTRAINT `gps_locations_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `daladala_vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gps_locations_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `route_authorizations`
--
ALTER TABLE `route_authorizations`
  ADD CONSTRAINT `route_authorizations_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `daladala_vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `route_authorizations_ibfk_2` FOREIGN KEY (`original_route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `route_authorizations_ibfk_3` FOREIGN KEY (`temporary_route_id`) REFERENCES `routes` (`route_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `route_authorizations_ibfk_4` FOREIGN KEY (`authorized_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD CONSTRAINT `route_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `daladala_vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trips_ibfk_3` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;