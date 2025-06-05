<?php
// API endpoint to get group report data for the group performance report
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Set content type to JSON
header('Content-Type: application/json');

// RBAC guard: Only users with 'access_group_reports' permission can access this page.
if (!hasPermission('access_group_reports')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. You do not have permission to access group reports.']);
    exit;
}

// Get parameters
$groupId = isset($_GET['group_id']) ? $_GET['group_id'] : null;
$courseId = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Validate required parameters
if (!$groupId || !$courseId) {
    echo json_encode(['error' => 'Group ID and Course ID are required']);
    exit;
}

try {
    // Get the GroupCourseID
    $gcStmt = $conn->prepare("SELECT ID FROM GroupCourses WHERE GroupID = ? AND CourseID = ?");
    $gcStmt->bind_param("ii", $groupId, $courseId);
    $gcStmt->execute();
    $gcResult = $gcStmt->get_result();
    
    if ($gcResult->num_rows === 0) {
        echo json_encode(['error' => 'No data found for the specified group and course']);
        exit;
    }
    
    $gcRow = $gcResult->fetch_assoc();
    $groupCourseId = $gcRow['ID'];
    
    // 1. Get summary data
    $summaryStmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT t.TID) AS TraineeCount,
            AVG(vt.AttendancePercentage) AS AvgAttendance,
            AVG(vt.CompositeScore) AS AvgTotal,
            AVG(vt.LGI) AS AvgLGI
        FROM 
            View_TraineePerformanceDetails vt
        JOIN 
            Trainees t ON vt.TID = t.TID
        WHERE 
            vt.GroupCourseID = ?
    ");
    $summaryStmt->bind_param("i", $groupCourseId);
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    $summary = $summaryResult->fetch_assoc();
    
    // 2. Get performance distribution
    $distributionStmt = $conn->prepare("
        SELECT 
            CASE
                WHEN CompositeScore >= 90 THEN '90-100 (A)'
                WHEN CompositeScore >= 80 THEN '80-89 (B)'
                WHEN CompositeScore >= 70 THEN '70-79 (C)'
                WHEN CompositeScore >= 60 THEN '60-69 (D)'
                ELSE '<60 (F)'
            END AS ScoreRange,
            COUNT(*) AS Count
        FROM 
            View_TraineePerformanceDetails
        WHERE 
            GroupCourseID = ?
        GROUP BY 
            CASE
                WHEN CompositeScore >= 90 THEN '90-100 (A)'
                WHEN CompositeScore >= 80 THEN '80-89 (B)'
                WHEN CompositeScore >= 70 THEN '70-79 (C)'
                WHEN CompositeScore >= 60 THEN '60-69 (D)'
                ELSE '<60 (F)'
            END
        ORDER BY 
            ScoreRange DESC
    ");
    $distributionStmt->bind_param("i", $groupCourseId);
    $distributionStmt->execute();
    $distributionResult = $distributionStmt->get_result();
    $distribution = $distributionResult->fetch_all(MYSQLI_ASSOC);
    
    // 3. Get attendance status counts
    $attendanceStmt = $conn->prepare("
        SELECT 
            Status,
            COUNT(*) AS Count
        FROM 
            Attendance
        WHERE 
            GroupCourseID = ?
        GROUP BY 
            Status
        ORDER BY 
            FIELD(Status, 'Present', 'Late', 'Excused', 'Absent')
    ");
    $attendanceStmt->bind_param("i", $groupCourseId);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();
    $attendanceCounts = $attendanceResult->fetch_all(MYSQLI_ASSOC);
    
    // 4. Get trainee details
    $traineeStmt = $conn->prepare("
        SELECT 
            t.TID,
            CONCAT(t.FirstName, ' ', t.LastName) AS TraineeName,
            vt.PreTestScore AS PreTestScore,
            vt.CompositeScore AS TotalScore,
            vt.FinalExamScore AS FinalScore,
            vt.AttendancePercentage,
            vt.AvgQuizScore AS AvgQuizScore
        FROM 
            Trainees t
        JOIN 
            View_TraineePerformanceDetails vt ON t.TID = vt.TID
        WHERE 
            vt.GroupCourseID = ?
        ORDER BY 
            t.LastName, t.FirstName
    ");
    $traineeStmt->bind_param("i", $groupCourseId);
    $traineeStmt->execute();
    $traineeResult = $traineeStmt->get_result();
    $trainees = $traineeResult->fetch_all(MYSQLI_ASSOC);
    
    // Combine all data
    $response = [
        'summary' => $summary,
        'performance_distribution' => $distribution,
        'attendance_status_counts' => $attendanceCounts,
        'trainee_details' => $trainees
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_group_report_data.php: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching report data']);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
