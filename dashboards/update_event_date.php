<?php
// Enable error logging to a custom file
ini_set("display_errors", 0); // Don't display errors to the browser
ini_set("log_errors", 1); // Enable error logging
ini_set("error_log", "../logs/php_dashboard_errors.log"); // Set the log file path

include("../includes/config.php");
include("../includes/auth.php"); // Basic auth, ensure session started

header("Content-Type: application/json"); // Ensure this is set before any output

// Check if user is authorized to update event dates
if (!isLoggedIn() || !hasPermission('manage_groups')) {
    error_log("update_event_date.php: Unauthorized access attempt");
    echo json_encode(["error" => "You are not authorized to perform this action."]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("update_event_date.php: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

// Get POST data
$event_type = $_POST['event_type'] ?? null;
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
$new_end_date = $_POST['new_end_date'] ?? null;
$reason = $_POST['reason'] ?? '';

// Validate input
if (empty($event_type) || $event_id === null || $event_id <= 0 || empty($new_end_date)) {
    error_log("update_event_date.php: Missing or invalid input. Type: " . $event_type . ", ID: " . ($event_id ?? 'NULL') . ", New End Date: " . ($new_end_date ?? 'NULL'));
    echo json_encode(["error" => "Missing or invalid input."]);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_end_date)) {
    error_log("update_event_date.php: Invalid date format: " . $new_end_date);
    echo json_encode(["error" => "Invalid date format. Please use YYYY-MM-DD."]);
    exit;
}

// Ensure $conn is available and not in error state
if (!$conn || $conn->connect_error) {
    error_log("update_event_date.php: Database connection failed.");
    echo json_encode(["error" => "Database connection failed. Please check server logs."]);
    exit;
}

// Update the end date based on event type
if ($event_type === 'course_instance_ending') {
    // Update GroupCourses table
    $sql = "UPDATE GroupCourses SET EndDate = ?, LastModified = NOW() WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("update_event_date.php: Prepare failed: " . $conn->error);
        echo json_encode(["error" => "Database error. Please check server logs."]);
        exit;
    }
    
    $stmt->bind_param("si", $new_end_date, $event_id);
    
    if (!$stmt->execute()) {
        error_log("update_event_date.php: Execute failed: " . $stmt->error);
        echo json_encode(["error" => "Failed to update course end date. Please check server logs."]);
        exit;
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        error_log("update_event_date.php: No rows affected. Course ID: " . $event_id);
        echo json_encode(["error" => "No changes made. The course may not exist or the end date is already set to the specified value."]);
        exit;
    }
    
    $stmt->close();
    
    // Log the change
    $user_id = $_SESSION['UserID'] ?? 0;
    $sql_log = "INSERT INTO ActivityLog (UserID, ActivityType, EntityType, EntityID, Details, ActivityDate) VALUES (?, 'Update', 'GroupCourse', ?, ?, NOW())";
    $stmt_log = $conn->prepare($sql_log);
    
    if ($stmt_log) {
        $details = "Updated end date to " . $new_end_date . ". Reason: " . $reason;
        $stmt_log->bind_param("iis", $user_id, $event_id, $details);
        $stmt_log->execute();
        $stmt_log->close();
    } else {
        error_log("update_event_date.php: Failed to log activity: " . $conn->error);
    }
    
} elseif ($event_type === 'group_program_ending') {
    // Update Groups table
    $sql = "UPDATE Groups SET EndDate = ?, LastModified = NOW() WHERE GroupID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("update_event_date.php: Prepare failed: " . $conn->error);
        echo json_encode(["error" => "Database error. Please check server logs."]);
        exit;
    }
    
    $stmt->bind_param("si", $new_end_date, $event_id);
    
    if (!$stmt->execute()) {
        error_log("update_event_date.php: Execute failed: " . $stmt->error);
        echo json_encode(["error" => "Failed to update group end date. Please check server logs."]);
        exit;
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        error_log("update_event_date.php: No rows affected. Group ID: " . $event_id);
        echo json_encode(["error" => "No changes made. The group may not exist or the end date is already set to the specified value."]);
        exit;
    }
    
    $stmt->close();
    
    // Log the change
    $user_id = $_SESSION['UserID'] ?? 0;
    $sql_log = "INSERT INTO ActivityLog (UserID, ActivityType, EntityType, EntityID, Details, ActivityDate) VALUES (?, 'Update', 'Group', ?, ?, NOW())";
    $stmt_log = $conn->prepare($sql_log);
    
    if ($stmt_log) {
        $details = "Updated end date to " . $new_end_date . ". Reason: " . $reason;
        $stmt_log->bind_param("iis", $user_id, $event_id, $details);
        $stmt_log->execute();
        $stmt_log->close();
    } else {
        error_log("update_event_date.php: Failed to log activity: " . $conn->error);
    }
    
} else {
    error_log("update_event_date.php: Invalid event type: " . $event_type);
    echo json_encode(["error" => "Invalid event type."]);
    exit;
}

// Return success response
echo json_encode([
    "success" => true,
    "message" => "End date updated successfully.",
    "new_end_date" => $new_end_date
]);

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
