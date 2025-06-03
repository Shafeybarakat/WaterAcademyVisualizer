<?php
$pageTitle = "Attendance & Grades"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php");

// Protect this page - only users with appropriate permissions can access
if (!isLoggedIn() || (!hasPermission('record_grades') && !hasPermission('record_attendance'))) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

// Include the header - this also includes the sidebar
include_once("../includes/header.php");

// Get groups that have courses with attendance and grades data
$groupsResult = $conn->query("
    SELECT DISTINCT g.GroupID, g.GroupName 
    FROM `Groups` g
    JOIN GroupCourses gc ON g.GroupID = gc.GroupID
    ORDER BY g.GroupName
");
$groups = $groupsResult->fetch_all(MYSQLI_ASSOC);
$selectedGroup = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int)$_GET['group_id'] : null;
$selectedCourse = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? $_GET['course_id'] : null;

// When a Group is chosen, fetch its Courses
$courses = [];
if (!empty($selectedGroup)) {
    $stmt = $conn->prepare("
      SELECT DISTINCT c.CourseID, c.CourseName 
      FROM Courses c
      JOIN GroupCourses gc ON c.CourseID = gc.CourseID
      WHERE gc.GroupID = ?
      ORDER BY c.CourseName
    ");
    if ($stmt) {
        $stmt->bind_param("i", $selectedGroup);
        $stmt->execute();
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("Error preparing statement to fetch courses for group: " . $conn->error);
    }
}

// Get GroupCourseID if both group and course are selected
$groupCourseId = null;
if (!empty($selectedGroup) && !empty($selectedCourse)) {
    $stmt = $conn->prepare("
        SELECT ID FROM GroupCourses 
        WHERE GroupID = ? AND CourseID = ?
    ");
    if ($stmt) {
        $stmt->bind_param("is", $selectedGroup, $selectedCourse);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $groupCourseId = $row['ID'];
        }
        $stmt->close();
    }
}
?>

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Dashboard /</span> Attendance & Grades
    </h4>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="get" id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="group_select" class="form-label fw-bold">Group</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="ri-team-line"></i>
                        </span>
                        <select name="group_id" id="group_select" class="form-select" aria-label="Select Group">
                            <option value="">– Select Group –</option>
                            <?php foreach($groups as $g): ?>
                            <option value="<?= $g['GroupID'] ?>" <?= $g['GroupID']==$selectedGroup?'selected':''?>><?= htmlspecialchars($g['GroupName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="course_select" class="form-label fw-bold">Course</label>
                    <div class="input-group">
                        <span class="input-group-text bg-success text-white">
                            <i class="ri-book-open-line"></i>
                        </span>
                        <select name="course_id" id="course_select" class="form-select" <?php if(empty($selectedGroup)) echo 'disabled' ?> aria-label="Select Course">
                            <option value="">– Select Course –</option>
                            <?php foreach($courses as $c): ?>
                            <option value="<?= $c['CourseID'] ?>" <?= $c['CourseID']==$selectedCourse?'selected':''?>><?= htmlspecialchars($c['CourseName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" id="applyFiltersBtn" class="btn btn-primary flex-grow-1">
                        <i class="ri-filter-3-line me-1"></i> Apply
                    </button>
                    <a href="attendance_grades.php" class="btn btn-outline-secondary">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons Row -->
    <div class="row g-4 mb-4">
        <!-- Grades Card -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Grades</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="card-text">Enter and manage grades for the selected group and course.</p>
                    <button type="button" id="submitGradesBtn" class="btn btn-primary btn-lg w-100 py-3 mt-3" 
                            onclick="openGradesModal(<?php echo !empty($groupCourseId) ? $groupCourseId : 'null' ?>)" 
                            <?php if(empty($groupCourseId)) echo 'disabled' ?>>
                        <i class="ri-edit-2-line fs-3 mb-2"></i>
                        <div class="fs-5">Submit Grades</div>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Attendance Card -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Attendance</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="card-text">Record and manage attendance for the selected group and course.</p>
                    <button type="button" id="submitAttendanceBtn" class="btn btn-success btn-lg w-100 py-3 mt-3" 
                            onclick="openAttendanceModal(<?php echo !empty($groupCourseId) ? $groupCourseId : 'null' ?>)"
                            <?php if(empty($groupCourseId)) echo 'disabled' ?>>
                        <i class="ri-calendar-check-line fs-3 mb-2"></i>
                        <div class="fs-5">Submit Attendance</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grades Modal -->
<div class="modal fade" id="gradesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Grades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="gradesLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading trainees...</p>
                </div>
                
                <form id="gradeEntryForm" method="post" style="display: none;">
                    <input type="hidden" name="group_course_id" id="grade_group_course_id" value="">
                    
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" id="gradesSearchInput" class="form-control" placeholder="Search trainees...">
                            </div>
                            <button type="button" id="resetGradesBtn" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line"></i> Reset Values
                            </button>
                        </div>
                        <div class="save-status text-end">
                            <span id="gradesSaveStatus" class="badge bg-secondary">Ready</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover grades-table" id="gradesTable">
                            <thead class="table-light">
                                <tr>
                                    <th data-sort="name">First Name <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="lastname">Last Name <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="govid">Gov ID <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="pretest">Pre-Test <span class="badge bg-secondary">Max: 50</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="attgrade" title="Calculated as Attendance Percentage / 10">Att.Grade <span class="badge bg-info">Auto</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="participation">Participation <span class="badge bg-secondary">Max: 10</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="quiz1">Q1 <span class="badge bg-secondary">Max: 30</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="quiz2">Q2 <span class="badge bg-secondary">Max: 30</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="quiz3">Q3 <span class="badge bg-secondary">Max: 30</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="quizavg">Q. Avg. <span class="badge bg-info">Auto</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="finaltest">Final Test <span class="badge bg-secondary">Max: 50</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="total">Total <span class="badge bg-info">Auto</span> <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                </tr>
                            </thead>
                            <tbody id="gradesTableBody">
                                <!-- Trainees will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="gradesNoTraineesMessage" class="alert alert-info" style="display: none;">
                        <h6 class="alert-heading fw-bold mb-1">No trainees found</h6>
                        <p class="mb-0">There are no trainees enrolled in this course. Please check the group assignments.</p>
                    </div>
                    
                    <div id="gradesErrorMessage" class="alert alert-danger" style="display: none;">
                        <h6 class="alert-heading fw-bold mb-1">Error</h6>
                        <p class="mb-0" id="gradesErrorText"></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveGradesBtn">Save Grades</button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="attendanceLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading trainees...</p>
                </div>
                
                <form id="attendanceEntryForm" method="post" style="display: none;">
                    <input type="hidden" name="group_course_id" id="attendance_group_course_id" value="">
                    
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" id="attendanceSearchInput" class="form-control" placeholder="Search trainees...">
                            </div>
                            <button type="button" id="resetAttendanceBtn" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line"></i> Reset Values
                            </button>
                        </div>
                        <div class="save-status text-end">
                            <span id="attendanceSaveStatus" class="badge bg-secondary">Ready</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover attendance-table" id="attendanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th data-sort="name">First Name <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="lastname">Last Name <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="govid">Gov ID <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="present" title="Present Hours">P <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="excused" title="Excused Hours">E <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="late" title="Late Hours">L <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="absent" title="Absent Hours">A <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="points" title="Moodle Points = 2*P + 1*E">Points <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="sessions" title="Total Sessions (Calculated)">Sessions <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                    <th data-sort="percentage" title="Attendance Percentage (Calculated)">Att % (Max: 100) <i class="ri-arrow-up-down-line sort-icon"></i></th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <!-- Trainees will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="attendanceNoTraineesMessage" class="alert alert-info" style="display: none;">
                        <h6 class="alert-heading fw-bold mb-1">No trainees found</h6>
                        <p class="mb-0">There are no trainees enrolled in this course. Please check the group assignments.</p>
                    </div>
                    
                    <div id="attendanceErrorMessage" class="alert alert-danger" style="display: none;">
                        <h6 class="alert-heading fw-bold mb-1">Error</h6>
                        <p class="mb-0" id="attendanceErrorText"></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceBtn">Save Attendance</button>
            </div>
        </div>
    </div>
</div>


<!-- Add necessary JS libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global modal variables
let gradesModal, attendanceModal;

// Function to open the grades modal
function openGradesModal(groupCourseId) {
    console.log('Opening grades modal with groupCourseId:', groupCourseId);
    
    if (!groupCourseId) {
        console.error('No group course ID available');
        return;
    }
    
    // Set the group course ID in the modal form
    $('#grade_group_course_id').val(groupCourseId);
    
    // Show loading indicator
    $('#gradesLoading').show();
    $('#gradeEntryForm').hide();
    $('#gradesNoTraineesMessage').hide();
    $('#gradesErrorMessage').hide();
    
    try {
        // Try multiple approaches to show the modal
        
        // Approach 1: Try direct bootstrap 5 constructor
        const modalElement = document.getElementById('gradesModal');
        if (typeof bootstrap !== 'undefined') {
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
            console.log('Modal shown using Bootstrap 5 constructor');
        } else {
            // Approach 2: Try jQuery method
            $('#gradesModal').modal('show');
            console.log('Modal shown using jQuery method');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Could not open the grades modal. Please try again or contact support.');
    }
    
    // Load trainees for the selected course
    loadTraineesForGrades(groupCourseId);
}

// Function to open the attendance modal
function openAttendanceModal(groupCourseId) {
    console.log('Opening attendance modal with groupCourseId:', groupCourseId);
    
    if (!groupCourseId) {
        console.error('No group course ID available');
        return;
    }
    
    // Set the group course ID in the modal form
    $('#attendance_group_course_id').val(groupCourseId);
    
    // Show loading indicator
    $('#attendanceLoading').show();
    $('#attendanceEntryForm').hide();
    $('#attendanceNoTraineesMessage').hide();
    $('#attendanceErrorMessage').hide();
    
    try {
        // Try multiple approaches to show the modal
        
        // Approach 1: Try direct bootstrap 5 constructor
        const modalElement = document.getElementById('attendanceModal');
        if (typeof bootstrap !== 'undefined') {
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
            console.log('Modal shown using Bootstrap 5 constructor');
        } else {
            // Approach 2: Try jQuery method
            $('#attendanceModal').modal('show');
            console.log('Modal shown using jQuery method');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Could not open the attendance modal. Please try again or contact support.');
    }
    
    // Load trainees for the selected course
    loadTraineesForAttendance(groupCourseId);
}

$(document).ready(function() {
    // Initialize Bootstrap modals after document is ready
    try {
        gradesModal = new bootstrap.Modal(document.getElementById('gradesModal'));
        attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
        console.log("Modals initialized successfully");
    } catch (error) {
        console.error("Error initializing modals:", error);
    }
    // Handle group selection change
    $('#group_select').on('change', function() {
        const groupId = $(this).val();
        const courseSelect = $('#course_select');
        
        if (groupId) {
            // Enable course select and fetch courses for this group
            courseSelect.prop('disabled', false);
            
            // Clear current options
            courseSelect.empty().append('<option value="">– Select Course –</option>');
            
            // Fetch courses via AJAX
            $.ajax({
                url: 'ajax_get_courses_by_group.php',
                data: { group_id: groupId },
                dataType: 'json',
                success: function(data) {
                    if (data.courses && data.courses.length > 0) {
                        data.courses.forEach(function(course) {
                            courseSelect.append($('<option></option>')
                                .val(course.CourseID)
                                .text(course.CourseName));
                        });
                    }
                },
                error: function() {
                    console.error('Failed to fetch courses');
                }
            });
        } else {
            // Disable and reset course select
            courseSelect.prop('disabled', true).empty().append('<option value="">– Select Course –</option>');
        }
    });
    
    // These event handlers are now defined at the top of the document.ready function
    
    // Handle Save Grades button click
    $('#saveGradesBtn').on('click', function() {
        $('#gradeEntryForm').submit();
    });
    
    // Handle Save Attendance button click
    $('#saveAttendanceBtn').on('click', function() {
        $('#attendanceEntryForm').submit();
    });
    
    // Handle input changes to calculate quiz average and total for grades
    $(document).on('input', '.grade-input', function() {
        const row = $(this).closest('tr');
        calculateQuizAvg(row);
        calculateTotal(row);
        
        // Update status to indicate changes
        $('#gradesSaveStatus').text('Unsaved changes').removeClass('bg-secondary').addClass('bg-warning');
        
        // Schedule auto-save after 30 seconds of inactivity
        scheduleAutoSave('grades');
    });
    
    // Handle input changes to calculate attendance percentage
    $(document).on('input', '.attendance-input', function() {
        const row = $(this).closest('tr');
        calculateAttendance(row);
        
        // Update status to indicate changes
        $('#attendanceSaveStatus').text('Unsaved changes').removeClass('bg-secondary').addClass('bg-warning');
        
        // Schedule auto-save after 30 seconds of inactivity
        scheduleAutoSave('attendance');
    });
    
    // Set up search functionality for grades table
    $('#gradesSearchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterTable('#gradesTableBody', searchTerm);
    });
    
    // Set up search functionality for attendance table
    $('#attendanceSearchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterTable('#attendanceTableBody', searchTerm);
    });
    
    // Set up sorting functionality for tables
    $('.grades-table th[data-sort], .attendance-table th[data-sort]').on('click', function() {
        const table = $(this).closest('table');
        const tbody = table.find('tbody');
        const sortField = $(this).data('sort');
        
        // Toggle sort direction
        const currentDir = $(this).hasClass('asc') ? 'desc' : 'asc';
        
        // Remove sort classes from all columns
        table.find('th').removeClass('asc desc');
        $(this).addClass(currentDir);
        
        // Sort the table
        sortTable(tbody, sortField, currentDir);
    });
    
    // Reset grades form to initial values
    $('#resetGradesBtn').on('click', function() {
        if (confirm('Are you sure you want to reset all values to their initial state? Unsaved changes will be lost.')) {
            const groupCourseId = $('#grade_group_course_id').val();
            loadTraineesForGrades(groupCourseId);
            $('#gradesSaveStatus').text('Reset complete').removeClass('bg-warning').addClass('bg-info');
            setTimeout(() => {
                $('#gradesSaveStatus').text('Ready').removeClass('bg-info').addClass('bg-secondary');
            }, 3000);
        }
    });
    
    // Reset attendance form to initial values
    $('#resetAttendanceBtn').on('click', function() {
        if (confirm('Are you sure you want to reset all values to their initial state? Unsaved changes will be lost.')) {
            const groupCourseId = $('#attendance_group_course_id').val();
            loadTraineesForAttendance(groupCourseId);
            $('#attendanceSaveStatus').text('Reset complete').removeClass('bg-warning').addClass('bg-info');
            setTimeout(() => {
                $('#attendanceSaveStatus').text('Ready').removeClass('bg-info').addClass('bg-secondary');
            }, 3000);
        }
    });
    
    // Enable paste functionality for grades table
    setupPasteHandling('#gradesTableBody');
    
    // Enable paste functionality for attendance table
    setupPasteHandling('#attendanceTableBody');
});

function loadTraineesForGrades(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_grades.php',
        data: { group_course_id: groupCourseId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#gradesErrorText').text(data.error);
                $('#gradesErrorMessage').show();
                $('#gradesLoading').hide();
                return;
            }
            
            if (data.length === 0) {
                $('#gradesNoTraineesMessage').show();
                $('#gradesLoading').hide();
                return;
            }
            
            const tableBody = $('#gradesTableBody');
            tableBody.empty();
            
            data.forEach(function(trainee) {
                const row = $('<tr></tr>');
                
                // First Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.FirstName + '" readonly>' +
                    '<input type="hidden" name="trainee_ids[]" value="' + trainee.TID + '">' +
                    '</td>'
                );
                
                // Last Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.LastName + '" readonly>' +
                    '</td>'
                );
                
                // Gov ID
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.GovID + '" readonly>' +
                    '</td>'
                );
                
                // Pre-Test
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input pretest" ' +
                    'name="pretest[]" min="0" max="100" step="0.1" ' +
                    'value="' + (trainee.PreTest !== null ? trainee.PreTest : '') + '">' +
                    '</td>'
                );
                
                // Attendance (read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance" ' +
                    'name="att_grade[]" min="0" max="10" step="0.1" ' +
                    'value="' + (trainee.AttGrade !== null ? trainee.AttGrade : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Participation
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input participation" ' +
                    'name="participation[]" min="0" max="10" step="0.1" ' +
                    'value="' + (trainee.Participation !== null ? trainee.Participation : 0) + '">' +
                    '</td>'
                );
                
                // Quiz 1
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz1" ' +
                    'name="quiz1[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz1 !== null ? trainee.Quiz1 : 0) + '">' +
                    '</td>'
                );
                
                // Quiz 2
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz2" ' +
                    'name="quiz2[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz2 !== null ? trainee.Quiz2 : '') + '">' +
                    '</td>'
                );
                
                // Quiz 3
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input quiz3" ' +
                    'name="quiz3[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.Quiz3 !== null ? trainee.Quiz3 : '') + '">' +
                    '</td>'
                );
                
                // Quiz Average (calculated)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm quiz-avg" ' +
                    'name="quiz_avg[]" min="0" max="30" step="0.1" ' +
                    'value="' + (trainee.QuizAvg !== null ? trainee.QuizAvg : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Final Exam
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm grade-input final-exam" ' +
                    'name="final_exam[]" min="0" max="50" step="0.1" ' +
                    'value="' + (trainee.FinalExam !== null ? trainee.FinalExam : 0) + '">' +
                    '</td>'
                );
                
                // Total (calculated)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm course-total" ' +
                    'name="course_total[]" min="0" max="100" step="0.1" ' +
                    'value="' + (trainee.CourseTotal !== null ? trainee.CourseTotal : 0) + '" readonly>' +
                    '</td>'
                );
                
                tableBody.append(row);
                
                // Calculate initial values
                calculateQuizAvg(row);
                calculateTotal(row);
            });
            
            $('#gradesLoading').hide();
            $('#gradeEntryForm').show();
        },
        error: function(xhr, status, error) {
            $('#gradesErrorText').text('Failed to load trainees: ' + error);
            $('#gradesErrorMessage').show();
            $('#gradesLoading').hide();
        }
    });
}

