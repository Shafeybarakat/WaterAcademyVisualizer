<?php
// get_instructor_courses_admin.php - AJAX endpoint to get courses for any instructor (admin view)
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Debug: Log request with more details
error_log("get_instructor_courses_admin.php accessed with instructor_id: " . (isset($_GET['instructor_id']) ? $_GET['instructor_id'] : 'none'));
error_log("User logged in: " . (isLoggedIn() ? 'Yes' : 'No'));
error_log("User role: " . getUserRole());
error_log("Has admin permissions: " . (hasPermission('manage_users') ? 'Yes' : 'No'));

// If debug parameter is set, output debug information
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($debug) {
    header('Content-Type: text/plain');
    echo "Debug Information:\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "User logged in: " . (isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "User role: " . getUserRole() . "\n";
    echo "Has admin permissions: " . (hasPermission('manage_users') ? 'Yes' : 'No') . "\n";
    echo "Instructor ID: " . (isset($_GET['instructor_id']) ? $_GET['instructor_id'] : 'none') . "\n";
    echo "\n";
}

// Check if user is logged in and has appropriate permission
if (!isLoggedIn() || !hasPermission('view_courses')) {
    error_log("Access denied: User not logged in or doesn't have appropriate permission");
    if ($debug) {
        echo "Access denied: User not logged in or doesn't have appropriate permission\n";
        echo "User role: " . getUserRole() . "\n";
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied. Role: ' . getUserRole()]);
        exit;
    }
}

// Get instructor ID from request
$instructorId = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;

if ($instructorId <= 0) {
    error_log("Invalid instructor ID: " . $instructorId);
    if ($debug) {
        echo "Invalid instructor ID: " . $instructorId . "\n";
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid instructor ID']);
        exit;
    }
}

if ($debug) {
    echo "Valid instructor ID: " . $instructorId . "\n";
    
    // Debug database connection
    echo "\nDatabase Connection Info:\n";
    echo "Connection error: " . ($conn->connect_error ?? 'None') . "\n";
    echo "Connection errno: " . ($conn->connect_errno ?? 'None') . "\n";
    echo "Host info: " . ($conn->host_info ?? 'Unknown') . "\n";
    echo "Server info: " . ($conn->server_info ?? 'Unknown') . "\n";
    echo "Server version: " . ($conn->server_version ?? 'Unknown') . "\n";
    echo "\n";
}

// Query for all courses assigned to this instructor across all groups
$coursesQuery = "
    SELECT 
        gc.ID as GroupCourseID, 
        c.CourseID, 
        c.CourseName,
        g.GroupName,
        gc.StartDate,
        gc.EndDate,
        CASE 
            WHEN CURDATE() < gc.StartDate THEN 'Upcoming'
            WHEN CURDATE() > gc.EndDate THEN 'Completed'
            ELSE 'Active'
        END as Status
    FROM GroupCourses gc
    JOIN Courses c ON gc.CourseID = c.CourseID
    JOIN Groups g ON gc.GroupID = g.GroupID
    WHERE gc.InstructorID = ?
    ORDER BY gc.StartDate DESC, c.CourseName
";

if ($debug) {
    echo "SQL Query:\n" . str_replace('?', $instructorId, $coursesQuery) . "\n\n";
}

try {
    $stmt = $conn->prepare($coursesQuery);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        if ($debug) {
            echo "Prepare failed: " . $conn->error . "\n";
            exit;
        }
        throw new Exception("Database prepare error");
    }
    
    $stmt->bind_param("i", $instructorId);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        if ($debug) {
            echo "Execute failed: " . $stmt->error . "\n";
            exit;
        }
        throw new Exception("Database execute error");
    }
    
    $result = $stmt->get_result();
    if ($debug) {
        echo "Query executed successfully\n";
        echo "Number of rows: " . $result->num_rows . "\n\n";
    }
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates for display
        $row['StartDate'] = date('M d, Y', strtotime($row['StartDate']));
        $row['EndDate'] = date('M d, Y', strtotime($row['EndDate']));
        $courses[] = $row;
    }
    
    // Debug: Log response
    error_log("Returning " . count($courses) . " courses for instructor ID: " . $instructorId);
    
// Return JSON response
if (!$debug) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo json_encode($courses);
} else {
    echo "Courses found: " . count($courses) . "\n";
    echo "JSON response:\n";
    echo json_encode($courses, JSON_PRETTY_PRINT);
}
} catch (Exception $e) {
    error_log("Error in get_instructor_courses_admin.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
