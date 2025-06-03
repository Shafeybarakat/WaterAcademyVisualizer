<?php
include_once "../includes/config.php";
include_once "../includes/auth.php"; // For session and permission checks if necessary

// Get course ID
$courseId = $_GET['course_id'] ?? '';

// Get course defaults
$courseQuery = "SELECT BaseDurationWeeks, BaseTotalHours FROM Courses WHERE CourseID = ?";
$stmt = $conn->prepare($courseQuery);
$stmt->bind_param("s", $courseId);
$stmt->execute();
$result = $stmt->get_result();

$response = [
    'duration_weeks' => 4, // Default values if not found
    'total_hours' => 40
];

if ($course = $result->fetch_assoc()) {
    $response['duration_weeks'] = $course['BaseDurationWeeks'] ?? 4;
    $response['total_hours'] = $course['BaseTotalHours'] ?? 40;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>