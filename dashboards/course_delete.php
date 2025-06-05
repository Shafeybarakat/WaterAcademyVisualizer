<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

// RBAC guard: Only users with 'manage_courses' permission can access this page.
if (!require_permission('manage_courses', '../login.php')) {
    // Since this is a redirect-only page, we can just redirect to login with an error.
    // If logged in but no permission, redirect to dashboard with error.
    $_SESSION['access_denied_message'] = 'You do not have permission to delete courses.';
    header("Location: courses.php"); // Redirect to a relevant dashboard
    exit;
}

// Get course ID from URL
$courseId = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($courseId)) {
    $_SESSION['course_error'] = "No course ID provided for deletion.";
    header("Location: course_dashboard.php");
    exit;
}

// Check if course exists
$checkQuery = "SELECT CourseID, CourseName FROM Courses WHERE CourseID = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $courseId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$course = $checkResult->fetch_assoc();

if (!$course) {
    $_SESSION['course_error'] = "Course not found.";
    header("Location: course_dashboard.php");
    exit;
}

// Check for associated records before deletion
$enrollmentsQuery = "SELECT COUNT(*) as count FROM Enrollments WHERE CourseID = ?";
$enrollmentsStmt = $conn->prepare($enrollmentsQuery);
$enrollmentsStmt->bind_param("s", $courseId);
$enrollmentsStmt->execute();
$enrollmentsResult = $enrollmentsStmt->get_result();
$enrollmentsCount = $enrollmentsResult->fetch_assoc()['count'];

$gradesQuery = "SELECT COUNT(*) as count FROM TraineeGrades WHERE CourseID = ?";
$gradesStmt = $conn->prepare($gradesQuery);
$gradesStmt->bind_param("s", $courseId);
$gradesStmt->execute();
$gradesResult = $gradesStmt->get_result();
$gradesCount = $gradesResult->fetch_assoc()['count'];

$attendanceQuery = "SELECT COUNT(*) as count FROM Attendance WHERE CourseID = ?";
$attendanceStmt = $conn->prepare($attendanceQuery);
$attendanceStmt->bind_param("s", $courseId);
$attendanceStmt->execute();
$attendanceResult = $attendanceStmt->get_result();
$attendanceCount = $attendanceResult->fetch_assoc()['count'];

$lgiQuery = "SELECT COUNT(*) as count FROM LearningGapIndicators WHERE CourseID = ?";
$lgiStmt = $conn->prepare($lgiQuery);
$lgiStmt->bind_param("s", $courseId);
$lgiStmt->execute();
$lgiResult = $lgiStmt->get_result();
$lgiCount = $lgiResult->fetch_assoc()['count'];

// If there are associated records, show error
if ($enrollmentsCount > 0 || $gradesCount > 0 || $attendanceCount > 0) {
    $_SESSION['course_error'] = "Cannot delete course '{$course['CourseName']}' because it has associated data. 
                               Found: " . 
                               ($enrollmentsCount > 0 ? "$enrollmentsCount enrollments, " : "") . 
                               ($gradesCount > 0 ? "$gradesCount grades, " : "") . 
                               ($attendanceCount > 0 ? "$attendanceCount attendance records" : "");
    
    header("Location: course_dashboard.php");
    exit;
}

// Perform the deletion
$deleteQuery = "DELETE FROM Courses WHERE CourseID = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("s", $courseId);

if ($deleteStmt->execute()) {
    $_SESSION['course_success'] = "Course '{$course['CourseName']}' has been deleted successfully.";
} else {
    $_SESSION['course_error'] = "Error deleting course: " . $conn->error;
}

header("Location: course_dashboard.php");
exit;
