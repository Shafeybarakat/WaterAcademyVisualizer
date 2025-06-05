<?php
$pageTitle = "Trainee Dashboard";
// Include the header - this also includes config.php and auth.php
include_once "../includes/header.php";

// RBAC guard: Only users with 'access_trainee_dashboard' permission can access this page.
if (!require_permission('access_trainee_dashboard', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

$traineeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($traineeId === 0) {
    // It's generally better to redirect or show a more specific error than just echo
    // Redirecting to a general listing or dashboard might be appropriate
    // For now, keeping the original echo for consistency, but consider improvement
    echo "<div class='bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4 mx-6'>
            <div class='flex'>
              <div class='flex-shrink-0'>
                <svg class='h-5 w-5 text-yellow-400' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'>
                  <path fill-rule='evenodd' d='M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z' clip-rule='evenodd' />
                </svg>
              </div>
              <div class='ml-3'>
                <p class='text-sm text-yellow-700'>No trainee selected. Please provide a valid trainee ID.</p>
              </div>
            </div>
          </div>";
    include_once "../includes/footer.php";
    exit; // Ensure script stops execution
}

// Get trainee basic information
// Get trainee basic information
$stmt = $conn->prepare("SELECT t.TID, CONCAT(t.FirstName, ' ', t.LastName) AS FullName, g.GroupName FROM Trainees t LEFT JOIN Groups g ON t.GroupID = g.GroupID WHERE t.TID = ?");
if (!$stmt) {
     // Handle prepare error, e.g., log it and show a generic error message
     echo "<div class='bg-red-50 border-l-4 border-red-500 p-4 my-4 mx-6'>
             <div class='flex'>
               <div class='flex-shrink-0'>
                 <svg class='h-5 w-5 text-red-400' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'>
                   <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd' />
                 </svg>
               </div>
               <div class='ml-3'>
                 <p class='text-sm text-red-700'>Error preparing trainee query: " . htmlspecialchars($conn->error) . "</p>
               </div>
             </div>
           </div>";
     include_once "../includes/footer.php";
     exit;
}
$stmt->bind_param("i", $traineeId);
$stmt->execute();
$traineeResult = $stmt->get_result();
$trainee = $traineeResult->fetch_assoc();
$stmt->close(); // Close the statement

if (!$trainee) {
    echo "<div class='bg-red-50 border-l-4 border-red-500 p-4 my-4 mx-6'>
            <div class='flex'>
              <div class='flex-shrink-0'>
                <svg class='h-5 w-5 text-red-400' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'>
                  <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd' />
                </svg>
              </div>
              <div class='ml-3'>
                <p class='text-sm text-red-700'>Trainee not found.</p>
              </div>
            </div>
          </div>";
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
     echo "<div class='bg-red-50 border-l-4 border-red-500 p-4 my-4 mx-6'>
             <div class='flex'>
               <div class='flex-shrink-0'>
                 <svg class='h-5 w-5 text-red-400' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'>
                   <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd' />
                 </svg>
               </div>
               <div class='ml-3'>
                 <p class='text-sm text-red-700'>Error preparing course metrics query: " . htmlspecialchars($conn->error) . "</p>
               </div>
             </div>
           </div>";
     include_once "../includes/footer.php";
     exit;
}
$stmt->bind_param("i", $traineeId);
$stmt->execute();
$courseResult = $stmt->get_result();
$course = $courseResult->fetch_assoc();
$stmt->close(); // Close the statement

?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($trainee['FullName']) ?> – <?= htmlspecialchars($trainee['GroupName'] ?? 'No Group Assigned') ?></h1>

    <?php if ($course): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Score Card -->
      <?php
        $cards = [
          ['Score', 'bx bx-chart-pie text-blue-500', $course['AvgScore'] ?? 0, 'scoreChart'],
          ['Attendance', 'bx bx-calendar-check text-green-500', $course['Attendance'] ?? 0, 'attChart'],
          ['LGI', 'bx bx-line-chart text-yellow-500', $course['LGI'] ?? 0, 'lgiChart'],
        ];
        foreach ($cards as $c) {
          [$title, $iconClass, $value, $chartId] = $c;
          include __DIR__ . '/../includes/components/kpi-card.php';
        }
      ?>
      
      <!-- Status Card -->
      <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
        <div class="flex items-center justify-between w-full mb-2">
          <h2 class="text-lg font-semibold text-gray-700">Status</h2>
          <i class="bx bx-info-circle text-gray-500 text-xl"></i>
        </div>
        <div class="mt-4 text-center">
          <p class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($course['EnrollmentStatus'] ?? 'N/A') ?></p>
        </div>
      </div>
    </div>

    <!-- Using chart utilities from app.js -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize charts using the utility function from app.js
      initDoughnutChart('scoreChart', <?= $course['AvgScore'] ?? 0 ?>, '#3B82F6');
      initDoughnutChart('attChart', <?= $course['Attendance'] ?? 0 ?>, '#10B981');
      initDoughnutChart('lgiChart', <?= $course['LGI'] ?? 0 ?>, '#F59E0B');
    });
    </script>

    <?php else: ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-blue-700">This trainee is not currently associated with any course summary data or has not started any courses.</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>
