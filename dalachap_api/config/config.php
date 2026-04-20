<?php
// =============================================
// File: config/config.php
// Global Configuration File
// =============================================

// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Africa/Dar_es_Salaam');

// JWT Secret Key (for token-based authentication)
define('JWT_SECRET_KEY', 'Dalachap_S3cr3t_K3y_2026_ChangeThisInProduction');

// Token expiry time (hours)
define('TOKEN_EXPIRY_HOURS', 24);

// API Version
define('API_VERSION', 'v1.0');

// Base URL (change according to your setup)
define('BASE_URL', 'http://localhost/dalachap_api/');

// Upload paths
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
?>