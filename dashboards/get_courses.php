<?php
// Ensure errors are not displayed directly to the client, breaking JSON output.
// Errors should be logged via error_log.
@ini_set('display_errors', 0);
@ini_set('log_errors', 1); // Ensure errors are logged
// error_reporting(E_ALL); // Consider setting this to E_ALL for comprehensive logging during development

header("Content-Type: application/json");

// Helper function to send JSON error responses
function send_json_error($message, $http_status_code = 500) {
    http_response_code($http_status_code);
    echo json_encode(["error" => $message]);
    exit();
}

include_once("../includes/config.php"); // Use include_once
include_once("../includes/auth.php");  // Use include_once. Ensure user is logged in and sets $_SESSION["user_id"], $_SESSION["role"]

// Check if the database connection was successful (assuming $conn is set in config.php)
if (!$conn || $conn->connect_error) {
    error_log("Database connection failed in get_courses.php: " . ($conn ? $conn->connect_error : "Unknown error - \$conn not set or not mysqli object"));
    send_json_error("Database connection error.", 500);
}

// Check if group_id is provided
if (!isset($_GET["group_id"])) {
    send_json_error("Group ID is required.", 400);
}

$group_id = $_GET["group_id"];
$user_role = $_SESSION["user_role"] ?? null;
$user_id = $_SESSION["user_id"] ?? null;


// Define allowed roles for accessing courses
$allowed_roles = ["Super Admin", "Admin", "Instructor", "Coordinator"];
if (!isLoggedIn()) {
    error_log("Not logged in when accessing get_courses.php");
    send_json_error("Authentication required.", 401);
}

// Debug log to see what role is being passed
error_log("User role in get_courses.php: " . $user_role . ", user_id: " . $user_id);

// Check if user has an allowed role
if (!in_array($user_role, $allowed_roles)) {
    error_log("Permission denied in get_courses.php for role: " . $user_role . ", user_id: " . $user_id);
    send_json_error("Permission denied.", 403);
}

// If user is an instructor, their ID must be set.
if ($user_role === "Instructor" && is_null($user_id)) {
    error_log("Instructor role without user_id in session for get_courses.php. GroupID: {$group_id}");
    send_json_error("User identification error for instructor.", 403); // Or 500 if it's an internal server state issue
}

$courses = []; // Initialize to empty array, ensures valid JSON `[]` if no courses found or error before population

try {
    // Updated SQL query to fetch courses for a specific group using the GroupCourses junction table
    $sql = "SELECT c.CourseID, c.CourseName
            FROM Courses c
            JOIN GroupCourses gc ON c.CourseID = gc.CourseID
            WHERE gc.GroupID = ?"; // Use proper case for column names
    $params = [$group_id];
    $types = "i"; // GroupID is integer

    // Apply filtering based on role
    if ($user_role === "Instructor") {
        // Instructors only see courses they are assigned to within the selected group
        $sql .= " AND gc.InstructorID = ?";
        $params[] = $user_id; // $user_id is confirmed not null for instructors by the check above
        $types .= "i"; // Assuming UserID is integer
    }
    // Coordinators, Admins, Editors, Designer Admins can see all courses within the selected group

    $sql .= " ORDER BY c.CourseName";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing course statement in get_courses.php: " . $conn->error . " SQL: " . $sql);
        send_json_error("Database error preparing to fetch courses.", 500);
    }

    if (!empty($params)) { // Only bind if there are params
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Error executing course statement in get_courses.php: " . $stmt->error . " SQL: " . $sql);
        send_json_error("Database error executing query for courses.", 500);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        error_log("Error getting result set in get_courses.php: " . $stmt->error);
        send_json_error("Database error retrieving course results.", 500);
    }

    $courses = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

} catch (Exception $e) {
    error_log("Exception in get_courses.php: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
    send_json_error("An unexpected error occurred while fetching courses.", 500);
} finally {
    if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) { // Check if $conn is a valid, open mysqli object
        $conn->close();
    }
}

echo json_encode($courses);
?>
