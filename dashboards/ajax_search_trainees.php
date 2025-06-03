<?php
// ajax_search_trainees.php - Endpoint for searching trainees with autocomplete
require_once "../includes/auth.php";
require_once "../includes/config.php";

// Set appropriate headers for AJAX
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Log the request for debugging
error_log("ajax_search_trainees.php called with: " . json_encode($_GET));

// Check permissions
$can_see_reports = hasAnyPermission(['access_group_reports', 'access_trainee_reports', 'access_attendance_reports']);
if (!$can_see_reports) {
    echo json_encode(['results' => [], 'error' => 'Permission denied']);
    exit;
}

// Initialize response in Select2 expected format
$response = [
    'results' => []
];

// Get search term - check both GET and POST for flexibility
$term = isset($_GET['term']) ? trim($_GET['term']) : '';
if (empty($term) && isset($_GET['q'])) {
    $term = trim($_GET['q']); // Select2 sometimes uses 'q' instead of 'term'
}
if (empty($term) && isset($_POST['term'])) {
    $term = trim($_POST['term']);
}

if (empty($term)) {
    // Return empty response if no search term
    echo json_encode($response);
    exit;
}

// Log the search term
error_log("Searching for term: " . $term);

try {
    // Create search pattern with wildcards
    $searchPattern = '%' . $term . '%';
    
    // Query to search trainees by various fields
    $query = "
        SELECT DISTINCT 
            t.TID as id,
            CONCAT(t.FirstName, ' ', t.LastName) as text,
            t.GovID,
            t.Email,
            t.Phone,
            g.GroupName
        FROM 
            Trainees t
            JOIN Groups g ON t.GroupID = g.GroupID
        WHERE 
            t.FirstName LIKE ? OR 
            t.LastName LIKE ? OR 
            CONCAT(t.FirstName, ' ', t.LastName) LIKE ? OR
            t.GovID LIKE ? OR
            t.Email LIKE ? OR
            t.Phone LIKE ?
        ORDER BY 
            t.FirstName, t.LastName
        LIMIT 15
    ";
    
    error_log("Executing query: " . $query);
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("ssssss", 
        $searchPattern, 
        $searchPattern, 
        $searchPattern, 
        $searchPattern, 
        $searchPattern,
        $searchPattern
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log("Query returned " . $result->num_rows . " results");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add additional info to the text field to make search more helpful
            $row['text'] = $row['text'] . ($row['GovID'] ? ' (ID: ' . $row['GovID'] . ')' : '') . 
                           ' - ' . $row['GroupName'];
            
            // Add to results array
            $response['results'][] = [
                'id' => $row['id'],
                'text' => $row['text']
            ];
        }
    }
    
    $stmt->close();
    $response['success'] = true;
} catch (Exception $e) {
    $errorMsg = "Error in ajax_search_trainees.php: " . $e->getMessage();
    error_log($errorMsg);
    $response['error'] = $errorMsg;
    $response['success'] = false;
}

// Log the response
error_log("Sending response: " . json_encode($response));

// Return JSON response
echo json_encode($response);