function loadTraineesForAttendance(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_attendance.php',
        data: { group_course_id: groupCourseId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#attendanceErrorText').text(data.error);
                $('#attendanceErrorMessage').show();
                $('#attendanceLoading').hide();
                return;
            }
            
            if (data.length === 0) {
                $('#attendanceNoTraineesMessage').show();
                $('#attendanceLoading').hide();
                return;
            }
            
            const tableBody = $('#attendanceTableBody');
            tableBody.empty();
            
            data.forEach(function(trainee) {
                const row = $('<tr></tr>');
                
                // First Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.FirstName + '" readonly>' +
                    '<input type="hidden" name="trainee_ids[]" value="' + trainee.TID + '">' +
                    '</td>'
                );
                
                // Last Name
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.LastName + '" readonly>' +
                    '</td>'
                );
                
                // Gov ID
                row.append(
                    '<td>' +
                    '<input type="text" class="form-control form-control-sm" value="' + trainee.GovID + '" readonly>' +
                    '</td>'
                );
                
                // Present Hours (P)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input present-hours" ' +
                    'name="present_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.PresentHours !== null ? trainee.PresentHours : 0) + '">' +
                    '</td>'
                );
                
                // Excused Hours (E)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input excused-hours" ' +
                    'name="excused_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.ExcusedHours !== null ? trainee.ExcusedHours : 0) + '">' +
                    '</td>'
                );
                
                // Late Hours (L)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input late-hours" ' +
                    'name="late_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.LateHours !== null ? trainee.LateHours : 0) + '">' +
                    '</td>'
                );
                
                // Absent Hours (A)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-input absent-hours" ' +
                    'name="absent_hours[]" min="0" step="0.5" ' +
                    'value="' + (trainee.AbsentHours !== null ? trainee.AbsentHours : 0) + '">' +
                    '</td>'
                );
                
                // Points (calculated, read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm points" ' +
                    'name="points[]" min="0" step="1" ' +
                    'value="' + (trainee.MoodlePoints !== null ? trainee.MoodlePoints : 0) + '" readonly>' +
                    '</td>'
                );
                
                // Total Sessions (calculated, read-only)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm taken-sessions" ' +
                    'name="taken_sessions[]" min="1" step="1" ' +
                    'value="' + (trainee.TakenSessions !== null ? trainee.TakenSessions : 1) + '" readonly>' +
                    '</td>'
                );
                
                // Attendance Percentage (calculated)
                row.append(
                    '<td>' +
                    '<input type="number" class="form-control form-control-sm attendance-percentage" ' +
                    'name="attendance_percentage[]" min="0" max="100" step="0.1" ' +
                    'value="' + (trainee.AttendancePercentage !== null ? trainee.AttendancePercentage : 0) + '" readonly>' +
                    '</td>'
                );
                
                tableBody.append(row);
                
                // Calculate initial attendance percentage
                calculateAttendance(row);
            });
            
            $('#attendanceLoading').hide();
            $('#attendanceEntryForm').show();
        },
        error: function(xhr, status, error) {
            $('#attendanceErrorText').text('Failed to load trainees: ' + error);
            $('#attendanceErrorMessage').show();
            $('#attendanceLoading').hide();
        }
    });
}

