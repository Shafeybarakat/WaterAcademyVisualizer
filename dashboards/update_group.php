<?php
// dashboards/update_group.php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// We expect a POST from the modal form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Start a transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Function to convert date from dd/mm/yyyy to yyyy-mm-dd
    function formatDateToYYYYMMDD($dateStr) {
        if (empty($dateStr)) return '';
        
        // Check if the date is already in yyyy-mm-dd format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }
        
        // Convert from dd/mm/yyyy to yyyy-mm-dd
        $parts = explode('/', $dateStr);
        if (count($parts) === 3) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        
        return $dateStr; // Return as is if format is unknown
    }
    
    // Validate & sanitize group data
    $gid         = filter_input(INPUT_POST, 'group_id',       FILTER_VALIDATE_INT);
    $name        = trim(filter_input(INPUT_POST, 'name',       FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description',FILTER_SANITIZE_STRING));
    $startDate   = formatDateToYYYYMMDD(filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING));
    $endDate     = formatDateToYYYYMMDD(filter_input(INPUT_POST, 'end_date',   FILTER_SANITIZE_STRING));
    $room        = trim(filter_input(INPUT_POST, 'room_number',FILTER_SANITIZE_STRING));

    if (!$gid || !$name || !$startDate || !$endDate) {
        throw new Exception('Missing required group fields');
    }

    // Update the group record
    $stmt = $conn->prepare("
      UPDATE `Groups`
         SET GroupName   = ?,
             Description = ?,
             StartDate   = ?,
             EndDate     = ?,
             Room        = ?
       WHERE GroupID    = ?
    ");
    $stmt->bind_param(
        "sssssi",
        $name,
        $description,
        $startDate,
        $endDate,
        $room,
        $gid
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to update group: ' . $stmt->error);
    }

    // Process course deletions if any
    if (isset($_POST['delete_courses']) && is_array($_POST['delete_courses'])) {
        $deleteStmt = $conn->prepare("DELETE FROM GroupCourses WHERE ID = ? AND GroupID = ?");
        
        foreach ($_POST['delete_courses'] as $courseId) {
            $courseId = filter_var($courseId, FILTER_VALIDATE_INT);
            if ($courseId) {
                $deleteStmt->bind_param("ii", $courseId, $gid);
                if (!$deleteStmt->execute()) {
                    throw new Exception('Failed to delete course: ' . $deleteStmt->error);
                }
            }
        }
    }

    // Process course updates and additions
    if (isset($_POST['courses']) && is_array($_POST['courses'])) {
        // Prepare statements for update and insert
        $updateCourseStmt = $conn->prepare("
            UPDATE GroupCourses 
            SET CourseID = ?, 
                InstructorID = ?, 
                StartDate = ?, 
                EndDate = ?, 
                Status = ?
            WHERE ID = ? AND GroupID = ?
        ");
        
        $insertCourseStmt = $conn->prepare("
            INSERT INTO GroupCourses 
            (GroupID, CourseID, InstructorID, StartDate, EndDate, Status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['courses'] as $course) {
            // Validate course data
            $groupCourseId = isset($course['group_course_id']) ? filter_var($course['group_course_id'], FILTER_VALIDATE_INT) : null;
            $courseId = isset($course['course_id']) ? filter_var($course['course_id'], FILTER_VALIDATE_INT) : null;
            $instructorId = isset($course['instructor_id']) && !empty($course['instructor_id']) ? 
                filter_var($course['instructor_id'], FILTER_VALIDATE_INT) : null;
            $courseStartDate = isset($course['start_date']) ? formatDateToYYYYMMDD(filter_var($course['start_date'], FILTER_SANITIZE_STRING)) : null;
            $courseEndDate = isset($course['end_date']) ? formatDateToYYYYMMDD(filter_var($course['end_date'], FILTER_SANITIZE_STRING)) : null;
            $status = isset($course['status']) ? filter_var($course['status'], FILTER_SANITIZE_STRING) : 'Scheduled';
            
            if (!$courseId) {
                continue; // Skip invalid course entries
            }
            
            // If group_course_id exists, update the existing record
            if ($groupCourseId) {
                $updateCourseStmt->bind_param(
                    "iisssii",
                    $courseId,
                    $instructorId,
                    $courseStartDate,
                    $courseEndDate,
                    $status,
                    $groupCourseId,
                    $gid
                );
                
                if (!$updateCourseStmt->execute()) {
                    throw new Exception('Failed to update course: ' . $updateCourseStmt->error);
                }
            } 
            // Otherwise, insert a new record
            else {
                $insertCourseStmt->bind_param(
                    "iiisss",
                    $gid,
                    $courseId,
                    $instructorId,
                    $courseStartDate,
                    $courseEndDate,
                    $status
                );
                
                if (!$insertCourseStmt->execute()) {
                    throw new Exception('Failed to add course: ' . $insertCourseStmt->error);
                }
            }
        }
    }
    
    // If we got here, everything succeeded, so commit the transaction
    $conn->commit();
    
    // Return JSON success
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // An error occurred, rollback the transaction
    $conn->rollback();
    
    // Return error response
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
