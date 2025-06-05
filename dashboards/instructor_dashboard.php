<?php
$pageTitle = "Instructor Dashboard";
// Include the header - this also includes config.php and auth.php
include_once '../includes/header.php';

// RBAC guard
if (!require_permission('access_dashboard', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Dummy data for demonstration. Replace with actual PHP variables.
$courseCount = 5;
$pendingGradesCount = 3;
$pendingAttendanceCount = 2;
$courseCompletionPct = 75; // Example for a chart

include_once '../includes/header.php';
?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <!-- KPI Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <?php
      // Example KPI array for instructor:
      $instructorKPIs = [
        ['My Courses', 'bx bx-book text-indigo-500', $courseCount, ''],
        ['Pending Grades', 'bx bx-edit text-red-500', $pendingGradesCount, ''],
        ['Pending Attendance', 'bx bx-calendar-day text-green-500', $pendingAttendanceCount, ''],
      ];
      foreach ($instructorKPIs as $k) {
        [$title, $iconClass, $value, $chartId] = $k;
        // For items without charts, set $chartId = '' and modify kpi-card to hide canvas when ID is empty
        include __DIR__ . '/../includes/components/kpi-card.php';
      }
      ?>
    </div>

    <!-- Placeholder for other instructor-specific content like recent activities or course list -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Assigned Courses Overview</h2>
      <p class="text-gray-600">Details about your assigned courses will appear here.</p>
      <!-- Example: A simple list of courses -->
      <ul class="list-disc list-inside mt-4">
        <li>Course A - Group 1 (In Progress)</li>
        <li>Course B - Group 2 (Completed)</li>
        <li>Course C - Group 3 (Upcoming)</li>
      </ul>
    </div>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
