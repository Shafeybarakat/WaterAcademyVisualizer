<?php
// attendance_grades.php - Data Entry Page for Attendance and Grades
$pageTitle = "Data Entry";

// Authentication, config and header
include_once "../includes/header.php";

// Enforce permissions
enforceAnyPermission(['enter_grades', 'enter_attendance']);
// If execution continues, permissions are granted.

// Fetch all groups
$groupsQuery = "SELECT GroupID, GroupName FROM `Groups` ORDER BY GroupName";
$groupsResult = $conn->query($groupsQuery);
$groups = $groupsResult->fetch_all(MYSQLI_ASSOC);
?>

<!-- Main Content -->
<div class="action-card mb-6">
    <div class="action-card-header">
        <i class="bx bx-spreadsheet text-3xl text-blue-600 action-card-icon"></i>
        <h5 class="action-card-title">Data Entry</h5>
    </div>
    
    <div class="p-6">
        <!-- Filters Section -->
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg mb-6">
            <h6 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Select Group and Course</h6>
            
            <form method="get" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Group Select -->
                    <div>
                        <label for="group_select" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Group</label>
                        <select id="group_select" name="group_id" class="block w-full pl-3 pr-10 py-4 text-base border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 font-medium sm:text-sm rounded-md">
                            <option value="" class="py-2 bg-gray-50 dark:bg-gray-800">-- Select Group --</option>
                            <?php foreach($groups as $group): ?>
                                <option value="<?= $group['GroupID'] ?>" class="py-2 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700" <?= (isset($_GET['group_id']) && $_GET['group_id'] == $group['GroupID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($group['GroupName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Course Select -->
                    <div>
                        <label for="course_select" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Course</label>
                        <select id="course_select" name="course_id" class="block w-full pl-3 pr-10 py-4 text-base border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 font-medium sm:text-sm rounded-md" <?= !isset($_GET['group_id']) ? 'disabled' : '' ?>>
                            <option value="" class="py-2 bg-gray-50 dark:bg-gray-800">-- Select Course --</option>
                            <!-- Courses will be populated via JavaScript -->
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button type="submit" class="action-card-button shining-btn w-4/5">
                        <i class="bx bx-filter-alt mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Action Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <!-- Grades Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md overflow-hidden">
                <div class="bg-blue-600 dark:bg-blue-800 text-white px-6 py-4">
                    <h3 class="text-lg font-medium"><i class="bx bx-table mr-2"></i> Grades Entry</h3>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Enter and manage student grades including quizzes, participation, and final exam scores.</p>
                    
                    <div class="flex justify-center">
                        <button type="button" class="action-card-button shining-btn w-4/5" onclick="openGradesModal(<?= isset($_GET['group_id']) && isset($_GET['course_id']) ? 'getGroupCourseId(' . $_GET['group_id'] . ', \'' . $_GET['course_id'] . '\')' : 'null' ?>)" <?= (!isset($_GET['group_id']) || !isset($_GET['course_id'])) ? 'disabled' : '' ?>>
                            <i class="bx bx-edit mr-2"></i> Enter/Edit Grades
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md overflow-hidden">
                <div class="bg-green-600 dark:bg-green-800 text-white px-6 py-4">
                    <h3 class="text-lg font-medium"><i class="bx bx-calendar-check mr-2"></i> Attendance Entry</h3>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Track student attendance including present hours, excused absences, and tardiness.</p>
                    
                    <div class="flex justify-center">
                        <button type="button" class="action-card-button shining-btn w-4/5" onclick="openAttendanceModal(<?= isset($_GET['group_id']) && isset($_GET['course_id']) ? 'getGroupCourseId(' . $_GET['group_id'] . ', \'' . $_GET['course_id'] . '\')' : 'null' ?>)" <?= (!isset($_GET['group_id']) || !isset($_GET['course_id'])) ? 'disabled' : '' ?>>
                            <i class="bx bx-time mr-2"></i> Enter/Edit Attendance
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Grades Modal -->
<div x-data="{ open: false }" id="gradesModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="gradesModalLabel">
                    <i class="bx bx-edit-alt mr-2"></i> Grade Entry
                </h5>
                <div class="flex items-center space-x-4">
                    <span id="gradesSaveStatus" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Ready</span>
                    <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <!-- Loading indicator -->
                <div id="gradesLoading" class="flex justify-center items-center min-h-[200px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
                
                <!-- No trainees message -->
                <div id="gradesNoTraineesMessage" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded" role="alert">
                    <div class="flex items-center">
                        <i class="bx bx-info-circle mr-2 text-xl"></i>
                        <span>No trainees found for this course. Please add trainees to the group first.</span>
                    </div>
                </div>
                
                <!-- Error message -->
                <div id="gradesErrorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <div class="flex items-center">
                        <i class="bx bx-error-circle mr-2 text-xl"></i>
                        <span id="gradesErrorText"></span>
                    </div>
                </div>
                
                <!-- Grade entry form -->
                <form id="gradeEntryForm" class="hidden">
                    <input type="hidden" id="grade_group_course_id" name="group_course_id">
                    
                    <div class="mb-4 flex justify-end space-x-2">
                        <div class="relative w-64">
                            <input type="text" id="gradesSearchInput" placeholder="Search trainee..." class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bx bx-search text-gray-500 dark:text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th data-sort="first-name" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        First Name <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th data-sort="last-name" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        Last Name <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th data-sort="gov-id" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        Gov ID <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Pre-Test<br><small>(Optional)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Attendance<br><small>(10 Points)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Participation<br><small>(10 Points)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Quiz 1<br><small>(30 Points)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Quiz 2<br><small>(Optional)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Quiz 3<br><small>(Optional)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Quiz Avg<br><small>(30 Points)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Final Exam<br><small>(50 Points)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total<br><small>(100 Points)</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="gradesTableBody" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Rows will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between">
                <div>
                    <button type="button" id="resetGradesBtn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <i class="bx bx-reset mr-1"></i> Reset Changes
                    </button>
                </div>
                <div class="space-x-2">
                    <button type="button" @click="open = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                        Close
                    </button>
                    <button type="button" id="saveGradesBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        <i class="bx bx-save mr-1"></i> Save Grades
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div x-data="{ open: false }" id="attendanceModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-green-600 dark:bg-green-700 text-white px-6 py-4 flex justify-between items-center">
                <h5 class="text-xl font-medium" id="attendanceModalLabel">
                    <i class="bx bx-calendar-check mr-2"></i> Attendance Entry
                </h5>
                <div class="flex items-center space-x-4">
                    <span id="attendanceSaveStatus" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Ready</span>
                    <button type="button" @click="open = false" class="text-white hover:text-gray-200">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <!-- Loading indicator -->
                <div id="attendanceLoading" class="flex justify-center items-center min-h-[200px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-green-500"></div>
                </div>
                
                <!-- No trainees message -->
                <div id="attendanceNoTraineesMessage" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded" role="alert">
                    <div class="flex items-center">
                        <i class="bx bx-info-circle mr-2 text-xl"></i>
                        <span>No trainees found for this course. Please add trainees to the group first.</span>
                    </div>
                </div>
                
                <!-- Error message -->
                <div id="attendanceErrorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <div class="flex items-center">
                        <i class="bx bx-error-circle mr-2 text-xl"></i>
                        <span id="attendanceErrorText"></span>
                    </div>
                </div>
                
                <!-- Attendance entry form -->
                <form id="attendanceEntryForm" class="hidden">
                    <input type="hidden" id="attendance_group_course_id" name="group_course_id">
                    
                    <div class="mb-4 flex justify-end space-x-2">
                        <div class="relative w-64">
                            <input type="text" id="attendanceSearchInput" placeholder="Search trainee..." class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bx bx-search text-gray-500 dark:text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th data-sort="first-name" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        First Name <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th data-sort="last-name" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        Last Name <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th data-sort="gov-id" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                        Gov ID <i class="bx bx-sort-alt-2 ml-1"></i>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Present Hours<br><small>(P)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Excused Hours<br><small>(E)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Late Hours<br><small>(L)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Absent Hours<br><small>(A)</small>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Points
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total Sessions
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Attendance %
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Rows will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between">
                <div>
                    <button type="button" id="resetAttendanceBtn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <i class="bx bx-reset mr-1"></i> Reset Changes
                    </button>
                </div>
                <div class="space-x-2">
                    <button type="button" @click="open = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                        Close
                    </button>
                    <button type="button" id="saveAttendanceBtn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                        <i class="bx bx-save mr-1"></i> Save Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
// Helper function to get the group_course_id based on group_id and course_id
function getGroupCourseId(groupId, courseId) {
    // For now, return a temporary value that will be looked up via AJAX in a real implementation
    return `${groupId}_${courseId}`;
}

// Function to filter table based on search term
function filterTable(tableBodySelector, searchTerm) {
    const rows = document.querySelectorAll(`${tableBodySelector} tr`);
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to sort table
function sortTable(tbody, sortField, direction) {
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        // Get values based on sort field
        switch (sortField) {
            case 'first-name':
                aValue = a.querySelector('input:nth-child(1)').value.toLowerCase();
                bValue = b.querySelector('input:nth-child(1)').value.toLowerCase();
                break;
            case 'last-name':
                aValue = a.querySelector('td:nth-child(2) input').value.toLowerCase();
                bValue = b.querySelector('td:nth-child(2) input').value.toLowerCase();
                break;
            case 'gov-id':
                aValue = a.querySelector('td:nth-child(3) input').value.toLowerCase();
                bValue = b.querySelector('td:nth-child(3) input').value.toLowerCase();
                break;
            default:
                return 0;
        }
        
        // Compare based on direction
        if (direction === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    // Re-append rows in new order
    rows.forEach(row => tbody.appendChild(row));
}

// Setup paste handling for grade entry
function setupPasteHandling() {
    // Set up paste event for grade inputs
    document.addEventListener('paste', function(e) {
        // Only handle paste events for grade inputs
        if (!e.target.classList.contains('grade-input') && !e.target.classList.contains('attendance-input')) {
            return;
        }
        
        // Get paste data
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        
        // If paste data contains tab or newline, it might be from Excel or spreadsheet
        if (pasteData.includes('\t') || pasteData.includes('\n')) {
            e.preventDefault();
            
            // Split into rows and cells
            const rows = pasteData.trim().split(/[\n\r]+/);
            const table = e.target.closest('table');
            const startRow = e.target.closest('tr');
            const startCellIndex = Array.from(startRow.cells).findIndex(cell => cell.contains(e.target));
            
            // Apply paste data to table cells
            rows.forEach((rowData, rowIndex) => {
                const cells = rowData.split('\t');
                const currentRow = rowIndex === 0 ? startRow : startRow.nextElementSibling;
                
                if (currentRow) {
                    cells.forEach((cellData, cellIndex) => {
                        const cellToFill = currentRow.cells[startCellIndex + cellIndex];
                        if (cellToFill) {
                            const input = cellToFill.querySelector('input:not([readonly])');
                            if (input) {
                                input.value = cellData.trim();
                                // Trigger change event for calculations
                                const event = new Event('input', { bubbles: true });
                                input.dispatchEvent(event);
                            }
                        }
                    });
                }
            });
        }
    });
}
</script>
</file_content>
<environment_details>
# VSCode Visible Files
dashboards/index.php

# VSCode Open Tabs
dashboards/report_group_performance.php
dashboards/reports.php
dashboards/index.php

# Current Time
6/6/2025, 12:51:13 AM (Asia/Riyadh, UTC+3:00)

# Context Window Usage
389,691 / 1,048.576K tokens used (37%)

# Current Mode
ACT MODE
</environment_details>
