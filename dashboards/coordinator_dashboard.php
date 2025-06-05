<?php
$pageTitle = "Coordinator Dashboard";
// Include the header - this also includes config.php and auth.php
include_once '../includes/header.php';

// RBAC guard
if (!require_permission('access_dashboard', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Get coordinator ID
$coordinatorId = $_SESSION['user_id'];

// Dummy data for demonstration. Replace with actual PHP variables.
$groupCount = 5;
$totalTrainees = 120;
$avgAttendance = 88;
$avgFinalScore = 82;
?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <?php
      $coordinatorKPIs = [
        ['Groups', 'bx bx-group text-primary-wa', $groupCount, ''],
        ['Trainees', 'bx bx-user text-info-500', $totalTrainees, ''],
        ['Attendance', 'bx bx-calendar-check text-green-500', $avgAttendance, ''],
        ['Final Scores', 'bx bx-bar-chart-alt-2 text-yellow-500', $avgFinalScore, ''],
      ];
      foreach ($coordinatorKPIs as $k) {
        [$title, $iconClass, $value, $chartId] = $k;
        // For items without charts, set $chartId = '' and modify kpi-card to hide canvas when ID is empty
        include __DIR__ . '/../includes/components/kpi-card.php';
      }
      ?>
    </div>

    <!-- Placeholder for other coordinator-specific content like group list or reports overview -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">My Groups Overview</h2>
      <p class="text-gray-600">Details about your assigned groups and their performance will appear here.</p>
      <!-- Example: A simple list of groups -->
      <ul class="list-disc list-inside mt-4">
        <li>Group X - Program A (50 trainees)</li>
        <li>Group Y - Program B (30 trainees)</li>
      </ul>
    </div>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
