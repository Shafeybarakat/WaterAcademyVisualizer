<?php
// get_instructor_courses.php - AJAX endpoint to get courses for an instructor
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to view courses
if (!isLoggedIn() || !hasPermission('view_courses')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Get group ID from request
$groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($groupId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid group ID']);
    exit;
}

// Query for courses assigned to this instructor for the specified group
$coursesQuery = "
    SELECT gc.ID as GroupCourseID, c.CourseID, c.CourseName
    FROM GroupCourses gc
    JOIN Courses c ON gc.CourseID = c.CourseID
    WHERE gc.InstructorID = ? AND gc.GroupID = ?
    ORDER BY c.CourseName
";

$stmt = $conn->prepare($coursesQuery);
$stmt->bind_param("ii", $instructorId, $groupId);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($courses);
