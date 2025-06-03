<?php
// submit_grades.php - Script to handle grade submission
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has permission to record grades
if (!isLoggedIn() || !hasPermission('record_grades')) {
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
$pretests = isset($_POST['pretest']) ? $_POST['pretest'] : [];
$attGrades = isset($_POST['att_grade']) ? $_POST['att_grade'] : [];
$participations = isset($_POST['participation']) ? $_POST['participation'] : [];
$quiz1s = isset($_POST['quiz1']) ? $_POST['quiz1'] : [];
$quiz2s = isset($_POST['quiz2']) ? $_POST['quiz2'] : [];
$quiz3s = isset($_POST['quiz3']) ? $_POST['quiz3'] : [];
$quizAvgs = isset($_POST['quiz_avg']) ? $_POST['quiz_avg'] : [];
$finalExams = isset($_POST['final_exam']) ? $_POST['final_exam'] : [];
$courseTotals = isset($_POST['course_total']) ? $_POST['course_total'] : [];
$positiveFeedbacks = isset($_POST['positive_feedback']) ? $_POST['positive_feedback'] : [];
$areasToImprove = isset($_POST['areas_to_improve']) ? $_POST['areas_to_improve'] : [];

// Validate input
if ($groupCourseId <= 0 || empty($traineeIds) || count($traineeIds) != count($participations)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit;
}

// Validate maximum values for each grade component
$errors = [];

// Validate pre-test values (max 50)
foreach ($pretests as $idx => $value) {
    if ($value !== '' && (floatval($value) < 0 || floatval($value) > 50)) {
        $errors[] = "Pre-Test values must be between 0 and 50";
        break;
    }
}

// Validate participation values (max 10)
foreach ($participations as $idx => $value) {
    if ($value !== '' && (floatval($value) < 0 || floatval($value) > 10)) {
        $errors[] = "Participation values must be between 0 and 10";
        break;
    }
}

// Validate quiz values (max 30)
foreach ([$quiz1s, $quiz2s, $quiz3s] as $quizArray) {
    foreach ($quizArray as $idx => $value) {
        if ($value !== '' && (floatval($value) < 0 || floatval($value) > 30)) {
            $errors[] = "Quiz values must be between 0 and 30";
            break 2;
        }
    }
}

// Validate final exam values (max 50)
foreach ($finalExams as $idx => $value) {
    if ($value !== '' && (floatval($value) < 0 || floatval($value) > 50)) {
        $errors[] = "Final Exam values must be between 0 and 50";
        break;
    }
}

if (!empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
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

// Get component IDs
$componentIds = [];
// Only require components that are actually stored in the database
$requiredComponents = ['Pre-Test', 'Participation', 'Quiz 1', 'Quiz 2', 'Quiz 3', 'Final Exam'];
$componentQuery = "SELECT ComponentID, ComponentName FROM GradeComponents";
$componentResult = $conn->query($componentQuery);

while ($componentRow = $componentResult->fetch_assoc()) {
    $componentIds[$componentRow['ComponentName']] = $componentRow['ComponentID'];
}

// Check if all required components exist
$missingComponents = [];
foreach ($requiredComponents as $component) {
    if (!isset($componentIds[$component])) {
        $missingComponents[] = $component;
    }
}

if (!empty($missingComponents)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'The following grade components are missing from the database: ' . implode(', ', $missingComponents)
    ]);
    exit;
}

// Add calculated component IDs as null so they won't be stored
$componentIds['Quiz Avg'] = null;
$componentIds['Course Total'] = null;
$componentIds['Learning Gain Index'] = null;

// Begin transaction
$conn->begin_transaction();

