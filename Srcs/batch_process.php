<?php
// batch_process.php
require_once "../includes/auth.php"; // For session and permission checks
include_once "../includes/config.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Check if the user has permission to access this page
// Add permission checking based on your RolePermissions table

$formType = $_POST['form_type'] ?? '';

if ($formType === 'batch_attendance') {
    $courseId = $_POST['batch_course_id'] ?? '';
    $date = $_POST['batch_date'] ?? '';
    $traineeIds = $_POST['trainee_ids'] ?? [];
    $statuses = $_POST['statuses'] ?? [];
    $notes = $_POST['notes'] ?? [];
    
    if (empty($courseId) || empty($date) || empty($traineeIds)) {
        $response['message'] = 'Missing required data';
        echo json_encode($response);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        $recordedBy = $_SESSION['user_id'] ?? 1; // Default to 1 if not set
        $stmt = $conn->prepare("INSERT INTO Attendance (TID, CourseID, AttendanceDate, Status, Notes, RecordedBy) VALUES (?, ?, ?, ?, ?, ?)");
        
        $successCount = 0;
        foreach ($traineeIds as $traineeId) {
            $status = $statuses[$traineeId] ?? 'Absent';
            $note = $notes[$traineeId] ?? '';
            
            $stmt->bind_param("sssssi", $traineeId, $courseId, $date, $status, $note, $recordedBy);
            if ($stmt->execute()) {
                $successCount++;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = "Successfully added attendance records for $successCount trainees.";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} elseif ($formType === 'batch_grades') {
    $courseId = $_POST['batch_grade_course_id'] ?? '';
    $componentId = $_POST['batch_component_id'] ?? '';
    $gradeDate = $_POST['batch_grade_date'] ?? '';
    $traineeIds = $_POST['trainee_ids'] ?? [];
    $scores = $_POST['scores'] ?? [];
    $comments = $_POST['comments'] ?? [];
    
    if (empty($courseId) || empty($componentId) || empty($gradeDate) || empty($traineeIds)) {
        $response['message'] = 'Missing required data';
        echo json_encode($response);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        $recordedBy = $_SESSION['user_id'] ?? 1; // Default to 1 if not set
        $successCount = 0;
        
        foreach ($traineeIds as $traineeId) {
            $score = $scores[$traineeId] ?? 0;
            $comment = $comments[$traineeId] ?? '';
            
            // Check if grade already exists
            $checkStmt = $conn->prepare("SELECT GradeID FROM StudentGrades WHERE TID = ? AND CourseID = ? AND ComponentID = ?");
            $checkStmt->bind_param("ssi", $traineeId, $courseId, $componentId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update existing grade
                $gradeId = $checkResult->fetch_assoc()['GradeID'];
                $updateStmt = $conn->prepare("UPDATE StudentGrades SET Score = ?, Comments = ?, GradeDate = ? WHERE GradeID = ?");
                $updateStmt->bind_param("dssi", $score, $comment, $gradeDate, $gradeId);
                
                if ($updateStmt->execute()) {
                    $successCount++;
                }
            } else {
                // Insert new grade
                $insertStmt = $conn->prepare("INSERT INTO StudentGrades (TID, CourseID, ComponentID, Score, Comments, GradeDate, RecordedBy) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("ssidssi", $traineeId, $courseId, $componentId, $score, $comment, $gradeDate, $recordedBy);
                
                if ($insertStmt->execute()) {
                    $successCount++;
                }
            }
            
            // Update LGI if final exam component
            if ($componentId == 12) { // Assuming 12 is the Final Exam component
                $lgiStmt = $conn->prepare("UPDATE LearningGapIndicators SET FinalScore = ? WHERE TID = ? AND CourseID = ?");
                $lgiStmt->bind_param("dss", $score, $traineeId, $courseId);
                $lgiStmt->execute();
                
                // Recalculate LGI
                $lgiCalcStmt = $conn->prepare("UPDATE LearningGapIndicators SET LGIPercentage = CalculateLGI(?, ?) WHERE TID = ? AND CourseID = ?");
                $lgiCalcStmt->bind_param("ssss", $traineeId, $courseId, $traineeId, $courseId);
                $lgiCalcStmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = "Successfully added grade records for $successCount trainees.";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid form type';
}

echo json_encode($response);
