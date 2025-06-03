<?php
// dashboards/get_group.php - Retrieve group details for the edit modal

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in and has permission to view groups
if (!isLoggedIn() || !hasPermission('view_groups')) {
    http_response_code(403);
    exit('Access denied');
}

// Get the group ID from the request
$groupId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$groupId) {
    http_response_code(400);
    exit('Invalid group ID');
}

// Query to get group details
$stmt = $conn->prepare("
    SELECT 
        GroupID AS id,
        GroupName AS name,
        Description AS description,
        StartDate AS start_date,
        EndDate AS end_date,
        Room AS room_number
    FROM `Groups`
    WHERE GroupID = ?
");

$stmt->bind_param('i', $groupId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $group = $result->fetch_assoc();
    
    // Query to get courses attached to this group
    $coursesStmt = $conn->prepare("
        SELECT 
            gc.ID AS group_course_id,
            gc.CourseID,
            c.CourseName,
            c.CourseCode,
            gc.InstructorID,
            CONCAT(u.FirstName, ' ', u.LastName) AS InstructorName,
            gc.StartDate AS course_start_date,
            gc.EndDate AS course_end_date,
            gc.Location,
            gc.ScheduleDetails,
            gc.Status
        FROM GroupCourses gc
        JOIN Courses c ON gc.CourseID = c.CourseID
        LEFT JOIN Users u ON gc.InstructorID = u.UserID
        WHERE gc.GroupID = ?
        ORDER BY gc.StartDate
    ");
    
    $coursesStmt->bind_param('i', $groupId);
    $coursesStmt->execute();
    $coursesResult = $coursesStmt->get_result();
    
    $courses = [];
    while ($course = $coursesResult->fetch_assoc()) {
        $courses[] = $course;
    }
    
    // Add courses to the group data
    $group['courses'] = $courses;
    
    // Get all available courses for dropdown
    $allCoursesQuery = "
        SELECT 
            CourseID, 
            CourseName, 
            CourseCode
        FROM Courses 
        WHERE Status = 'Active'
        ORDER BY CourseName
    ";
    $allCoursesResult = $conn->query($allCoursesQuery);
    
    $allCourses = [];
    if ($allCoursesResult) {
        while ($course = $allCoursesResult->fetch_assoc()) {
            $allCourses[] = $course;
        }
    }
    
    // Get all instructors for dropdown
    $instructorsQuery = "
        SELECT 
            UserID, 
            CONCAT(FirstName, ' ', LastName) AS InstructorName
        FROM Users 
        WHERE RoleID = (SELECT RoleID FROM Roles WHERE RoleName = 'Instructor') AND IsActive = 1
        ORDER BY LastName, FirstName
    ";
    $instructorsResult = $conn->query($instructorsQuery);
    
    $instructors = [];
    if ($instructorsResult) {
        while ($instructor = $instructorsResult->fetch_assoc()) {
            $instructors[] = $instructor;
        }
    }
    
    // Add available courses and instructors to the response
    $group['available_courses'] = $allCourses;
    $group['available_instructors'] = $instructors;
    
    // Format dates to dd/mm/yyyy format
    function formatDateToDDMMYYYY($dateStr) {
        if (empty($dateStr)) return '';
        $date = new DateTime($dateStr);
        return $date->format('d/m/Y');
    }
    
    // Format group dates
    $group['start_date'] = formatDateToDDMMYYYY($group['start_date']);
    $group['end_date'] = formatDateToDDMMYYYY($group['end_date']);
    
    // Format course dates
    foreach ($group['courses'] as &$course) {
        $course['course_start_date'] = formatDateToDDMMYYYY($course['course_start_date']);
        $course['course_end_date'] = formatDateToDDMMYYYY($course['course_end_date']);
    }
    
    // Return the group data as JSON
    header('Content-Type: application/json');
    echo json_encode($group);
} else {
    http_response_code(404);
    exit('Group not found');
}
