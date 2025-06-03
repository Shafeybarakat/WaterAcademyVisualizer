<?php
// batch_load.php
require_once "../includes/auth.php"; // For session and permission checks
include_once "../includes/config.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

$action = $_GET['action'] ?? '';
$courseId = $_GET['course_id'] ?? '';

if (empty($action) || empty($courseId)) {
    $response['message'] = 'Missing required parameters';
    echo json_encode($response);
    exit;
}

if ($action === 'attendance') {
    // Load trainees enrolled in the course
    $query = "SELECT s.TID, s.FullName 
              FROM Students s
              JOIN Enrollments e ON s.TID = e.TID
              WHERE e.CourseID = ?
              ORDER BY s.FullName";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trainees = [];
    while ($row = $result->fetch_assoc()) {
        $trainees[] = $row;
    }
    
    $response['success'] = true;
    $response['trainees'] = $trainees;
} elseif ($action === 'grades') {
    $componentId = $_GET['component_id'] ?? '';
    
    if (empty($componentId)) {
        $response['message'] = 'Missing component ID';
        echo json_encode($response);
        exit;
    }
    
    // Get max points for this component
    $maxQuery = "SELECT MaxPoints FROM GradeComponents WHERE ComponentID = ?";
    $maxStmt = $conn->prepare($maxQuery);
    $maxStmt->bind_param("i", $componentId);
    $maxStmt->execute();
    $maxResult = $maxStmt->get_result();
    $maxPoints = $maxResult->fetch_assoc()['MaxPoints'] ?? 100;
    
    // Load trainees enrolled in the course
    $query = "SELECT s.TID, s.FullName,
              (SELECT Score FROM StudentGrades WHERE TID = s.TID AND CourseID = ? AND ComponentID = ?) as ExistingScore
              FROM Students s
              JOIN Enrollments e ON s.TID = e.TID
              WHERE e.CourseID = ?
              ORDER BY s.FullName";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $courseId, $componentId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trainees = [];
    while ($row = $result->fetch_assoc()) {
        $trainees[] = $row;
    }
    
    $response['success'] = true;
    $response['trainees'] = $trainees;
    $response['max_points'] = $maxPoints;
}

echo json_encode($response);
