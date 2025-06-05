<?php
// dashboards/remove_course_from_group.php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// RBAC guard: Only users with 'manage_groups' permission can access this page.
if (!hasPermission('manage_groups')) {
    http_response_code(403);
    exit('Access denied. You do not have permission to manage groups.');
}

// Expect POST (or you can use DELETE via AJAX if you prefer)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$gid    = filter_input(INPUT_POST, 'group_id',  FILTER_VALIDATE_INT);
$cid    = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);

if (!$gid || !$cid) {
    http_response_code(400);
    exit('Missing group_id or course_id');
}

$stmt = $conn->prepare("
  DELETE FROM GroupCourses
   WHERE GroupID  = ?
     AND CourseID = ?
");
$stmt->bind_param("ii", $gid, $cid);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo 'Database error';
}
