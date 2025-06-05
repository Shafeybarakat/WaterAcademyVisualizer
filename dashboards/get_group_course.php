<?php
// dashboards/get_group_course.php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

// RBAC guard: Only users with 'view_groups' permission can access this page.
if (!hasPermission('view_groups')) {
    http_response_code(403);
    exit('Access denied. You do not have permission to view group details.');
}

if (empty($_GET['group_id']) || !ctype_digit($_GET['group_id'])) {
    http_response_code(400);
    exit('Invalid group ID');
}
$gid = (int)$_GET['group_id'];

// 1) Fetch group details
$stmt = $conn->prepare("
  SELECT
    GroupName   AS name,
    Description AS description,
    StartDate   AS start_date,
    EndDate     AS end_date,
    Room        AS room_number
  FROM `Groups`
  WHERE GroupID = ?
");
$stmt->bind_param("i", $gid);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) {
    http_response_code(404);
    exit('Group not found');
}

// 2) Fetch attached courses, using gc.InstructorID
$stmt = $conn->prepare("
  SELECT
    c.CourseID                           AS id,
    c.CourseName                         AS title,
    gc.StartDate                         AS start_date,
    gc.EndDate                           AS end_date,
    CONCAT(v.FirstName, ' ', v.LastName) AS instructor_name
  FROM GroupCourses gc
  JOIN Courses c
    ON gc.CourseID = c.CourseID
  LEFT JOIN vw_Instructors v
    ON gc.InstructorID = v.InstructorID
  WHERE gc.GroupID = ?
  ORDER BY c.CourseName
");
$stmt->bind_param("i", $gid);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!-- Editable Group Form -->
<form id="editGroupForm">
  <input type="hidden" name="group_id" value="<?= $gid ?>">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input name="name" class="form-control" value="<?= htmlspecialchars($group['name']) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($group['description']) ?></textarea>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($group['start_date']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($group['end_date']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Room</label>
      <input name="room_number" class="form-control" value="<?= htmlspecialchars($group['room_number']) ?>">
    </div>
  </div>
</form>

<hr>

<!-- Courses Table -->
<h5>Attached Courses</h5>
<table class="table table-sm">
  <thead>
    <tr>
      <th>Course</th>
      <th>Start</th>
      <th>End</th>
      <th>Instructor</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if ($courses): ?>
      <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['title']) ?></td>
        <td><?= htmlspecialchars($c['start_date']) ?></td>
        <td><?= htmlspecialchars($c['end_date']) ?></td>
        <td><?= htmlspecialchars($c['instructor_name'] ?: '—') ?></td>
        <td>
          <button
            class="btn btn-sm btn-outline-danger detach-course-btn"
            data-course-id="<?= $c['id'] ?>"
            data-group-id="<?= $gid ?>"
          >Detach</button>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="5"><em>No courses attached.</em></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<button class="btn btn-success" disabled>+ Add New Course</button>
