<?php
$pageTitle = "Coordinator Dashboard";
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Protect this page - only users with view_coordinator_dashboard permission can access
if (!isLoggedIn() || !hasPermission('view_coordinator_dashboard')) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

require_once "../includes/header.php";

// Get coordinator ID
$coordinatorId = $_SESSION['user_id'];

// Get groups assigned to this coordinator
$assignedGroups = [];

// Query for groups assigned to this coordinator
$groupsQuery = "
    SELECT g.GroupID, g.GroupName, g.Program, g.StartDate, g.EndDate,
           COUNT(DISTINCT t.TID) as TraineeCount,
           COUNT(DISTINCT gc.ID) as CourseCount
    FROM Groups g
    LEFT JOIN Trainees t ON t.GroupID = g.GroupID
    LEFT JOIN GroupCourses gc ON gc.GroupID = g.GroupID
    WHERE g.CoordinatorID = ?
    GROUP BY g.GroupID, g.GroupName, g.Program, g.StartDate, g.EndDate
    ORDER BY g.StartDate DESC
";

$stmt = $conn->prepare($groupsQuery);
$stmt->bind_param("i", $coordinatorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $assignedGroups[] = $row;
}

// Get recent course completions
$recentCompletions = [];

$completionsQuery = "
    SELECT gc.ID as GroupCourseID, c.CourseName, g.GroupName, gc.EndDate,
           COUNT(DISTINCT e.TID) as TraineeCount,
           AVG(tg_final.Score) as AvgFinalScore
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    JOIN Courses c ON gc.CourseID = c.CourseID
    JOIN Enrollments e ON gc.ID = e.GroupCourseID
    LEFT JOIN TraineeGrades tg_final ON e.TID = tg_final.TID 
                                    AND gc.ID = tg_final.GroupCourseID 
                                    AND tg_final.ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Final Exam')
    WHERE g.CoordinatorID = ? 
    AND gc.EndDate < CURDATE()
    GROUP BY gc.ID, c.CourseName, g.GroupName, gc.EndDate
    ORDER BY gc.EndDate DESC
    LIMIT 5
";

$stmt = $conn->prepare($completionsQuery);
$stmt->bind_param("i", $coordinatorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $recentCompletions[] = $row;
}

// Get upcoming course starts
$upcomingCourses = [];

$upcomingQuery = "
    SELECT gc.ID as GroupCourseID, c.CourseName, g.GroupName, gc.StartDate,
           CONCAT(u.FirstName, ' ', u.LastName) as InstructorName,
           COUNT(DISTINCT e.TID) as TraineeCount
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    JOIN Courses c ON gc.CourseID = c.CourseID
    LEFT JOIN Users u ON gc.InstructorID = u.UserID
    LEFT JOIN Enrollments e ON gc.ID = e.GroupCourseID
    WHERE g.CoordinatorID = ? 
    AND gc.StartDate > CURDATE()
    GROUP BY gc.ID, c.CourseName, g.GroupName, gc.StartDate, InstructorName
    ORDER BY gc.StartDate ASC
    LIMIT 5
";

$stmt = $conn->prepare($upcomingQuery);
$stmt->bind_param("i", $coordinatorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $upcomingCourses[] = $row;
}

// Get overall statistics
$totalTrainees = 0;
$totalCourses = 0;
$avgAttendance = 0;
$avgFinalScore = 0;

$statsQuery = "
    SELECT 
        COUNT(DISTINCT t.TID) as TotalTrainees,
        COUNT(DISTINCT gc.ID) as TotalCourses,
        AVG(a.AttendancePercentage) as AvgAttendance,
        AVG(tg_final.Score) as AvgFinalScore
    FROM Groups g
    LEFT JOIN Trainees t ON t.GroupID = g.GroupID
    LEFT JOIN GroupCourses gc ON gc.GroupID = g.GroupID
    LEFT JOIN Attendance a ON a.GroupCourseID = gc.ID
    LEFT JOIN TraineeGrades tg_final ON tg_final.GroupCourseID = gc.ID 
                                    AND tg_final.ComponentID = (SELECT ComponentID FROM GradeComponents WHERE ComponentName = 'Final Exam')
    WHERE g.CoordinatorID = ?
