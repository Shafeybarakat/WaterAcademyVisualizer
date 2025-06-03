<?php
include_once "../includes/config.php";
include_once "../includes/auth.php"; // For session and permission checks if necessary

// Get group ID
$groupId = $_GET['group_id'] ?? '';

// Get courses already assigned to this group
$assignedCoursesQuery = "SELECT CourseID FROM GroupCourses WHERE GroupID = ?";
$stmt = $conn->prepare($assignedCoursesQuery);
$stmt->bind_param("s", $groupId);
$stmt->execute();
$result = $stmt->get_result();

$assignedCourses = [];
while ($row = $result->fetch_assoc()) {
    $assignedCourses[] = $row['CourseID'];
}

// Format assignedCourses for SQL NOT IN clause
$assignedCoursesStr = "''";
if (!empty($assignedCourses)) {
    $assignedCoursesStr = "'" . implode("','", $assignedCourses) . "'";
}

// Get available courses (not assigned to this group)
$availableCoursesQuery = "SELECT CourseID, CourseName FROM Courses 
                          WHERE CourseID NOT IN ($assignedCoursesStr)
                          ORDER BY CourseName";
$availableCourses = $conn->query($availableCoursesQuery);

// Generate HTML for available courses list
if ($availableCourses && $availableCourses->num_rows > 0) {
    while ($course = $availableCourses->fetch_assoc()) {
        echo '<a href="#" class="list-group-item list-group-item-action course-select-item" 
                 data-id="' . htmlspecialchars($course['CourseID']) . '" 
                 data-name="' . htmlspecialchars($course['CourseName']) . '">' . 
                 htmlspecialchars($course['CourseName']) . 
             '</a>';
    }
} else {
    echo '<div class="list-group-item text-center text-muted">No available courses found</div>';
}
?>