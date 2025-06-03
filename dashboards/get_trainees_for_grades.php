<?php
// get_trainees_for_grades.php - AJAX endpoint to get trainees for grade entry
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to record grades
if (!isLoggedIn() || !hasPermission('record_grades')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Get group course ID from request
$groupCourseId = isset($_GET['group_course_id']) ? intval($_GET['group_course_id']) : 0;

if ($groupCourseId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid group course ID']);
    exit;
}

// Allow access to Admin, Super Admin and the assigned instructor
$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Skip verification for Admin and Super Admin
$skipVerification = (in_array($userRole, ['Admin', 'Super Admin']));

if (!$skipVerification) {
    // For instructors, verify that this course is assigned to them
    $verifyQuery = "
        SELECT COUNT(*) as count
        FROM GroupCourses
        WHERE ID = ? AND InstructorID = ?
    ";
    
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bind_param("ii", $groupCourseId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'You are not authorized to access this course']);
        exit;
    }
}

// Log successful access
error_log("User {$userId} ({$userRole}) accessing grades data for GroupCourseID: {$groupCourseId}");

// Get trainees for this course with their grades
$traineesQuery = "
    SELECT 
        t.TID, 
        t.FirstName, 
        t.LastName, 
        t.GovID,
        a.AttendancePercentage / 10 as AttGrade,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Pre-Test')) as PreTest,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Participation')) as Participation,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Quiz 1')) as Quiz1,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Quiz 2')) as Quiz2,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Quiz 3')) as Quiz3,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Quiz Avg')) as QuizAvg,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Final Exam')) as FinalExam,
        (SELECT Score FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Course Total')) as CourseTotal,
        (SELECT PositiveFeedback FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Final Exam')) as PositiveFeedback,
        (SELECT AreasToImprove FROM TraineeGrades WHERE TID = t.TID AND GroupCourseID = gc.ID AND ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Final Exam')) as AreasToImprove
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    JOIN Trainees t ON t.GroupID = g.GroupID
    LEFT JOIN Attendance a ON a.TID = t.TID AND a.GroupCourseID = gc.ID
    WHERE gc.ID = ?
    ORDER BY t.FirstName, t.LastName
";

$stmt = $conn->prepare($traineesQuery);
$stmt->bind_param("i", $groupCourseId);
$stmt->execute();
$result = $stmt->get_result();

$trainees = [];
while ($row = $result->fetch_assoc()) {
    $trainees[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($trainees);
