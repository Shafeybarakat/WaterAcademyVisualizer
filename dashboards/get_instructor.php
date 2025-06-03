<?php
// get_instructor.php - Returns instructor details as JSON
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to manage instructors
if (!isLoggedIn() || !hasPermission('view_users')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get instructor ID
$instructorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($instructorId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid instructor ID']);
    exit;
}

// Query the instructor details from the view
$stmt = $conn->prepare("SELECT * FROM vw_Instructors WHERE InstructorID = ?");
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();
$instructor = $result->fetch_assoc();

if (!$instructor) {
    http_response_code(404);
    echo json_encode(['error' => 'Instructor not found']);
    exit;
}

// Return instructor data as JSON
header('Content-Type: application/json');
echo json_encode($instructor);
