<?php
// group-analytics.php
$pageTitle = "Group Analytics";

// 1) Authentication, config and header (which includes your sidebar)
include_once "../includes/header.php";

// RBAC guard: Only users with 'access_group_analytics' permission can access this page.
if (!require_permission('access_group_analytics', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Back to Reports Button -->
    <div class="mb-4">
        <a href="reports.php?group_id=<?= $selectedGroup ?>&course_id=<?= $selectedCourse ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>
    <?php
    // 2) Fetch only groups that have finished courses with attendance and grades data
    $groupsResult = $conn->query("
        SELECT DISTINCT g.GroupID, g.GroupName 
        FROM `Groups` g
        JOIN GroupCourses gc ON g.GroupID = gc.GroupID
        JOIN Attendance a ON gc.ID = a.GroupCourseID
        JOIN TraineeGrades tg ON gc.ID = tg.GroupCourseID
        ORDER BY g.GroupName
    ");
    $groups = $groupsResult->fetch_all(MYSQLI_ASSOC);
    $selectedGroup = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int)$_GET['group_id'] : ($groups[0]['GroupID'] ?? null);
    $selectedCourse = $_GET['course_id'] ?? '';

    // 3) When a Group is chosen, fetch only its Courses that have attendance and grades data
    $courses = [];
    if (!empty($selectedGroup)) {
        $stmt = $conn->prepare("
          SELECT DISTINCT c.CourseID, c.CourseName 
          FROM Courses c
          JOIN GroupCourses gc ON c.CourseID = gc.CourseID
          JOIN Attendance a ON gc.ID = a.GroupCourseID
          JOIN TraineeGrades tg ON gc.ID = tg.GroupCourseID
          WHERE gc.GroupID = ?
          ORDER BY c.CourseName
        ");
        if ($stmt) {
            $stmt->bind_param("i", $selectedGroup);
            $stmt->execute();
            $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            error_log("Error preparing statement to fetch courses for group: " . $conn->error);
        }

        // Default to first course if none selected yet
        if (empty($selectedCourse) && !empty($courses)) {
            $selectedCourse = $courses[0]['CourseID'];
        }
    }

    // 4) If both Group & Course are set, pull Metrics & Trainee rows
    $metrics     = null;
    $traineeRows = [];

    if (!empty($selectedGroup) && !empty($selectedCourse)) {
        //
        // 4A) Group Performance Metrics
        //
        $sqlMetrics = "
            SELECT 
                AVG(AttendancePercentage) AS AvgAttendance,
                AVG(LEAST(CompositeScore, 100)) AS AvgScore,
                AVG(LEAST(LGI, 100)) AS AvgLGI
            FROM View_TraineePerformanceDetails
            WHERE GroupCourseID = (
                SELECT ID FROM GroupCourses 
                WHERE CourseID = ? AND GroupID = ?
            )
            AND GroupID = ?
        ";
        $stmt = $conn->prepare($sqlMetrics);
        if ($stmt) {
            $stmt->bind_param("sii", $selectedCourse, $selectedGroup, $selectedGroup);
            $stmt->execute();
            $metrics = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            error_log("Error preparing metrics statement: " . $conn->error);
            // Optionally, set $metrics to null or an empty array to prevent errors later
            $metrics = null;
        }

        //
        // 4B) Trainee‑by‑Trainee Breakdown
        //
        $sqlTrainees = "
            SELECT 
                TID,
                TraineeFullName AS FullName,
                CompositeScore AS Score,
                AttendancePercentage AS Attendance,
                LGI
            FROM View_TraineePerformanceDetails
            WHERE GroupCourseID = (
                SELECT ID FROM GroupCourses 
                WHERE CourseID = ? AND GroupID = ?
            )
            AND GroupID = ?
            ORDER BY TraineeFullName
        ";
        $stmt = $conn->prepare($sqlTrainees);
        if ($stmt) {
            $stmt->bind_param("sii", $selectedCourse, $selectedGroup, $selectedGroup);
            $stmt->execute();
            $traineeRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            error_log("Error preparing trainee rows statement: " . $conn->error);
            $traineeRows = [];
        }
    }
    ?>

    <!-- Report Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Report Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button 
                        type="button"
                        id="printReportBtn"
                        class="btn btn-outline-primary w-100"
                        title="Export PDF"
                    >
                        <i class="bi bi-file-pdf me-1"></i> Export PDF
                    </button>
                </div>
                <div class="col-md-6">
                    <button 
                        type="button"
                        id="emailReportBtn"
                        class="btn btn-outline-secondary w-100"
                        title="Email Report"
                    >
                        <i class="bi bi-envelope me-1"></i> Email Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if($metrics): ?>
      <!-- Performance Metric Cards -->
      <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Group Performance Metrics</h5>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: row; flex-wrap: wrap; margin-left: -0.75rem; margin-right: -0.75rem;">
              <?php 
                $metric_configs = [
                    'Score' => ['field' => 'AvgScore', 'id' => 'scoreChart', 'color' => '#4361ee'],
                    'Attendance' => ['field' => 'AvgAttendance', 'id' => 'attendanceChart', 'color' => '#2ec4b6'],
                    'LGI' => ['field' => 'AvgLGI', 'id' => 'lgiChart', 'color' => '#ff9f1c']
                ];
                foreach($metric_configs as $label => $config): 
              ?>
                <div style="flex: 1 0 33.333%; max-width: 33.333%; padding: 0 0.75rem; margin-bottom: 1rem;">
                  <div class="card shadow-sm border rounded-3 h-100" style="width: 100%;">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                      <div>
                        <canvas id="<?= $config['id'] ?>" height="130"></canvas>
                      </div>
                      <div class="mt-2 text-center">
                        <h3 class="fw-bold"><?= number_format(floatval($metrics[$config['field']] ?? 0), 1) ?></h3>
                        <p class="text-muted mb-0 fs-5 fw-bold">
                          <?php if($label == 'Score'): ?>
                            Average Score (0-100)
                          <?php elseif($label == 'Attendance'): ?>
                            Average Attendance <span>&#37;</span>
                          <?php elseif($label == 'LGI'): ?>
                            Average LGI <span>&#37;</span>
                          <?php else: ?>
                            Average <?= htmlspecialchars($label) ?>
                          <?php endif; ?>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if(!empty($traineeRows)): ?>
      <!-- Trainee Table -->
      <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-people me-2 text-primary"></i>
              Trainee Performance
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Score (0-100)</th>
                    <th>Attendance <span>&#37;</span></th>
                    <th>LGI <span>&#37;</span></th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($traineeRows as $i=>$t): ?>
                    <tr>
                      <td><?= $i+1 ?></td>
                      <td><?= htmlspecialchars($t['FullName']) ?></td>
                      <?php foreach(['Score','Attendance','LGI'] as $f): 
                        $val = $t[$f] ?? 0;
                        // Cap Score and LGI at 100
                        if ($f === 'Score' || $f === 'LGI') {
                            $val = min(floatval($val), 100);
                        }
                        $bar_color_class = 'bg-primary'; // Default
                        if ($f === 'Attendance') {
                            $bar_color_class = $val < 80 ? 'bg-warning' : 'bg-success';
                        } elseif ($f === 'Score') {
                            $bar_color_class = $val < 70 ? 'bg-danger' : ($val >= 90 ? 'bg-success' : 'bg-primary');
                        } elseif ($f === 'LGI') {
                            $bar_color_class = $val < 50 ? 'bg-danger' : ($val >= 80 ? 'bg-success' : 'bg-info');
                        }
                      ?>
                        <td>
                          <div class="progress mb-1" style="height:8px">
                            <div class="progress-bar <?= $bar_color_class ?>"
                                 role="progressbar"
                                 style="width:<?= $val ?>%"
                                 aria-valuenow="<?= $val ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100"
                            ></div>
                          </div>
                          <span class="small">
                            <?= number_format(floatval($val), 1) ?>
                            <?php if($f === 'Attendance' || $f === 'LGI'): ?>
                              <span>&#37;</span>
                            <?php endif; ?>
                          </span>
                        </td>
                      <?php endforeach; ?>
                      <td>
                        <a href="trainee_report.php?id=<?= $t['TID'] ?>&course_id=<?= $selectedCourse ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="View Course Report">
                          <i class="bi bi-bar-chart"></i> View
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        </div>
      </div>
    <?php endif; ?>
</div>

<!-- 6) Include footer, closing main and body tags -->
<?php include_once "../includes/footer.php"; ?>

<!-- Chart.js and Report Print Resources -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/report-print.css">
<script src="../assets/js/report-print.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($metrics): ?>
    // Score Chart
    const scoreCtx = document.getElementById('scoreChart')?.getContext('2d');
    if (scoreCtx) {
        const scoreValue = Math.min(<?= floatval($metrics["AvgScore"] ?? 0) ?>, 100);
        new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: ['Score', 'Remaining'],
                datasets: [{ data: [scoreValue, 100 - scoreValue], backgroundColor: ['#4361ee', '#f1f1f1'], borderWidth: 0, cutout: '70%' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true, callbacks: { label: (ctx) => `Score: ${ctx.raw.toFixed(1)}` } } } }
        });
    }

    // Attendance Chart
    const attCtx = document.getElementById('attendanceChart')?.getContext('2d');
    if (attCtx) {
        const attValue = <?= floatval($metrics["AvgAttendance"] ?? 0) ?>;
        new Chart(attCtx, {
            type: 'doughnut',
            data: {
                labels: ['Attendance', 'Absent'],
                datasets: [{ data: [attValue, 100 - attValue], backgroundColor: ['#2ec4b6', '#f1f1f1'], borderWidth: 0, cutout: '70%' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true, callbacks: { label: (ctx) => `Attendance: ${ctx.raw.toFixed(1)}%` } } } }
        });
    }

    // LGI Chart
    const lgiCtx = document.getElementById('lgiChart')?.getContext('2d');
    if (lgiCtx) {
        const lgiValue = Math.min(<?= floatval($metrics["AvgLGI"] ?? 0) ?>, 100);
        new Chart(lgiCtx, {
            type: 'doughnut',
            data: {
                labels: ['LGI', 'Remaining'],
                datasets: [{ data: [lgiValue, 100 - lgiValue], backgroundColor: ['#ff9f1c', '#f1f1f1'], borderWidth: 0, cutout: '70%' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true, callbacks: { label: (ctx) => `LGI: ${ctx.raw.toFixed(1)}%` } } } }
        });
    }
    <?php endif; ?>
    
    // Configure print button with dynamic data
    <?php if (!empty($selectedGroup) && !empty($selectedCourse)): ?>
    const printBtn = document.getElementById('printReportBtn');
    if (printBtn) {
        const groupName = document.querySelector('select[name="group_id"] option:checked')?.textContent || 'Group';
        const courseName = document.querySelector('select[name="course_id"] option:checked')?.textContent || 'Course';
        
        // Set data attributes for the print functionality
        printBtn.dataset.reportTitle = "Group Analytics Report";
        printBtn.dataset.reportSubtitle = `${groupName} - ${courseName}`;
        printBtn.dataset.filename = `${groupName.trim()}-${courseName.trim()}-Report`;
    }
    <?php endif; ?>
});
</script>
