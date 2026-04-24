<?php
// =============================================
// API: Submit Passenger Feedback
// Endpoint: POST /api/passengers/submit_feedback.php
// Body: { "trip_id": 5, "rating": 4, "comment": "Good service", "feedback_type": "compliment" }
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/validation.php';
require_once '../../includes/auth.php';

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate (passengers only)
$user = requireRole($conn, ['passenger', 'driver', 'admin']);

$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['rating']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$trip_id = isset($input['trip_id']) ? intval($input['trip_id']) : null;
$rating = intval($input['rating']);
$comment = isset($input['comment']) ? sanitizeInput($input['comment']) : null;
$feedback_type = isset($input['feedback_type']) ? sanitizeInput($input['feedback_type']) : 'general';

// Validate rating
if ($rating < 1 || $rating > 5) {
    sendValidationError(['rating' => 'Rating must be between 1 and 5']);
}

// Validate feedback type
$allowed_types = ['complaint', 'suggestion', 'compliment', 'general'];
if (!in_array($feedback_type, $allowed_types)) {
    sendValidationError(['feedback_type' => 'Invalid feedback type']);
}

// Insert feedback
$insert_sql = "INSERT INTO feedback (user_id, trip_id, rating, comment, feedback_type) 
               VALUES (?, ?, ?, ?, ?)";
$result = $database->executeQuery($insert_sql, 
    [$user['user_id'], $trip_id, $rating, $comment, $feedback_type], 
    "iiiss"
);

if ($result['success']) {
    sendSuccessResponse([
        "feedback_id" => $result['insert_id']
    ], "Thank you for your feedback!");
} else {
    sendErrorResponse("Failed to submit feedback", 500);
}
?>