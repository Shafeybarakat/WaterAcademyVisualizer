<?php
$pageTitle = "Attendance Entry"; // Set page title
// Include the header - this also includes config.php and auth.php
include_once("../includes/header.php");

// RBAC guard
if (!require_permission('record_attendance', '../login.php')) {
    // If require_permission returns false (meaning user is logged in but lacks permission)
    // Display the error message set in session by require_permission
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Check if group_course_id is provided
$groupCourseId = isset($_GET['group_course_id']) ? intval($_GET['group_course_id']) : 0;

// Get groups and courses assigned to this instructor
$query = "
    SELECT 
        g.GroupID, 
        g.GroupName, 
        gc.ID as GroupCourseID, 
        c.CourseID, 
        c.CourseName
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    JOIN Courses c ON gc.CourseID = c.CourseID
    WHERE gc.InstructorID = ?
    ORDER BY g.GroupName, c.CourseName
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

// Organize results by group
$groups = [];
while ($row = $result->fetch_assoc()) {
    $groupId = $row['GroupID'];
    
    if (!isset($groups[$groupId])) {
        $groups[$groupId] = [
            'GroupID' => $row['GroupID'],
            'GroupName' => $row['GroupName'],
            'Courses' => []
        ];
    }
    
    $groups[$groupId]['Courses'][] = [
        'GroupCourseID' => $row['GroupCourseID'],
        'CourseID' => $row['CourseID'],
        'CourseName' => $row['CourseName']
    ];
}

// Dummy data for attendanceRows for demonstration. Replace with actual data fetching.
$attendanceRows = [
    ['Name' => 'Trainee One', 'GroupName' => 'Group A', 'PresentHours' => 10, 'AbsentHours' => 2, 'TakenSessions' => 12, 'AttendancePercentage' => 83.3, 'AttendanceID' => 1],
    ['Name' => 'Trainee Two', 'GroupName' => 'Group A', 'PresentHours' => 11, 'AbsentHours' => 1, 'TakenSessions' => 12, 'AttendancePercentage' => 91.7, 'AttendanceID' => 2],
];

?>

<div class="ml-0 md:ml-64 transition-all duration-200">
  <main class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle); ?></h1>

    <?php if (empty($groups)): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">No courses assigned!</strong>
            <span class="block sm:inline">You currently don't have any courses assigned to you. Please contact an administrator if you believe this is an error.</span>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Course Selection Form -->
    <?php if ($groupCourseId <= 0): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Course</h2>
        <form id="courseSelectionForm" method="get" action="attendance.php">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="group_select" class="block text-sm font-medium text-gray-700">Group</label>
                    <select id="group_select" name="group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">Select a group</option>
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['GroupID']; ?>"><?php echo htmlspecialchars($group['GroupName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="group_course_id" class="block text-sm font-medium text-gray-700">Course</label>
                    <select id="group_course_id" name="group_course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required disabled>
                        <option value="">Select a course</option>
                    </select>
                </div>
            </div>
            <div class="text-right">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Continue
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <!-- Attendance Entry Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Enter Attendance</h2>
        <div id="loading" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-blue-500" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-gray-600">Loading trainees...</p>
        </div>
        
        <form id="attendanceEntryForm" method="post" action="submit_attendance.php">
            <input type="hidden" name="group_course_id" value="<?php echo $groupCourseId; ?>">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainee</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present Hours</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late Hours</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Excused Hours</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent Hours</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sessions</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="traineesTableBody">
                        <?php foreach ($attendanceRows as $row): ?>
                            <tr x-data="{ present: <?= $row['PresentHours'] ?>, absent: <?= $row['AbsentHours'] ?> }">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <strong><?= htmlspecialchars($row['Name']) ?></strong>
                                    <input type="hidden" name="trainee_ids[]" value="<?= $row['AttendanceID'] ?>">
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="number" class="w-20 border rounded px-2 py-1 text-sm" 
                                           x-model.number="present" name="present_hours[]" min="0" step="0.5" required>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="number" class="w-20 border rounded px-2 py-1 text-sm" 
                                           name="late_hours[]" min="0" step="0.5" value="0" required>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="number" class="w-20 border rounded px-2 py-1 text-sm" 
                                           name="excused_hours[]" min="0" step="0.5" value="0" required>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="number" class="w-20 border rounded px-2 py-1 text-sm" 
                                           x-model.number="absent" name="absent_hours[]" min="0" step="0.5" required>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="number" class="w-20 border rounded px-2 py-1 text-sm" 
                                           name="taken_sessions[]" min="1" step="1" value="<?= $row['TakenSessions'] ?>" required>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span x-text="((present + (0.5 * 0) + 0) / (present + 0 + 0 + absent) * 100).toFixed(1) + '%'"></span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button type="button" @click="$dispatch('save-attendance', { id: <?= $row['AttendanceID'] ?>, present: present, absent: absent })" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">Save</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-right">
                <a href="attendance.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Back to Course Selection</a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Save All Attendance</button>
            </div>
        </form>
        
        <div id="noTraineesMessage" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mt-4" role="alert" style="display: none;">
            <strong class="font-bold">No trainees found!</strong>
            <span class="block sm:inline">There are no trainees enrolled in this course. Please check the group assignments.</span>
        </div>
        
        <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert" style="display: none;">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline" id="errorText"></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
  </main>
</div>

<?php include_once '../includes/footer.php'; ?>