";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param("i", $coordinatorId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalTrainees = $row['TotalTrainees'] ?? 0;
    $totalCourses = $row['TotalCourses'] ?? 0;
    $avgAttendance = $row['AvgAttendance'] ?? 0;
    $avgFinalScore = $row['AvgFinalScore'] ?? 0;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Coordinator Dashboard</h4>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Groups</h5>
                            <small class="text-muted">Assigned to you</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded-pill p-2">
                                <i class="bx bx-group fs-3"></i>
                            </span>
                        </div>
                    </div>
                    <h2 class="mt-2"><?= count($assignedGroups) ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Trainees</h5>
                            <small class="text-muted">Total in your groups</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded-pill p-2">
                                <i class="bx bx-user fs-3"></i>
                            </span>
                        </div>
                    </div>
                    <h2 class="mt-2"><?= $totalTrainees ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Attendance</h5>
                            <small class="text-muted">Average percentage</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded-pill p-2">
                                <i class="bx bx-calendar-check fs-3"></i>
                            </span>
                        </div>
                    </div>
                    <h2 class="mt-2"><?= number_format($avgAttendance, 1) ?>%</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Final Scores</h5>
                            <small class="text-muted">Average score</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded-pill p-2">
                                <i class="bx bx-bar-chart-alt-2 fs-3"></i>
                            </span>
                        </div>
                    </div>
                    <h2 class="mt-2"><?= number_format($avgFinalScore, 1) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- My Groups -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">My Groups</h5>
                    <a href="report_group_performance.php" class="btn btn-primary btn-sm">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i> View All Reports
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($assignedGroups)): ?>
                        <p class="text-muted">You are not assigned to any groups yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Program</th>
                                        <th>Duration</th>
                                        <th>Trainees</th>
                                        <th>Courses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedGroups as $group): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($group['GroupName']) ?></td>
                                            <td><?= htmlspecialchars($group['Program']) ?></td>
                                            <td>
                                                <?php if ($group['StartDate'] && $group['EndDate']): ?>
                                                    <?= date('M d, Y', strtotime($group['StartDate'])) ?> - 
                                                    <?= date('M d, Y', strtotime($group['EndDate'])) ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $group['TraineeCount'] ?></td>
                                            <td><?= $group['CourseCount'] ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="report_group_performance.php?group_id=<?= $group['GroupID'] ?>">
                                                            <i class="bx bx-bar-chart-alt-2 me-1"></i> Group Performance
                                                        </a>
                                                        <a class="dropdown-item" href="report_trainee_performance.php?group_id=<?= $group['GroupID'] ?>">
                                                            <i class="bx bx-user-check me-1"></i> Trainee Performance
                                                        </a>
                                                        <a class="dropdown-item" href="report_attendance_summary.php?group_id=<?= $group['GroupID'] ?>">
                                                            <i class="bx bx-calendar-check me-1"></i> Attendance Summary
                                                        </a>
                                                        <a class="dropdown-item" href="group-analytics.php?group_id=<?= $group['GroupID'] ?>">
                                                            <i class="bx bx-line-chart me-1"></i> Analytics
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Completions and Upcoming Courses -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recently Completed Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentCompletions)): ?>
                        <p class="text-muted">No recently completed courses.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recentCompletions as $course): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($course['CourseName']) ?></h6>
                                        <small><?= date('M d, Y', strtotime($course['EndDate'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($course['GroupName']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= $course['TraineeCount'] ?> trainees</small>
                                        <div>
                                            <span class="badge bg-label-info">Avg: <?= number_format($course['AvgFinalScore'] ?? 0, 1) ?></span>
                                            <a href="trainee_report.php?course_id=<?= $course['GroupCourseID'] ?>" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="bx bx-bar-chart-alt-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingCourses)): ?>
                        <p class="text-muted">No upcoming courses.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($upcomingCourses as $course): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($course['CourseName']) ?></h6>
                                        <small><?= date('M d, Y', strtotime($course['StartDate'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($course['GroupName']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= $course['TraineeCount'] ?> trainees</small>
                                        <div>
                                            <span class="badge bg-label-secondary">Instructor: <?= htmlspecialchars($course['InstructorName'] ?? 'Not Assigned') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="report_group_performance.php" class="btn btn-primary w-100">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i> Group Performance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="report_trainee_performance.php" class="btn btn-primary w-100">
                                <i class="bx bx-user-check me-1"></i> Trainee Performance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="report_attendance_summary.php" class="btn btn-primary w-100">
                                <i class="bx bx-calendar-check me-1"></i> Attendance Summary
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="group-analytics.php" class="btn btn-primary w-100">
                                <i class="bx bx-line-chart me-1"></i> Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once "../includes/footer.php";
?>