try {
    // Prepare statement for inserting/updating grade records
    $insertQuery = "
        INSERT INTO TraineeGrades (TID, GroupCourseID, ComponentID, Score, CreatedAt, UpdatedAt)
        VALUES (?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            Score = VALUES(Score),
            UpdatedAt = NOW()
    ";
    
    $stmt = $conn->prepare($insertQuery);
    
    // Process each trainee's grades
    for ($i = 0; $i < count($traineeIds); $i++) {
        $traineeId = intval($traineeIds[$i]);
        
        // Process Pre-Test
        if (isset($pretests[$i]) && $pretests[$i] !== '') {
            $pretest = floatval($pretests[$i]);
            $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Pre-Test'], $pretest);
            $stmt->execute();
        }
        
        // Process Participation
        $participation = floatval($participations[$i]);
        $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Participation'], $participation);
        $stmt->execute();
        
        // Process Quiz 1
        $quiz1 = floatval($quiz1s[$i]);
        $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Quiz 1'], $quiz1);
        $stmt->execute();
        
        // Process Quiz 2 (if provided)
        if (isset($quiz2s[$i]) && $quiz2s[$i] !== '') {
            $quiz2 = floatval($quiz2s[$i]);
            $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Quiz 2'], $quiz2);
            $stmt->execute();
        }
        
        // Process Quiz 3 (if provided)
        if (isset($quiz3s[$i]) && $quiz3s[$i] !== '') {
            $quiz3 = floatval($quiz3s[$i]);
            $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Quiz 3'], $quiz3);
            $stmt->execute();
        }
        
        // Calculate Quiz Average
        $quizCount = 1; // Quiz 1 is mandatory
        $quizSum = $quiz1;
        
        if (isset($quiz2s[$i]) && $quiz2s[$i] !== '') {
            $quizCount++;
            $quizSum += floatval($quiz2s[$i]);
        }
        
        if (isset($quiz3s[$i]) && $quiz3s[$i] !== '') {
            $quizCount++;
            $quizSum += floatval($quiz3s[$i]);
        }
        
        $quizAvg = $quizSum / $quizCount;
        
        // Quiz Average is calculated but not stored (null component ID)
        // We'll still calculate it for use in the course total
        
        // Process Final Exam
        $finalExam = floatval($finalExams[$i]);
        $stmt->bind_param("iiid", $traineeId, $groupCourseId, $componentIds['Final Exam'], $finalExam);
        $stmt->execute();
        
        // Process PositiveFeedback and AreasToImprove for Final Exam component
        // We'll use a separate query for text fields
        $feedbackQuery = "
            UPDATE TraineeGrades 
            SET PositiveFeedback = ?, AreasToImprove = ?
            WHERE TID = ? AND GroupCourseID = ? AND ComponentID = ?
        ";
        $feedbackStmt = $conn->prepare($feedbackQuery);
        $positiveFeedback = isset($positiveFeedbacks[$i]) ? $positiveFeedbacks[$i] : '';
        $areaToImprove = isset($areasToImprove[$i]) ? $areasToImprove[$i] : '';
        $finalExamComponentId = $componentIds['Final Exam'];
        $feedbackStmt->bind_param("ssiii", $positiveFeedback, $areaToImprove, $traineeId, $groupCourseId, $finalExamComponentId);
        $feedbackStmt->execute();
        $feedbackStmt->close();
        
        // Calculate Course Total
        // Course Total = Attendance (10) + Participation (10) + Quiz Avg (30) + Final Exam (50)
        $attGrade = isset($attGrades[$i]) && $attGrades[$i] !== '' ? round(floatval($attGrades[$i]), 1) : 0;
        $courseTotal = $attGrade + $participation + $quizAvg + $finalExam;
        
        // Course Total is calculated but not stored (null component ID)
        
        // Calculate Learning Gain Index (LGI) if Pre-Test is available
        if (isset($pretests[$i]) && $pretests[$i] !== '' && $pretests[$i] < 100) {
            $pretest = floatval($pretests[$i]);
            $lgi = ((($courseTotal - $pretest) / (100 - $pretest)) * 100);
            
            // Learning Gain Index is calculated but not stored (null component ID)
        }
    }
    
// Commit transaction
    $conn->commit();
    
    // Return success JSON response
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    echo json_encode(['success' => true, 'message' => 'Grades saved successfully']);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Error saving grades: " . $e->getMessage());
    
    // Return error JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error saving grades: ' . $e->getMessage()]);
    exit;
}
?>
