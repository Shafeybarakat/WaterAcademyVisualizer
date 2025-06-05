<?php
$pageTitle = "Trainee Course Report";
// Include config.php and auth.php via header.php
include_once "../includes/header.php"; // Includes session checks, header HTML

// RBAC guard: Only users with 'access_trainee_reports' permission can access this page.
if (!require_permission('access_trainee_reports', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Get trainee ID and course ID from URL parameters
$traineeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// If accessed directly without required IDs, show an error
if ($traineeId <= 0 || $courseId <= 0) {
    $errorMessage = "Both trainee ID and course ID are required.";
}

// --- Fetch Trainee Data ---
$trainee = null;
if ($traineeId > 0) {
    $stmt = $conn->prepare("SELECT TID, CONCAT(FirstName, ' ', LastName) AS FullName, GroupID, Email FROM Trainees WHERE TID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $traineeId);
        $stmt->execute();
        $traineeResult = $stmt->get_result();
        $trainee = $traineeResult->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Failed to prepare trainee query: " . $conn->error);
    }
}

// --- Fetch Course Data ---
$course = null;
if ($courseId > 0) {
    $stmt = $conn->prepare("SELECT CourseID, CourseName, CourseCode, Description, DurationWeeks, TotalHours FROM Courses WHERE CourseID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $courseResult = $stmt->get_result();
        $course = $courseResult->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Failed to prepare course query: " . $conn->error);
    }
}

// --- Get Group Course ID ---
$groupCourseId = null;
if ($traineeId > 0 && $courseId > 0) {
    $stmt = $conn->prepare("
        SELECT gc.ID 
        FROM GroupCourses gc
        JOIN Enrollments e ON gc.ID = e.GroupCourseID
        WHERE e.TID = ? AND gc.CourseID = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $traineeId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $groupCourseId = $row ? $row['ID'] : null;
        $stmt->close();
    } else {
        error_log("Failed to prepare group course query: " . $conn->error);
    }
}

// --- Fetch Enrollment Data ---
$enrollment = null;
if ($traineeId > 0 && $groupCourseId) {
    $stmt = $conn->prepare("
        SELECT 
            EnrollmentID, 
            EnrollmentDate, 
            Status, 
            CompletionDate, 
            FinalScore
        FROM Enrollments 
        WHERE TID = ? AND GroupCourseID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $traineeId, $groupCourseId);
        $stmt->execute();
        $enrollmentResult = $stmt->get_result();
        $enrollment = $enrollmentResult->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Failed to prepare enrollment query: " . $conn->error);
    }
}

// --- Fetch Attendance Data ---
$attendance = null;
if ($traineeId > 0 && $groupCourseId) {
    $stmt = $conn->prepare("
        SELECT 
            AttendanceID,
            PresentHours,
            ExcusedHours,
            LateHours,
            AbsentHours,
            TakenSessions,
            AttendancePercentage,
            Notes
        FROM Attendance 
        WHERE TID = ? AND GroupCourseID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $traineeId, $groupCourseId);
        $stmt->execute();
        $attendanceResult = $stmt->get_result();
        $attendance = $attendanceResult->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Failed to prepare attendance query: " . $conn->error);
    }
}

