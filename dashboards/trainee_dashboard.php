<?php
$pageTitle = "Trainee Dashboard";
require_once "../includes/auth.php"; // Assuming auth is needed based on other files
require_once "../includes/config.php";
include_once "../includes/header.php";
include_once "../includes/sidebar.php";
$traineeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($traineeId === 0) {
    // It's generally better to redirect or show a more specific error than just echo
    // Redirecting to a general listing or dashboard might be appropriate
    // For now, keeping the original echo for consistency, but consider improvement
    echo "<div class='alert alert-warning m-4'>No trainee selected. Please provide a valid trainee ID.</div>";
    include_once "../includes/footer.php";
    exit; // Ensure script stops execution
}

// Get trainee basic information
// Get trainee basic information
$stmt = $conn->prepare("SELECT t.TID, CONCAT(t.FirstName, ' ', t.LastName) AS FullName, g.GroupName FROM Trainees t LEFT JOIN Groups g ON t.GroupID = g.GroupID WHERE t.TID = ?");
if (!$stmt) {
     // Handle prepare error, e.g., log it and show a generic error message
     echo "<div class='alert alert-danger m-4'>Error preparing trainee query: " . htmlspecialchars($conn->error) . "</div>";
     include_once "../includes/footer.php";
     exit;
}
$stmt->bind_param("i", $traineeId);
$stmt->execute();
$traineeResult = $stmt->get_result();
$trainee = $traineeResult->fetch_assoc();
$stmt->close(); // Close the statement

if (!$trainee) {
    echo "<div class='alert alert-danger m-4'>Trainee not found.</div>";
    include_once "../includes/footer.php";
    exit;
}

// Get first course and its metrics using StudentGradeSummary
// MODIFIED QUERY: Replaced Enrollments with StudentGradeSummary
// Get first course and its metrics using View_TraineePerformanceDetails
$courseQuery = "
    SELECT
        c.CourseID,
        c.CourseName,
        ROUND(AVG(tg.Score),1) AS AvgScore,
        ROUND(AVG(a.AttendancePercentage),1) AS Attendance,
        ROUND(AVG(vtpd.LGI),1) AS LGI,
        e.Status AS EnrollmentStatus
    FROM Enrollments e
    JOIN GroupCourses gc ON e.GroupCourseID = gc.ID
    JOIN Courses c ON gc.CourseID = c.CourseID
    JOIN Trainees t ON t.TID = e.TID
    LEFT JOIN TraineeGrades tg ON tg.GroupCourseID = e.GroupCourseID AND tg.TID = e.TID
    LEFT JOIN Attendance a ON a.GroupCourseID = e.GroupCourseID AND a.TID = e.TID
    LEFT JOIN View_TraineePerformanceDetails vtpd ON vtpd.GroupCourseID = e.GroupCourseID AND vtpd.TID = e.TID
    WHERE t.TID = ?
    GROUP BY c.CourseID, c.CourseName, e.Status
    ORDER BY c.CourseName ASC
    LIMIT 1
";

$stmt = $conn->prepare($courseQuery);
if (!$stmt) {
     // Handle prepare error
     echo "<div class='alert alert-danger m-4'>Error preparing course metrics query: " . htmlspecialchars($conn->error) . "</div>";
     include_once "../includes/footer.php";
     exit;
}
$stmt->bind_param("i", $traineeId);
$stmt->execute();
$courseResult = $stmt->get_result();
$course = $courseResult->fetch_assoc();
$stmt->close(); // Close the statement

?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-box mb-4">
      <h5><?= htmlspecialchars($trainee['FullName']) ?> – <?= htmlspecialchars($trainee['GroupName'] ?? 'No Group Assigned') ?></h5>
    </div>

    <?php if ($course): ?>
    <div class="card-grid justify-content-center mb-5">
      <!-- Score Card -->
      <div class="stat-card small">
        <div class="card-header blue"><i class="bi bi-graph-up"></i> Score</div>
        <canvas id="scoreChart"></canvas>
        <h3 class="mt-2 text-center"><?= $course['AvgScore'] ?? 0 ?>%</h3>
      </div>

      <!-- Attendance Card -->
      <div class="stat-card small">
        <div class="card-header green"><i class="bi bi-person-check"></i> Attendance</div>
        <canvas id="attChart"></canvas>
        <h3 class="mt-2 text-center"><?= $course['Attendance'] ?? 0 ?>%</h3>
      </div>

      <!-- LGI Card -->
      <div class="stat-card small">
        <div class="card-header orange"><i class="bi bi-lightbulb"></i> LGI</div>
        <canvas id="lgiChart"></canvas>
        <h3 class="mt-2 text-center"><?= $course['LGI'] ?? 0 ?>%</h3>
      </div>

      <!-- Status Card -->
      <div class="stat-card small">
        <div class="card-header dark"><i class="bi bi-info-circle"></i> Status</div>
        <div class="value-large mt-4 text-center"><?= htmlspecialchars($course['EnrollmentStatus'] ?? 'N/A') ?></div>
        <!-- Removed fixed margin top 5 and added text-center -->
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Ensure value is a number and within 0-100
      const validateValue = (val) => {
        const num = Number(val);
        if (isNaN(num)) return 0;
        return Math.max(0, Math.min(100, num));
      };

      const scoreValue = validateValue(<?= $course['AvgScore'] ?? 0 ?>);
      const attendanceValue = validateValue(<?= $course['Attendance'] ?? 0 ?>);
      const lgiValue = validateValue(<?= $course['LGI'] ?? 0 ?>);

      function drawDonut(id, value, color) {
        const ctx = document.getElementById(id);
        if (!ctx) return; // Don't proceed if canvas doesn't exist
        new Chart(ctx.getContext('2d'), {
          type: 'doughnut',
          data: {
            datasets: [{
              data: [value, 100 - value],
              backgroundColor: [color, '#e9ecef'], // Use a light grey for the remainder
              borderColor: '#ffffff', // Add a white border for separation
              borderWidth: 2 // Make border visible
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false, // Allow chart to fill container better
            cutout: '70%', // Adjust doughnut thickness
            rotation: -90, // Start from the top
            circumference: 180, // Make it a semi-circle gauge? Or keep 360 for full donut? Let's keep 360.
            plugins: {
              legend: { display: false },
              tooltip: { enabled: false } // Disable tooltips for simple display
            }
          }
        });
      }
      drawDonut("scoreChart", scoreValue, "#4361ee");
      drawDonut("attChart", attendanceValue, "#00b894"); // Updated green color
      drawDonut("lgiChart", lgiValue, "#e17055");
    });
    </script>

    <?php else: ?>
    <div class="alert alert-info m-4">This trainee is not currently associated with any course summary data or has not started any courses.</div>
    <?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>