function calculateQuizAvg(row) {
    const quiz1 = parseFloat(row.find('.quiz1').val()) || 0;
    const quiz2 = row.find('.quiz2').val() ? parseFloat(row.find('.quiz2').val()) : null;
    const quiz3 = row.find('.quiz3').val() ? parseFloat(row.find('.quiz3').val()) : null;
    
    let quizCount = 1; // Quiz 1 is mandatory
    let quizSum = quiz1;
    
    if (quiz2 !== null) {
        quizCount++;
        quizSum += quiz2;
    }
    
    if (quiz3 !== null) {
        quizCount++;
        quizSum += quiz3;
    }
    
    const quizAvg = quizSum / quizCount;
    row.find('.quiz-avg').val(quizAvg.toFixed(1));
}

function calculateTotal(row) {
    const attendance = parseFloat(row.find('.attendance').val()) || 0;
    const participation = parseFloat(row.find('.participation').val()) || 0;
    const quizAvg = parseFloat(row.find('.quiz-avg').val()) || 0;
    const finalExam = parseFloat(row.find('.final-exam').val()) || 0;
    
    const total = attendance + participation + quizAvg + finalExam;
    row.find('.course-total').val(total.toFixed(1));
}

function calculateAttendance(row) {
    const presentHours = parseFloat(row.find('.present-hours').val()) || 0;
    const lateHours = parseFloat(row.find('.late-hours').val()) || 0;
    const excusedHours = parseFloat(row.find('.excused-hours').val()) || 0;
    const absentHours = parseFloat(row.find('.absent-hours').val()) || 0;
    
    const totalHours = presentHours + lateHours + excusedHours + absentHours;
    
    if (totalHours > 0) {
        // Calculate attendance percentage: (present + late + excused) / total * 100
        const attendancePercentage = ((presentHours + (lateHours * 0.5) + excusedHours) / totalHours) * 100;
        row.find('.attendance-percentage').val(attendancePercentage.toFixed(1));
    } else {
        row.find('.attendance-percentage').val('0.0');
    }
}