// --- Fetch Grade Components and Scores ---
$gradeComponents = [];
if ($traineeId > 0 && $groupCourseId) {
    $stmt = $conn->prepare("
        SELECT 
            gc.ComponentID,
            gc.ComponentName,
            gc.MaxPoints,
            tg.Score,
            tg.GradeDate,
            tg.Comments,
            tg.PositiveFeedback,
            tg.AreasToImprove
        FROM GradeComponents gc
        LEFT JOIN TraineeGrades tg ON gc.ComponentID = tg.ComponentID AND tg.TID = ? AND tg.GroupCourseID = ?
        WHERE gc.IsDefault = 1
        ORDER BY gc.ComponentID
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $traineeId, $groupCourseId);
        $stmt->execute();
        $componentsResult = $stmt->get_result();
        while ($row = $componentsResult->fetch_assoc()) {
            // Calculate normalized score (percentage)
            if ($row['Score'] !== null && $row['MaxPoints'] > 0) {
                $row['NormalizedScore'] = ($row['Score'] / $row['MaxPoints']) * 100;
            } else {
                $row['NormalizedScore'] = null;
            }
            $gradeComponents[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare grade components query: " . $conn->error);
    }
}

// --- Calculate Final Score (Simple Addition) ---
$totalScore = 0;
$totalMaxPoints = 0;

foreach ($gradeComponents as $component) {
    if ($component['Score'] !== null) {
        $totalScore += $component['Score'];
        $totalMaxPoints += $component['MaxPoints'] ?? 0;
    }
}

$finalScore = ($totalMaxPoints > 0) ? round(($totalScore / $totalMaxPoints) * 100) : 0;

// --- Calculate Learning Gap Indicator (LGI) ---
$lgi = null;
$preTestScore = null;

foreach ($gradeComponents as $component) {
    if ($component['ComponentName'] === 'PreTest') {
        $preTestScore = $component['Score'];
        break;
    }
}

if ($preTestScore !== null) {
    $normalizedPretest = ($preTestScore / 50) * 100; // Assuming PreTest is out of 50
    if (100 - $normalizedPretest > 0) {
        $lgi = (($finalScore - $normalizedPretest) / (100 - $normalizedPretest)) * 100;
        $lgi = round($lgi, 1);
    } else {
        $lgi = 0; // If pretest was perfect, no gap to close
    }
}

// --- Get Class Rank ---
$rank = null;
$totalStudents = null;

if ($traineeId > 0 && $courseId > 0) {
    $stmt = $conn->prepare("
        WITH CourseAverages AS (
            SELECT
                gc.CourseID,
                e.TID,
                AVG(tg.Score) AS AvgScore
            FROM Enrollments e
            JOIN GroupCourses gc ON e.GroupCourseID = gc.ID
            JOIN TraineeGrades tg ON e.TID = tg.TID AND e.GroupCourseID = tg.GroupCourseID
            WHERE gc.CourseID = ?
            GROUP BY gc.CourseID, e.TID
        ),
        RankedScores AS (
            SELECT
                CourseID,
                TID,
                AvgScore,
                RANK() OVER (PARTITION BY CourseID ORDER BY AvgScore DESC) as RankInCourse
            FROM CourseAverages
        ),
        CourseStudentCounts AS (
            SELECT
                gc.CourseID,
                COUNT(DISTINCT e.TID) as TotalStudents
            FROM Enrollments e
            JOIN GroupCourses gc ON e.GroupCourseID = gc.ID
            WHERE gc.CourseID = ?
            GROUP BY gc.CourseID
        )
        SELECT
            rs.RankInCourse as Rank,
            csc.TotalStudents
        FROM RankedScores rs
        JOIN CourseStudentCounts csc ON rs.CourseID = csc.CourseID
        WHERE rs.TID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("iii", $courseId, $courseId, $traineeId);
        $stmt->execute();
        $rankResult = $stmt->get_result();
        $rankData = $rankResult->fetch_assoc();
        if ($rankData) {
            $rank = $rankData['Rank'];
            $totalStudents = $rankData['TotalStudents'];
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare rank query: " . $conn->error);
    }
}

// --- Get Instructor Information ---
$instructor = null;
if ($groupCourseId) {
    $stmt = $conn->prepare("
        SELECT 
            u.UserID,
            CONCAT(u.FirstName, ' ', u.LastName) AS FullName,
            u.Email,
            u.Phone
        FROM GroupCourses gc
        JOIN Users u ON gc.InstructorID = u.UserID
        WHERE gc.ID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $groupCourseId);
        $stmt->execute();
        $instructorResult = $stmt->get_result();
        $instructor = $instructorResult->fetch_assoc();
        $stmt->close();
    } else {
        error_log("Failed to prepare instructor query: " . $conn->error);
    }
}

// Get current date for the report
$reportDate = date('F j, Y, g:i a');
?>

<main class="main-content p-4">
    <div class="container-fluid">
        <?php if ($traineeId > 0 && $courseId > 0 && $trainee && $course): ?>
        <!-- Report Header -->
        <div class="report-header mb-4 p-3 border rounded bg-light">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mb-1">Trainee Course Performance Report</h2>
                    <p class="mb-0"><strong>Trainee:</strong> <?= htmlspecialchars($trainee['FullName']) ?></p>
                    <p class="mb-0"><strong>Course:</strong> <?= htmlspecialchars($course['CourseName']) ?> <?= $course['CourseCode'] ? '(' . htmlspecialchars($course['CourseCode']) . ')' : '' ?></p>
                    <?php
                    $groupName = 'N/A';
                    if ($trainee['GroupID']) {
                        $groupStmt = $conn->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
                        if($groupStmt) {
                            $groupStmt->bind_param("i", $trainee['GroupID']);
                            $groupStmt->execute();
                            $groupResult = $groupStmt->get_result();
                            $groupData = $groupResult->fetch_assoc();
                            if ($groupData) $groupName = $groupData['GroupName'];
                            $groupStmt->close();
                        }
                    }
                    ?>
                    <p class="mb-0"><strong>Group:</strong> <?= htmlspecialchars($groupName) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><strong>Report Date:</strong> <?= $reportDate ?></p>
                    <p class="mb-0"><strong>Status:</strong> 
                        <?php if ($enrollment): ?>
                            <span class="badge bg-<?= strtolower($enrollment['Status']) === 'completed' ? 'success' : 'primary' ?>">
                                <?= htmlspecialchars($enrollment['Status']) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Unknown</span>
                        <?php endif; ?>
                    </p>
                    <div class="report-actions mt-2">
                        <button id="printReportBtn" class="btn btn-sm btn-primary"><i class="bi bi-file-pdf"></i> Export PDF</button>
                        <button id="emailReportBtn" class="btn btn-sm btn-secondary"><i class="bi bi-envelope"></i> Email Report</button>
                        <a href="trainee_report_all.php?id=<?= $traineeId ?>" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-list-ul"></i> View All Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="row mb-4">
            <!-- Performance Metrics -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">Performance Metrics</h4>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: row; flex-wrap: wrap; margin-left: -0.75rem; margin-right: -0.75rem;">
                            <!-- Score Chart -->
                            <div style="width: 33.333%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                                <div class="card shadow-sm border rounded-3 h-100" style="width: 100%;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <canvas id="scoreChart" height="130"></canvas>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <h3 class="fw-bold"><?= number_format($finalScore, 1) ?></h3>
                                            <p class="text-muted mb-0">Overall Score</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Attendance Chart -->
                            <div style="width: 33.333%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                                <div class="card shadow-sm border rounded-3 h-100" style="width: 100%;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <canvas id="attendanceChart" height="130"></canvas>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <h3 class="fw-bold"><?= number_format($attendance['AttendancePercentage'] ?? 0, 1) ?>%</h3>
                                            <p class="text-muted mb-0">Attendance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- LGI Chart -->
                            <div style="width: 33.333%; padding: 0 0.75rem; margin-bottom: 1rem;" class="d-flex">
                                <div class="card shadow-sm border rounded-3 h-100" style="width: 100%;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <canvas id="lgiChart" height="130"></canvas>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <h3 class="fw-bold"><?= $lgi !== null ? number_format($lgi, 1) . '%' : 'N/A' ?></h3>
                                            <p class="text-muted mb-0">Learning Gap Indicator</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Info -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">Course Information</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-event text-primary me-2"></i> Duration:</span>
                                <span class="badge bg-primary rounded-pill"><?= $course['DurationWeeks'] ?? 'N/A' ?> weeks</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-clock text-success me-2"></i> Total Hours:</span>
                                <span class="badge bg-success rounded-pill"><?= $course['TotalHours'] ?? 'N/A' ?> hours</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-trophy text-warning me-2"></i> Class Rank:</span>
                                <span class="badge bg-warning text-dark rounded-pill">
                                    <?= $rank !== null ? $rank . ' of ' . $totalStudents : 'N/A' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-person-badge text-info me-2"></i> Instructor:</span>
                                <span><?= $instructor ? htmlspecialchars($instructor['FullName']) : 'Not Assigned' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Breakdown -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Attendance Breakdown</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($attendance): ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="attendanceBreakdownChart" height="200"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Hours</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $totalHours = $attendance['PresentHours'] + $attendance['ExcusedHours'] + $attendance['LateHours'] + $attendance['AbsentHours'];
                                                $categories = [
                                                    ['Present', $attendance['PresentHours'], 'success'],
                                                    ['Excused', $attendance['ExcusedHours'], 'info'],
                                                    ['Late', $attendance['LateHours'], 'warning'],
                                                    ['Absent', $attendance['AbsentHours'], 'danger']
                                                ];
                                                foreach ($categories as $cat):
                                                    $percentage = $totalHours > 0 ? round(($cat[1] / $totalHours) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td><span class="badge bg-<?= $cat[2] ?>"><?= $cat[0] ?></span></td>
                                                    <td><?= number_format($cat[1], 1) ?></td>
                                                    <td><?= $percentage ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-active">
                                                    <td><strong>Total</strong></td>
                                                    <td><strong><?= number_format($totalHours, 1) ?></strong></td>
                                                    <td><strong>100%</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if ($attendance['Notes']): ?>
                                        <div class="alert alert-info mt-3">
                                            <strong>Notes:</strong> <?= htmlspecialchars($attendance['Notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No attendance data available for this course.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Components -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Grade Components</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($gradeComponents)): ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="gradeComponentsChart" height="300"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Component</th>
                                                    <th>Score</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($gradeComponents as $component): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($component['ComponentName']) ?></td>
                                                    <td>
                                                        <?= $component['Score'] !== null ? 
                                                            htmlspecialchars($component['Score']) . ' / ' . htmlspecialchars($component['MaxPoints']) : 
                                                            'Not Graded' 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?= $component['NormalizedScore'] !== null ? 
                                                            number_format($component['NormalizedScore'], 1) . '%' : 
                                                            '-' 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-active">
                                                    <td><strong>Final Score</strong></td>
                                                    <td colspan="2"><strong><?= number_format($finalScore, 1) ?>%</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No grade components available for this course.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bi bi-hand-thumbs-up me-2"></i>Strengths</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $hasStrengths = false;
                        foreach ($gradeComponents as $component) {
                            if (!empty($component['PositiveFeedback'])) {
                                $hasStrengths = true;
                                echo '<div class="mb-3">';
                                echo '<p class="mb-1">' . htmlspecialchars($component['PositiveFeedback']) . '</p>';
                                echo '<small class="text-muted">' . htmlspecialchars($component['ComponentName']) . ' - ' . ($component['GradeDate'] ? date('M j, Y', strtotime($component['GradeDate'])) : 'N/A') . '</small>';
                                echo '</div>';
                            }
                        }
                        if (!$hasStrengths) {
                            echo '<p class="text-muted">No specific strengths recorded.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Areas for Improvement</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $hasImprovements = false;
                        foreach ($gradeComponents as $component) {
                            if (!empty($component['AreasToImprove'])) {
                                $hasImprovements = true;
                                echo '<div class="mb-3">';
                                echo '<p class="mb-1">' . htmlspecialchars($component['AreasToImprove']) . '</p>';
                                echo '<small class="text-muted">' . htmlspecialchars($component['ComponentName']) . ' - ' . ($component['GradeDate'] ? date('M j, Y', strtotime($component['GradeDate'])) : 'N/A') . '</small>';
                                echo '</div>';
                            }
                        }
                        if (!$hasImprovements) {
                            echo '<p class="text-muted">No specific areas for improvement recorded.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
            <!-- Error Message -->
            <div class="alert alert-warning">
                <h4 class="alert-heading">Information Required</h4>
                <p><?= isset($errorMessage) ? htmlspecialchars($errorMessage) : 'Both trainee ID and course ID are required to view this report.' ?></p>
                <hr>
                <p class="mb-0">
                    <a href="trainee_report_all.php" class="btn btn-primary">View All Trainees Report</a>
                    <a href="group-analytics.php" class="btn btn-secondary">Return to Group Analytics</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include_once "../includes/footer.php"; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/report-print.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($traineeId > 0 && $courseId > 0 && $trainee && $course): ?>
    
    // Score Chart
    const scoreCtx = document.getElementById('scoreChart')?.getContext('2d');
    if (scoreCtx) {
        const scoreValue = Math.min(<?= $finalScore ?>, 100);
        new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: ['Score', 'Remaining'],
                datasets: [{ 
                    data: [scoreValue, 100 - scoreValue], 
                    backgroundColor: ['#4361ee', '#f1f1f1'], 
                    borderWidth: 0, 
                    cutout: '70%' 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: { 
                        enabled: true, 
                        callbacks: { 
                            label: (ctx) => `Score: ${ctx.raw.toFixed(1)}%` 
                        } 
                    } 
                } 
            }
        });
    }

    // Attendance Chart
    const attCtx = document.getElementById('attendanceChart')?.getContext('2d');
    if (attCtx) {
        const attValue = <?= $attendance['AttendancePercentage'] ?? 0 ?>;
        new Chart(attCtx, {
            type: 'doughnut',
            data: {
                labels: ['Attendance', 'Absent'],
                datasets: [{ 
                    data: [attValue, 100 - attValue], 
                    backgroundColor: ['#2ec4b6', '#f1f1f1'], 
                    borderWidth: 0, 
                    cutout: '70%' 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false }, 
                    tooltip: { 
                        enabled: true, 
                        callbacks: { 
                            label: (ctx) => `Attendance: ${ctx.raw.toFixed(1)}%` 
                        } 
                    } 
                } 
            }
        });
    }

    // LGI Chart
    const lgiCtx = document.getElementById('lgiChart')?.getContext('2d');
    if (lgiCtx) {
        const lgiValue = Math.min(<?= $lgi ?? 0 ?>, 100);
        new Chart(lgiCtx, {
            type: 'doughnut',
            data: {
                labels: ['LGI', 'Remaining'],
                datasets: [{ 
                    data: [lgiValue, 100 - lgiValue], 
                    backgroundColor: ['#ff9f1c', '#f1f1f1'], 
                    borderWidth: 0, 
                    cutout: '70%' 
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        enabled: true, 
                        callbacks: { 
                            label: (ctx) => `LGI: ${ctx.raw.toFixed(1)}%` 
                        } 
                    } 
                } 
            }
        });
    }

    // Attendance Breakdown Chart
    const attBreakdownCtx = document.getElementById('attendanceBreakdownChart')?.getContext('2d');
    if (attBreakdownCtx && <?= $attendance ? 'true' : 'false' ?>) {
        new Chart(attBreakdownCtx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Excused', 'Late', 'Absent'],
                datasets: [{ 
                    data: [
                        <?= $attendance['PresentHours'] ?? 0 ?>, 
                        <?= $attendance['ExcusedHours'] ?? 0 ?>, 
                        <?= $attendance['LateHours'] ?? 0 ?>, 
                        <?= $attendance['AbsentHours'] ?? 0 ?>
                    ], 
                    backgroundColor: ['#2ec4b6', '#3d9df3', '#ff9f1c', '#e71d36'], 
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    // Grade Components Chart
    const gradeComponentsCtx = document.getElementById('gradeComponentsChart')?.getContext('2d');
    if (gradeComponentsCtx && <?= !empty($gradeComponents) ? 'true' : 'false' ?>) {
        const componentLabels = [<?= implode(',', array_map(function($comp) { return "'" . addslashes($comp['ComponentName']) . "'"; }, $gradeComponents)) ?>];
        const componentScores = [<?= implode(',', array_map(function($comp) { return $comp['NormalizedScore'] ?? 0; }, $gradeComponents)) ?>];
        
        new Chart(gradeComponentsCtx, {
            type: 'bar',
            data: {
                labels: componentLabels,
                datasets: [{ 
                    label: 'Score (%)', 
                    data: componentScores, 
                    backgroundColor: '#4361ee', 
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Score (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Components'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Print Report Button
    document.getElementById('printReportBtn')?.addEventListener('click', function() {
        window.print();
    });

    // Email Report Button
    document.getElementById('emailReportBtn')?.addEventListener('click', function() {
        const traineeId = <?= $traineeId ?>;
        const courseId = <?= $courseId ?>;
        
        if (confirm('Send this report to the trainee via email?')) {
            fetch(`send_report_email.php?trainee_id=${traineeId}&course_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Report sent successfully!');
                    } else {
                        alert('Error sending report: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending the report.');
                });
        }
    });
    <?php endif; ?>
});
</script>
