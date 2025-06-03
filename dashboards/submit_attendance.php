<?php
// submit_attendance.php - Script to handle attendance submission
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to record attendance
if (!isLoggedIn() || !hasPermission('record_attendance')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Get form data
$groupCourseId = isset($_POST['group_course_id']) ? intval($_POST['group_course_id']) : 0;
$traineeIds = isset($_POST['trainee_ids']) ? $_POST['trainee_ids'] : [];
$presentHours = isset($_POST['present_hours']) ? $_POST['present_hours'] : [];
$excusedHours = isset($_POST['excused_hours']) ? $_POST['excused_hours'] : [];
$lateHours = isset($_POST['late_hours']) ? $_POST['late_hours'] : [];
$absentHours = isset($_POST['absent_hours']) ? $_POST['absent_hours'] : [];
$takenSessions = isset($_POST['taken_sessions']) ? $_POST['taken_sessions'] : [];
$points = isset($_POST['points']) ? $_POST['points'] : [];
$attendancePercentages = isset($_POST['attendance_percentage']) ? $_POST['attendance_percentage'] : [];

// Validate input
if ($groupCourseId <= 0 || empty($traineeIds) || count($traineeIds) != count($presentHours)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit;
}

// Validate attendance values
foreach ($presentHours as $hours) {
    if (floatval($hours) < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Present hours cannot be negative']);
        exit;
    }
}

foreach ($excusedHours as $hours) {
    if (floatval($hours) < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Excused hours cannot be negative']);
        exit;
    }
}

foreach ($lateHours as $hours) {
    if (floatval($hours) < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Late hours cannot be negative']);
        exit;
    }
}

foreach ($absentHours as $hours) {
    if (floatval($hours) < 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Absent hours cannot be negative']);
        exit;
    }
}

// Allow access to Admin, Super Admin and the assigned instructor
$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Skip verification for Admin and Super Admin
$skipVerification = (in_array($userRole, ['Admin', 'Super Admin']));

if (!$skipVerification) {
    // Verify that this course is assigned to the instructor
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
        echo json_encode(['success' => false, 'message' => 'You are not authorized to access this course']);
        exit;
    }
}

// Begin transaction
$conn->begin_transaction();

try {
    // Prepare statement for inserting/updating attendance records
    $insertQuery = "
        INSERT INTO Attendance (
            TID, GroupCourseID, PresentHours, ExcusedHours, LateHours, AbsentHours, 
            TakenSessions, MoodlePoints, AttendancePercentage, CreatedAt, UpdatedAt
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            PresentHours = VALUES(PresentHours),
            ExcusedHours = VALUES(ExcusedHours),
            LateHours = VALUES(LateHours),
            AbsentHours = VALUES(AbsentHours),
            TakenSessions = VALUES(TakenSessions),
            MoodlePoints = VALUES(MoodlePoints),
            AttendancePercentage = VALUES(AttendancePercentage),
            UpdatedAt = NOW()
    ";
    
    $stmt = $conn->prepare($insertQuery);
    
    // Process each trainee's attendance
    for ($i = 0; $i < count($traineeIds); $i++) {
        $traineeId = intval($traineeIds[$i]);
        $present = floatval($presentHours[$i]);
        $excused = floatval($excusedHours[$i]);
        $late = floatval($lateHours[$i]);
        $absent = floatval($absentHours[$i]);
        $sessions = intval($takenSessions[$i]);
        
        // Calculate the values using formulas rather than using the submitted values
        // Formula 1: Taken Sessions = Present + Excused + Late + Absent
        $calculatedSessions = $present + $excused + $late + $absent;
        $sessions = max(1, round($calculatedSessions)); // Ensure at least 1 session
        
        // Formula 2: Points = 2 * Present + 1 * Excused
        $calculatedPoints = (2 * $present) + (1 * $excused);
        // Ensure points is an integer between 0-10
        $moodlePoints = max(0, min(10, round($calculatedPoints)));
        
        // Formula 3: Percentage = Points / (Taken Sessions * 2) * 100
        $calculatedPercentage = ($calculatedSessions > 0) ? (($calculatedPoints / ($calculatedSessions * 2)) * 100) : 0;
        // Ensure percentage is an integer between 0-100
        $percentage = max(0, min(100, round($calculatedPercentage)));
        
        // Insert/update attendance record
        $stmt->bind_param("iiiiiiiii", 
            $traineeId, 
            $groupCourseId, 
            $present, 
            $excused, 
            $late, 
            $absent, 
            $sessions, 
            $moodlePoints, 
            $percentage
        );
        
        $stmt->execute();
        
        // Update attendance grade in TraineeGrades
        $attGrade = $percentage / 10; // Convert percentage to 10-point scale
        
        // Get the ComponentID for Attendance
        $componentQuery = "SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Attendance'";
        $componentResult = $conn->query($componentQuery);
        
        if ($componentRow = $componentResult->fetch_assoc()) {
            $componentId = $componentRow['ComponentID'];
            
            // Update or insert the attendance grade
            $gradeQuery = "
                INSERT INTO TraineeGrades (TID, GroupCourseID, ComponentID, Score, CreatedAt, UpdatedAt)
                VALUES (?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    Score = VALUES(Score),
                    UpdatedAt = NOW()
            ";
            
            $gradeStmt = $conn->prepare($gradeQuery);
            $gradeStmt->bind_param("iiid", $traineeId, $groupCourseId, $componentId, $attGrade);
            $gradeStmt->execute();
        }
    }
    
// Commit transaction
    $conn->commit();
    
    // Return success JSON response
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Error saving attendance: " . $e->getMessage());
    
    // Return error JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error saving attendance: ' . $e->getMessage()]);
    exit;
}