// Variables for auto-save
let gradesSaveTimeout;
let attendanceSaveTimeout;

function scheduleAutoSave(formType) {
    // Clear any existing timeout
    if (formType === 'grades') {
        clearTimeout(gradesSaveTimeout);
        // Schedule new auto-save after 30 seconds
        gradesSaveTimeout = setTimeout(() => {
            $('#gradesSaveStatus').text('Auto-saving...').removeClass('bg-warning').addClass('bg-primary');
            $('#gradeEntryForm').submit();
        }, 30000); // 30 seconds
    } else {
        clearTimeout(attendanceSaveTimeout);
        attendanceSaveTimeout = setTimeout(() => {
            $('#attendanceSaveStatus').text('Auto-saving...').removeClass('bg-warning').addClass('bg-primary');
            $('#attendanceEntryForm').submit();
        }, 30000); // 30 seconds
    }
}

function filterTable(tableBodySelector, searchTerm) {
    $(tableBodySelector + ' tr').each(function() {
        const row = $(this);
        const firstNameCell = row.find('td:nth-child(1) input').val().toLowerCase();
        const lastNameCell = row.find('td:nth-child(2) input').val().toLowerCase();
        const govIdCell = row.find('td:nth-child(3) input').val().toLowerCase();
        
        // If any cell contains the search term, show the row, otherwise hide it
        if (firstNameCell.includes(searchTerm) || lastNameCell.includes(searchTerm) || govIdCell.includes(searchTerm)) {
            row.show();
        } else {
            row.hide();
        }
    });
}

