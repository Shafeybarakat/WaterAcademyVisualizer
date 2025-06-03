<?php
// get_trainee_data.php - AJAX endpoint for fetching trainee course data
require_once "../includes/auth.php";
require_once "../includes/config.php";

// Check permissions
$can_see_reports = hasAnyPermission(['access_group_reports', 'access_trainee_reports', 'access_attendance_reports']);
if (!$can_see_reports) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'courses' => []
];

// Check if trainee_id is provided
if (!isset($_GET['trainee_id']) || empty($_GET['trainee_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Trainee ID is required']);
    exit;
}

$traineeId = (int)$_GET['trainee_id'];

try {
    // Query to get all courses for the trainee
    $query = "
        SELECT 
            t.TID,
            CONCAT(t.FirstName, ' ', t.LastName) AS TraineeName,
            t.GovID,
            g.GroupName,
            g.GroupID,
            c.CourseID,
            c.CourseName,
            gc.StartDate,
            gc.EndDate
        FROM 
            Trainees t
            JOIN Groups g ON t.GroupID = g.GroupID
            JOIN GroupCourses gc ON g.GroupID = gc.GroupID
            JOIN Courses c ON gc.CourseID = c.CourseID
        WHERE 
            t.TID = ?
        ORDER BY 
            c.CourseName
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $traineeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format dates for display
            if (!empty($row['StartDate'])) {
                $row['StartDate'] = date('Y-m-d', strtotime($row['StartDate']));
            }
            if (!empty($row['EndDate'])) {
                $row['EndDate'] = date('Y-m-d', strtotime($row['EndDate']));
            }
            
            $response['courses'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['error'] = 'No courses found for this trainee';
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("Error in get_trainee_data.php: " . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
