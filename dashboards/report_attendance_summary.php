<?php
$pageTitle = "Attendance Summary Report"; // Set page title
require_once 'report_functions.php'; // PHP functions to fetch data

// Include the header - this also includes config.php and auth.php

// RBAC guard
if (!require_permission('access_attendance_summary', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"]; // Assuming user ID is stored in session

// Example dummy data for demonstration. Replace with actual PHP data from database.
$attendanceSummary = [
    ['GroupName' => 'Group Alpha', 'CourseName' => 'Course A', 'PresentCount' => 15, 'AbsentCount' => 5, 'AttendancePercentage' => 75],
    ['GroupName' => 'Group Beta', 'CourseName' => 'Course B', 'PresentCount' => 18, 'AbsentCount' => 2, 'AttendancePercentage' => 90],
];

include_once '../includes/header.php';
?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <!-- Bar Chart for Attendance % per Group -->
    <div class="bg-white rounded-lg shadow p-4 mb-8 w-full">
      <canvas id="chartAttendanceSummary" data-labels='<?= json_encode(array_column($attendanceSummary, 'GroupName')) ?>' data-values='<?= json_encode(array_column($attendanceSummary, 'AttendancePercentage')) ?>' class="w-full h-64"></canvas>
    </div>

    <!-- Attendance Summary Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present Count</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent Count</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($attendanceSummary as $row): ?>
            <tr>
              <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['GroupName']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($row['CourseName']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $row['PresentCount'] ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $row['AbsentCount'] ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $row['AttendancePercentage'] ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