function sortTable(tbody, field, direction) {
    const rows = tbody.find('tr').toArray();
    
    rows.sort((a, b) => {
        let valA, valB;
        
        switch(field) {
            case 'name':
                valA = $(a).find('td:nth-child(1) input').val();
                valB = $(b).find('td:nth-child(1) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            case 'lastname':
                valA = $(a).find('td:nth-child(2) input').val();
                valB = $(b).find('td:nth-child(2) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            case 'govid':
                valA = $(a).find('td:nth-child(3) input').val();
                valB = $(b).find('td:nth-child(3) input').val();
                return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            default:
                // For numeric fields (find the correct column index based on field)
                let colIndex;
                switch(field) {
                    case 'pretest': colIndex = 4; break;
                    case 'attgrade': colIndex = 5; break;
                    case 'participation': colIndex = 6; break;
                    case 'quiz1': colIndex = 7; break;
                    case 'quiz2': colIndex = 8; break;
                    case 'quiz3': colIndex = 9; break;
                    case 'quizavg': colIndex = 10; break;
                    case 'finaltest': colIndex = 11; break;
                    case 'total': colIndex = 12; break;
                    case 'present': colIndex = 4; break;
                    case 'excused': colIndex = 5; break;
                    case 'late': colIndex = 6; break;
                    case 'absent': colIndex = 7; break;
                    case 'points': colIndex = 8; break;
                    case 'sessions': colIndex = 9; break;
                    case 'percentage': colIndex = 10; break;
                    default: colIndex = 0;
                }
                
                valA = parseFloat($(a).find('td:nth-child(' + colIndex + ') input').val()) || 0;
                valB = parseFloat($(b).find('td:nth-child(' + colIndex + ') input').val()) || 0;
                return direction === 'asc' ? valA - valB : valB - valA;
        }
    });
    
    // Re-append sorted rows to the tbody
    $.each(rows, function(index, row) {
        tbody.append(row);
    });
}

function setupPasteHandling(tableBodySelector) {
    // Handle paste events for the table
    $(document).on('paste', tableBodySelector + ' input', function(e) {
        e.preventDefault();
        
        // Get pasted data
        let pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        
        // Process the pasted data
        const rows = pastedData.split(/\r\n|\n|\r/);
        if (rows.length <= 1) return; // Not a multi-line paste
        
        const currentCell = $(this);
        const currentRow = currentCell.closest('tr');
        const currentTable = currentRow.closest('tbody');
        const currentCellIndex = currentRow.find('input:visible').index(currentCell);
        
        // Process each row of pasted data
        rows.forEach((rowData, rowIndex) => {
            if (!rowData.trim()) return; // Skip empty rows
            
            const targetRow = currentTable.find('tr').eq(currentRow.index() + rowIndex);
            if (targetRow.length === 0) return; // Skip if row doesn't exist
            
            const cells = rowData.split(/\t/);
            
            // Process each cell in the row
            cells.forEach((cellData, cellIndex) => {
                const targetCellIndex = currentCellIndex + cellIndex;
                const targetInput = targetRow.find('input:visible').eq(targetCellIndex);
                
                if (targetInput.length && !targetInput.prop('readonly')) {
                    targetInput.val(cellData.trim());
                    targetInput.trigger('input'); // Trigger calculations
                }
            });
        });
    });
}
</script>

<?php include_once "../includes/footer.php"; ?>
