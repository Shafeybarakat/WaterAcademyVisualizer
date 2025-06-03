<?php
// ajax_get_courses_by_group.php - AJAX endpoint to get courses for a group
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get group ID from request
$groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($groupId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid group ID']);
    exit;
}

// Get courses for this group
$coursesQuery = "
    SELECT 
        c.CourseID, 
        c.CourseName
    FROM GroupCourses gc
    JOIN Courses c ON gc.CourseID = c.CourseID
    WHERE gc.GroupID = ?
    ORDER BY c.CourseName
";

$stmt = $conn->prepare($coursesQuery);
$stmt->bind_param("i", $groupId);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Return JSON response with courses array wrapped in an object
header('Content-Type: application/json');
echo json_encode(['courses' => $courses, 'success' => true]);
