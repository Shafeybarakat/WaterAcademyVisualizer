<?php
$pageTitle = "Group Dashboard";
// Include the header - this also includes config.php and auth.php
include_once "../includes/header.php";

// RBAC guard: Only users with 'access_group_dashboard' permission can access this page.
if (!require_permission('access_group_dashboard', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Fetch all groups
$groupsResult = $conn->query("SELECT GroupID, GroupName FROM Groups ORDER BY GroupName");
$groups = $groupsResult->fetch_all(MYSQLI_ASSOC);
$selectedGroup = $_GET['group_id'] ?? ($groups[0]['GroupID'] ?? '');
$selectedCourse = $_GET['course_id'] ?? '';

// Fetch all courses for selected group
$courses = [];
if (!empty($selectedGroup)) {
    $stmt = $conn->prepare("SELECT DISTINCT c.CourseID, c.CourseName
    	FROM Courses c
    	JOIN Enrollments e ON c.CourseID = e.CourseID
    	JOIN Trainees t ON e.TID = t.TID
    	WHERE t.GroupID = ?");

    $stmt->bind_param("s", $selectedGroup);
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (empty($selectedCourse) && !empty($courses)) {
        $selectedCourse = $courses[0]['CourseID'];
    }
}

// Fetch metrics and trainee details
$metrics = null;
$traineeRows = [];
if (!empty($selectedGroup) && !empty($selectedCourse)) {
    $stmt = $conn->prepare("
    SELECT
        ROUND(AVG(IFNULL(tg.Score, 0)),1) AS AvgScore,
        ROUND(AVG(CASE
            WHEN a.Status IN ('Present','Excused') THEN 100
            WHEN a.Status = 'Late' THEN 50
            ELSE 0 END),1) AS AvgAttendance,
        ROUND(AVG(CASE
            WHEN tg_pretest.Score IS NULL THEN NULL
            WHEN 100 - (tg_pretest.Score / 50 * 100) = 0 THEN 0
            ELSE (
                (total.Score - (tg_pretest.Score / 50 * 100)) / 
                (100 - (tg_pretest.Score / 50 * 100)) * 100
            )
        END),1) AS AvgLGI
    FROM Enrollments e
    JOIN TraineeGrades tg ON tg.CourseID = e.CourseID AND tg.TID = e.TID
    JOIN Attendance a ON a.CourseID = e.CourseID AND a.TID = e.TID
    LEFT JOIN (
        SELECT TID, CourseID, Score
        FROM TraineeGrades tg
        JOIN GradeComponents gc ON tg.ComponentID = gc.ComponentID
        WHERE gc.ComponentName = 'PRETEST'
    ) tg_pretest ON tg_pretest.TID = e.TID AND tg_pretest.CourseID = e.CourseID
    LEFT JOIN (
        SELECT t.TID, t.CourseID, 
               ROUND(
                 IFNULL(att.AttendancePercentage/10, 0) + 
                 IFNULL(MAX(CASE WHEN gc.ComponentName = 'Participation' THEN tg.Score END), 0) +
                 CASE 
                     WHEN COUNT(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN 1 END) = 0 THEN 0
                     ELSE SUM(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN tg.Score ELSE 0 END) / 
                          COUNT(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN 1 END)
                 END +
                 IFNULL(MAX(CASE WHEN gc.ComponentName = 'Final Exam' THEN tg.Score END), 0),
                 1
               ) AS Score
        FROM TraineeGrades t
        LEFT JOIN GradeComponents gc ON t.ComponentID = gc.ComponentID
        LEFT JOIN (
            SELECT 
                TID, 
                CourseID, 
                ROUND(SUM(CASE WHEN Status IN ('Present', 'Excused') THEN 1 WHEN Status = 'Late' THEN 0.5 ELSE 0 END) / 
                      NULLIF(COUNT(*), 0) * 100, 1) AS AttendancePercentage
            FROM Attendance
            GROUP BY TID, CourseID
        ) att ON t.TID = att.TID AND t.CourseID = att.CourseID
        GROUP BY t.TID, t.CourseID
    ) total ON total.TID = e.TID AND total.CourseID = e.CourseID
    JOIN Trainees t ON t.TID = e.TID
    WHERE e.CourseID = ? AND t.GroupID = ?
");

    $stmt->bind_param("ss", $selectedCourse, $selectedGroup);
    $stmt->execute();
    $metrics = $stmt->get_result()->fetch_assoc();

    // Trainees
    $stmt = $conn->prepare("
        SELECT t.TID, CONCAT(t.FirstName, ' ', t.LastName) AS FullName,
            ROUND(AVG(IFNULL(tg.Score, 0)),1) AS Score,
            ROUND(AVG(CASE
                WHEN a.Status IN ('Present','Excused') THEN 100
                WHEN a.Status = 'Late' THEN 50
                ELSE 0 END),1) AS Attendance,
            CASE
                WHEN tg_pretest.Score IS NULL THEN 0
                WHEN 100 - (tg_pretest.Score / 50 * 100) = 0 THEN 0
                ELSE ROUND(
                    (total.Score - (tg_pretest.Score / 50 * 100)) / 
                    (100 - (tg_pretest.Score / 50 * 100)) * 100,
                    1
                )
            END AS LGI
        FROM Trainees t
        JOIN Enrollments e ON t.TID = e.TID
        LEFT JOIN TraineeGrades tg ON tg.TID = t.TID AND tg.CourseID = ?
        LEFT JOIN Attendance a ON a.TID = t.TID AND a.CourseID = ?
        LEFT JOIN (
            SELECT TID, CourseID, Score
            FROM TraineeGrades tg
            JOIN GradeComponents gc ON tg.ComponentID = gc.ComponentID
            WHERE gc.ComponentName = 'PRETEST'
        ) tg_pretest ON tg_pretest.TID = t.TID AND tg_pretest.CourseID = ?
        LEFT JOIN (
            SELECT t.TID, t.CourseID, 
                ROUND(
                    IFNULL(att.AttendancePercentage/10, 0) + 
                    IFNULL(MAX(CASE WHEN gc.ComponentName = 'Participation' THEN tg.Score END), 0) +
                    CASE 
                        WHEN COUNT(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN 1 END) = 0 THEN 0
                        ELSE SUM(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN tg.Score ELSE 0 END) / 
                            COUNT(CASE WHEN gc.ComponentName LIKE 'Quiz%' AND tg.Score IS NOT NULL THEN 1 END)
                    END +
                    IFNULL(MAX(CASE WHEN gc.ComponentName = 'Final Exam' THEN tg.Score END), 0),
                    1
                ) AS Score
            FROM TraineeGrades t
            LEFT JOIN GradeComponents gc ON t.ComponentID = gc.ComponentID
            LEFT JOIN (
                SELECT 
                    TID, 
                    CourseID, 
                    ROUND(SUM(CASE WHEN Status IN ('Present', 'Excused') THEN 1 WHEN Status = 'Late' THEN 0.5 ELSE 0 END) / 
                        NULLIF(COUNT(*), 0) * 100, 1) AS AttendancePercentage
                FROM Attendance
                GROUP BY TID, CourseID
            ) att ON t.TID = att.TID AND t.CourseID = att.CourseID
            GROUP BY t.TID, t.CourseID
        ) total ON total.TID = t.TID AND total.CourseID = ?
        WHERE t.GroupID = ?
        GROUP BY t.TID, FullName, LGI
        ORDER BY FullName
    ");
    $stmt->bind_param("sssss", $selectedCourse, $selectedCourse, $selectedCourse, $selectedCourse, $selectedGroup);
    $stmt->execute();
    $traineeRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Filter Card -->
  <div class="content-box mb-4">
  <div class="row mb-3">
    <div class="col-12">
      <h5 class="card-title mb-3">
        <i class="bi bi-filter-square-fill me-2 text-primary"></i>
        Select Group & Course
      </h5>
    </div>
  </div>
  <form method="get" class="row g-3 align-items-end">
    <div class="col-md-5">
      <label class="form-label fw-bold">Group</label>
      <div class="input-group">
        <span class="input-group-text bg-primary text-white"><i class="bi bi-people-fill"></i></span>
        <select name="group_id" class="form-select" onchange="this.form.submit()">
          <?php foreach ($groups as $g): ?>
            <option value="<?= $g['GroupID'] ?>" <?= $selectedGroup == $g['GroupID'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['GroupName']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php if (!empty($courses)): ?>
    <div class="col-md-5">
      <label class="form-label fw-bold">Course</label>
      <div class="input-group">
        <span class="input-group-text bg-success text-white"><i class="bi bi-book-fill"></i></span>
        <select name="course_id" class="form-select" onchange="this.form.submit()">
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['CourseID'] ?>" <?= $selectedCourse == $c['CourseID'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['CourseName']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php endif; ?>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search me-2"></i> View
      </button>
    </div>
  </form>
</div>

<!-- Metric Cards -->
<?php if ($metrics): ?>
<div class="content-box mb-4">
  <div class="row mb-3">
    <div class="col-12">
      <h5 class="card-title mb-3">
        <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
        Group Performance Metrics
      </h5>
    </div>
  </div>
  <div class="row justify-content-center mb-3 text-center">
    <div class="col-md-3 mb-4">
      <div class="card shadow-sm border rounded-3">
        <div class="card-body p-3">
          <canvas id="scoreChart" height="130"></canvas>
          <h3 class="mt-2 fw-bold"><?= $metrics["AvgScore"] ?>%</h3>
          <p class="text-muted mb-0">Average Score</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card shadow-sm border rounded-3">
        <div class="card-body p-3">
          <canvas id="attChart" height="130"></canvas>
          <h3 class="mt-2 fw-bold"><?= $metrics["AvgAttendance"] ?>%</h3>
          <p class="text-muted mb-0">Average Attendance</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card shadow-sm border rounded-3">
        <div class="card-body p-3">
          <canvas id="lgiChart" height="130"></canvas>
          <h3 class="mt-2 fw-bold"><?= $metrics["AvgLGI"] ?>%</h3>
          <p class="text-muted mb-0">Average LGI</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Trainee Table -->
<?php if (!empty($traineeRows)): ?>
<div class="content-box">
  <div class="row mb-3">
    <div class="col-md-6">
      <h5 class="card-title">
        <i class="bi bi-people me-2 text-primary"></i>
        Trainee Performance
      </h5>
    </div>
    <div class="col-md-6 text-end">
      <button class="btn btn-sm btn-outline-primary me-2" onclick="window.print()">
        <i class="bi bi-printer"></i> Print Report
      </button>
      <a class="btn btn-sm btn-primary" href="#">
        <i class="bi bi-envelope"></i> Email Report
      </a>
    </div>
  </div>
  
  <div class="table-responsive">
    <table class="table table-hover table-striped">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th width="25%">Name</th>
          <th width="20%">Score</th>
          <th width="20%">Attendance</th>
          <th width="15%">LGI</th>
          <th width="15%">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($traineeRows as $i => $t): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <div class="d-flex align-items-center">
              <i class="bi bi-person-circle fs-4 me-2 text-secondary"></i>
              <div>
                <?= htmlspecialchars($t['FullName']) ?>
                <div class="small text-muted">ID: <?= $t['TID'] ?></div>
              </div>
            </div>
          </td>
          <td>
            <div class="progress mb-1" style="height: 8px;">
              <div class="progress-bar bg-<?= $t['Score'] < 70 ? 'danger' : ($t['Score'] >= 90 ? 'success' : 'primary') ?>" 
                   style="width: <?= $t['Score'] ?>%;" aria-valuenow="<?= $t['Score'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="<?= $t['Score'] < 70 ? 'text-danger' : ($t['Score'] >= 90 ? 'text-success' : '') ?>">
              <?= $t['Score'] ?>%
            </span>
          </td>
          <td>
            <div class="progress mb-1" style="height: 8px;">
              <div class="progress-bar bg-<?= $t['Attendance'] < 80 ? 'warning' : 'success' ?>" 
                   style="width: <?= $t['Attendance'] ?>%;" aria-valuenow="<?= $t['Attendance'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="<?= $t['Attendance'] < 80 ? 'text-warning' : 'text-success' ?>">
              <?= $t['Attendance'] ?>%
            </span>
          </td>
          <td>
            <div class="progress mb-1" style="height: 8px;">
              <div class="progress-bar bg-<?= $t['LGI'] < 50 ? 'danger' : ($t['LGI'] >= 80 ? 'success' : 'info') ?>" 
                   style="width: <?= $t['LGI'] ?>%;" aria-valuenow="<?= $t['LGI'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="<?= $t['LGI'] < 50 ? 'text-danger' : ($t['LGI'] >= 80 ? 'text-success' : '') ?>">
              <?= $t['LGI'] ?>%
            </span>
          </td>
          <td>
            <a class="btn btn-sm btn-outline-primary btn-icon me-1" href="trainee_dashboard.php?id=<?= $t['TID'] ?>">
              <i class="bi bi-bar-chart"></i>
            </a>
            <a class="btn btn-sm btn-primary btn-icon" href="trainee_dashboard.php?id=<?= $t['TID'] ?>">
              <i class="bi bi-file-earmark-text"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Score Chart
    const scoreCtx = document.getElementById('scoreChart').getContext('2d');
    new Chart(scoreCtx, {
        type: 'doughnut',
        data: {
            labels: ['Score', 'Remaining'],
            datasets: [{
                data: [<?= $metrics["AvgScore"] ?>, <?= 100 - $metrics["AvgScore"] ?>],
                backgroundColor: [
                    '#4361ee',  // Score color
                    '#f1f1f1'  // Remaining color
                ],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Score: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
    
    // Attendance Chart
    const attCtx = document.getElementById('attChart').getContext('2d');
    new Chart(attCtx, {
        type: 'doughnut',
        data: {
            labels: ['Attendance', 'Absent'],
            datasets: [{
                data: [<?= $metrics["AvgAttendance"] ?>, <?= 100 - $metrics["AvgAttendance"] ?>],
                backgroundColor: [
                    '#2ec4b6',  // Attendance color
                    '#f1f1f1'  // Absent color
                ],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Attendance: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
    
    // LGI Chart
    const lgiCtx = document.getElementById('lgiChart').getContext('2d');
    new Chart(lgiCtx, {
        type: 'doughnut',
        data: {
            labels: ['LGI', 'Remaining'],
            datasets: [{
                data: [<?= $metrics["AvgLGI"] ?>, <?= 100 - $metrics["AvgLGI"] ?>],
                backgroundColor: [
                    '#ff9f1c',  // LGI color
                    '#f1f1f1'  // Remaining color
                ],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `LGI: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
});
</script>
