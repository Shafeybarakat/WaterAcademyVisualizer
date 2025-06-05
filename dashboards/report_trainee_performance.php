<?php
$pageTitle = "Trainee Performance Report"; // Set page title
require_once 'report_functions.php'; // PHP functions to fetch data

// Include the header - this also includes config.php and auth.php
include_once '../includes/header.php';

// RBAC guard
if (!require_permission('access_trainee_reports', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

$user_role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"]; // Assuming user ID is stored in session

// Example dummy data for demonstration. Replace with actual PHP variables.
$avgPreTest = 75;
$avgQuiz = 80;
$avgFinal = 85;
$avgLGI = 70;

// Example dummy data for demonstration. Replace with actual PHP data from database.
$trainees = [
    ['Name' => 'John Doe', 'GroupName' => 'Group Alpha', 'PreTestScore' => 70, 'QuizAverage' => 78, 'FinalExamScore' => 85, 'AttendancePercentage' => 92, 'LGI' => 65],
    ['Name' => 'Jane Smith', 'GroupName' => 'Group Beta', 'PreTestScore' => 80, 'QuizAverage' => 85, 'FinalExamScore' => 90, 'AttendancePercentage' => 95, 'LGI' => 75],
];

include_once '../includes/header.php';
?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <!-- KPI Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <?php
      $cards = [
        ['Pre-Test Avg', 'bx bx-pen-alt text-indigo-500', $avgPreTest, 'chartPreTest'],
        ['Quiz Avg', 'bx bx-question-circle text-purple-500', $avgQuiz, 'chartQuizAvg'],
        ['Final Exam Avg', 'bx bx-file-alt text-red-500', $avgFinal, 'chartFinalExam'],
        ['LGI', 'bx bx-line-chart text-yellow-500', $avgLGI, 'chartLGI'],
      ];
      foreach ($cards as $c) {
        [$title, $iconClass, $value, $chartId] = $c;
        // For items without charts, set $chartId = '' and modify kpi-card to hide canvas when ID is empty
        include __DIR__ . '/../includes/components/kpi-card.php';
      }
      ?>
    </div>

    <!-- Trainees Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainee Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pre-Test Score</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz Avg</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Exam</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LGI</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($trainees as $t): ?>
            <tr>
              <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($t['Name']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= htmlspecialchars($t['GroupName']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $t['PreTestScore'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $t['QuizAverage'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $t['FinalExamScore'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $t['AttendancePercentage'] ?>%</td>
              <td class="px-4 py-4 whitespace-nowrap"><?= $t['LGI'] ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
