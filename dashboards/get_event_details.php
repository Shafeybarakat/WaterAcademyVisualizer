<?php
// Enable error logging to a custom file
ini_set("display_errors", 0); // Don't display errors to the browser
ini_set("log_errors", 1); // Enable error logging
ini_set("error_log", "../logs/php_dashboard_errors.log"); // Set the log file path

include("../includes/config.php");
include("../includes/auth.php"); // Basic auth, ensure session started

header("Content-Type: application/json"); // Ensure this is set before any output

// RBAC guard: Only logged-in users can access this page.
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$event_type = $_GET["type"] ?? null;
$event_id = isset($_GET["id"]) ? intval($_GET["id"]) : null; // Ensure event_id is treated as an integer

if (!$conn) {
    error_log("get_event_details.php: Database connection failed.");
    echo json_encode(["error" => "Database connection failed. Please check server logs."]);
    exit;
}

// Attempt to set connection charset and collation to resolve collation conflicts
if (!$conn->set_charset("utf8mb4")) {
    error_log("get_event_details.php: Error loading character set utf8mb4: " . $conn->error);
}
// Explicitly set collation for the connection
if (!$conn->query("SET collation_connection = 'utf8mb4_unicode_ci'")) {
    error_log("get_event_details.php: Error setting collation_connection: " . $conn->error);
}

if (empty($event_type) || $event_id === null || $event_id <= 0) { // Check if event_id is a valid positive integer
    error_log("get_event_details.php: Missing or invalid event type or ID. Type: " . $event_type . ", ID: " . ($_GET["id"] ?? 'NULL'));
    echo json_encode(["error" => "Missing or invalid event type or ID."]);
    exit;
}

$response_data = [];

// Query for course details
if ($event_type === "course_instance_starting" || $event_type === "course_instance_ending") { // Match event types from index.php
    $sql = "SELECT c.CourseName, gc.StartDate, gc.EndDate, c.DurationWeeks, c.TotalHours, g.GroupName, " .
           "i.FirstName AS InstructorFirstName, i.LastName AS InstructorLastName, i.Email AS InstructorEmail, i.Phone AS InstructorPhone " .
           "FROM GroupCourses gc " . // Start with GroupCourses as we have its ID (GroupCourseID)
           "JOIN Courses c ON gc.CourseID = c.CourseID " . // Join to get Course template details
           "JOIN Groups g ON gc.GroupID = g.GroupID " .
           "LEFT JOIN Users i ON gc.InstructorID = i.UserID WHERE gc.ID = ?"; // Filter by GroupCourses.ID (which is the $event_id for course instances)
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("get_event_details.php: Prepare failed (course query): " . $conn->error . " SQL: " . $sql);
        echo json_encode(["error" => "Prepare failed (course). Please check server logs."]);
        exit;
    }
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        error_log("get_event_details.php: Execute failed (course): " . $stmt->error);
        echo json_encode(["error" => "Execute failed (course). Please check server logs."]);
        exit;
    }
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $response_data = $row;
    } else {
        error_log("get_event_details.php: Course instance details not found for GroupCourseID: " . htmlspecialchars($event_id) . ". Query error: " . $stmt->error);
        echo json_encode(["error" => "Course instance details not found for ID: " . htmlspecialchars($event_id) . "."]);
        exit;
    }
    $stmt->close();

// Query for group details
} elseif ($event_type === "group_program_starting" || $event_type === "group_program_ending") { // Match event types from index.php
    $sql = "SELECT g.GroupName, g.StartDate, g.EndDate, g.CompanyName, " .
           "u.FirstName AS CoordinatorFirstName, u.LastName AS CoordinatorLastName, u.Email AS CoordinatorEmail, u.Phone AS CoordinatorPhone " .
           "FROM Groups g " .
           "LEFT JOIN Users u ON g.CoordinatorID = u.UserID WHERE g.GroupID = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("get_event_details.php: Prepare failed (group query): " . $conn->error . " SQL: " . $sql);
        echo json_encode(["error" => "Prepare failed (group). Please check server logs."]);
        exit;
    }
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        error_log("get_event_details.php: Execute failed (group): " . $stmt->error);
        echo json_encode(["error" => "Execute failed (group). Please check server logs."]);
        exit;
    }
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $response_data = $row;
    } else {
        error_log("get_event_details.php: Group details not found for ID: " . htmlspecialchars($event_id) . ". Query error: " . $stmt->error);
        echo json_encode(["error" => "Group details not found for ID: " . htmlspecialchars($event_id) . "."]);
        exit;
    }
    $stmt->close();

} else {
    error_log("get_event_details.php: Invalid event type specified: " . $event_type);
    echo json_encode(["error" => "Invalid event type specified."]);
    exit;
}

if (empty($response_data)) {
     error_log("get_event_details.php: No data found for the specified event. Type: " . $event_type . ", ID: " . $event_id);
     echo json_encode(["error" => "No data found for the specified event."]);
} else {
    echo json_encode($response_data);
}

if (isset($conn)) {
    $conn->close();
}
?>
