<?php
$pageTitle = "Group Performance Report"; // Set page title
require_once __DIR__ . '/../includes/auth.php';
require_permission('access_group_reports', '../login.php'); // RBAC guard
require_once __DIR__ . '/../includes/config.php';
require_once 'report_functions.php'; // PHP functions to fetch data

$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"]; // Assuming user ID is stored in session

// --- Fetch initial data for filters (Groups) ---
$group_sql = "SELECT GroupID, GroupName FROM Groups";
$group_params = [];
$group_types = "";

// Apply filtering for Coordinators
if ($user_role === "Coordinator") {
    $group_sql .= " WHERE CoordinatorID = ?";
    $group_params[] = $user_id;
    $group_types .= "i";
} elseif ($user_role === "Instructor") {
    // Instructors see groups they teach in
    $group_sql = "SELECT DISTINCT g.GroupID, g.GroupName 
                  FROM Groups g
                  JOIN GroupCourses gc ON g.GroupID = gc.GroupID 
                  WHERE gc.InstructorID = ?";
    $group_params[] = $user_id;
    $group_types .= "i";
}

$group_sql .= " ORDER BY GroupName";

$group_stmt = $conn->prepare($group_sql);
$groups = [];
if ($group_stmt) {
    if (!empty($group_params)) {
        $group_stmt->bind_param($group_types, ...$group_params);
    }
    $group_stmt->execute();
    $group_result = $group_stmt->get_result();
    $groups = $group_result->fetch_all(MYSQLI_ASSOC);
    $group_stmt->close();
} else {
    error_log("Error preparing group statement: " . $conn->error);
    // Handle error appropriately
}

// Example dummy data for demonstration. Replace with actual PHP variables.
$avgScore = 85;
$avgAttendance = 92;
$avgLGI = 70;

include_once '../includes/header.php';
?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <!-- KPI Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <?php
        $cards = [
          ['Avg Score', 'bx bx-chart-pie text-primary-wa', $avgScore, 'chartAvgScore'],
          ['Avg Attendance', 'bx bx-calendar-check text-green-500', $avgAttendance, 'chartAvgAttendance'],
          ['Avg LGI', 'bx bx-line-chart text-yellow-500', $avgLGI, 'chartAvgLGI'],
        ];
        foreach ($cards as $c) {
          [$title, $iconClass, $value, $chartId] = $c;
          include __DIR__ . '/../includes/components/kpi-card.php';
        }
      ?>
    </div>

    <!-- Groups Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"># Trainees</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Attendance</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg LGI</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php
          // Example dummy data for demonstration. Replace with actual PHP data from $groupsData.
          $groups = [
              ['GroupName' => 'Group Alpha', 'TraineeCount' => 20, 'AvgFinalExamScore' => 88, 'AvgAttendance' => 95, 'AvgLGI' => 70],
              ['GroupName' => 'Group Beta', 'TraineeCount' => 15, 'AvgFinalExamScore' => 75, 'AvgAttendance' => 88, 'AvgLGI' => 50],
          ];
          foreach ($groups as $g): ?>
            <tr>
              <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($g['GroupName']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $g['TraineeCount'] ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgFinalExamScore'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgAttendance'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $g['AvgLGI'] ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
