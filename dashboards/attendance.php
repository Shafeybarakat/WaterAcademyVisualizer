<?php
$pageTitle = "Enter Attendance"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php");

// Protect this page - only users with record_attendance permission can access
if (!isLoggedIn() || !hasPermission('record_attendance')) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

// Include the header - this also includes the sidebar
include_once("../includes/header.php");

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
?>

<!-- Include Google Fonts for Canva-like styling -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Content specific to this page -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Instructor /</span> Enter Attendance
    </h4>

    <?php if (empty($groups)): ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <h6 class="alert-heading fw-bold mb-1">No courses assigned</h6>
                <p class="mb-0">You currently don't have any courses assigned to you. Please contact an administrator if you believe this is an error.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Course Selection Form -->
    <?php if ($groupCourseId <= 0): ?>
    <div class="card mb-4">
        <h5 class="card-header">Select Course</h5>
        <div class="card-body">
            <form id="courseSelectionForm" method="get" action="attendance.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="group_select" class="form-label">Group</label>
                        <select id="group_select" name="group_id" class="form-select" required>
                            <option value="">Select a group</option>
                            <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['GroupID']; ?>"><?php echo htmlspecialchars($group['GroupName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="group_course_id" class="form-label">Course</label>
                        <select id="group_course_id" name="group_course_id" class="form-select" required disabled>
                            <option value="">Select a course</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- Attendance Entry Form -->
    <div class="card mb-4">
        <h5 class="card-header">Enter Attendance</h5>
        <div class="card-body">
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading trainees...</p>
            </div>
            
            <form id="attendanceEntryForm" method="post" action="submit_attendance.php" style="display: none;">
                <input type="hidden" name="group_course_id" value="<?php echo $groupCourseId; ?>">
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Trainee</th>
                                <th>Present Hours</th>
                                <th>Late Hours</th>
                                <th>Excused Hours</th>
                                <th>Absent Hours</th>
                                <th>Total Sessions</th>
                                <th>Points (earned/total)</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody id="traineesTableBody">
                            <!-- Trainees will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <a href="attendance.php" class="btn btn-outline-secondary me-2">Back to Course Selection</a>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                </div>
            </form>
            
            <div id="noTraineesMessage" class="alert alert-info" style="display: none;">
                <h6 class="alert-heading fw-bold mb-1">No trainees found</h6>
                <p class="mb-0">There are no trainees enrolled in this course. Please check the group assignments.</p>
            </div>
            
            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                <h6 class="alert-heading fw-bold mb-1">Error</h6>
                <p class="mb-0" id="errorText"></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>
<!-- / Content -->

<style>
.card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.card-header {
    border-bottom: none;
    background-color: transparent;
    padding-bottom: 0;
}

.form-control, .form-select {
    border-radius: 8px;
    padding: 0.5rem 1rem;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
}

.table th {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

input[type="number"] {
    min-width: 80px;
}
</style>

<script>
$(document).ready(function() {
    console.log("Document ready");
    
    // Trigger change event on page load if a value is already selected
    if ($('#group_select').val()) {
        $('#group_select').trigger('change');
    }
    
    // Handle group selection change
    $('#group_select').on('change', function() {
        console.log("Group selection changed");
        const groupId = $(this).val();
        console.log("Selected group ID:", groupId);
        const courseSelect = $('#group_course_id');
        
        if (groupId) {
            console.log("Enabling course select");
            // Enable course select
            courseSelect.prop('disabled', false);
            
            // Clear current options
            courseSelect.empty().append('<option value="">Select a course</option>');
            
            let coursesAdded = 0;
            // Add courses for the selected group
            <?php foreach ($groups as $group): ?>
            console.log("Checking group: <?php echo $group['GroupID']; ?>");
            if (groupId === '<?php echo $group['GroupID']; ?>') {
                console.log("Found matching group: <?php echo $group['GroupName']; ?>");
                <?php foreach ($group['Courses'] as $course): ?>
                console.log("Adding course: <?php echo addslashes($course['CourseName']); ?>");
                courseSelect.append($('<option></option>')
                    .val('<?php echo $course['GroupCourseID']; ?>')
                    .text('<?php echo addslashes($course['CourseName']); ?>'));
                coursesAdded++;
                <?php endforeach; ?>
            }
            <?php endforeach; ?>
            console.log("Total courses added:", coursesAdded);
        } else {
            console.log("Disabling course select");
            // Disable and reset course select
            courseSelect.prop('disabled', true).empty().append('<option value="">Select a course</option>');
        }
    });
    
    <?php if ($groupCourseId > 0): ?>
    // Load trainees for the selected course
    loadTrainees(<?php echo $groupCourseId; ?>);
    <?php endif; ?>
    
    // Handle input changes to calculate attendance percentage
    $(document).on('input', '.attendance-input', function() {
        const row = $(this).closest('tr');
        calculateAttendance(row);
    });
});

function loadTrainees(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_attendance.php',
        data: { group_course_id: groupCourseId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#errorText').text(data.error);
                $('#errorMessage').show();
                $('#loading').hide();
                return;
            }
            
            if (data.length === 0) {
                $('#noTraineesMessage').show();
                $('#loading').hide();
                return;
            }
            
            const tableBody = $('#traineesTableBody');
            tableBody.empty();
            
            data.forEach(function(trainee) {
                const row = $('<tr></tr>');
                
                // Trainee name and hidden ID
                row.append(`
                    <td>
                        <strong>${trainee.FirstName} ${trainee.LastName}</strong>
                        <br><small class="text-muted">ID: ${trainee.GovID}</small>
                        <input type="hidden" name="trainee_ids[]" value="${trainee.TID}">
                    </td>
                `);
                
                // Present Hours
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance-input present-hours" 
                               name="present_hours[]" min="0" step="0.5" 
                               value="${trainee.PresentHours !== null ? trainee.PresentHours : 0}" required>
                    </td>
                `);
                
                // Late Hours
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance-input late-hours" 
                               name="late_hours[]" min="0" step="0.5" 
                               value="${trainee.LateHours !== null ? trainee.LateHours : 0}" required>
                    </td>
                `);
                
                // Excused Hours
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance-input excused-hours" 
                               name="excused_hours[]" min="0" step="0.5" 
                               value="${trainee.ExcusedHours !== null ? trainee.ExcusedHours : 0}" required>
                    </td>
                `);
                
                // Absent Hours
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance-input absent-hours" 
                               name="absent_hours[]" min="0" step="0.5" 
                               value="${trainee.AbsentHours !== null ? trainee.AbsentHours : 0}" required>
                    </td>
                `);
                
                // Total Sessions
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm taken-sessions" 
                               name="taken_sessions[]" min="1" step="1" 
                               value="${trainee.TakenSessions !== null ? trainee.TakenSessions : 1}" required>
                    </td>
                `);
                
                // Points (earned/total)
                row.append(`
                    <td>
                        <input type="text" class="form-control form-control-sm points" 
                               name="points[]" placeholder="0/10" 
                               value="${trainee.MoodlePoints !== null ? trainee.MoodlePoints + ' / 10' : '0 / 10'}" required>
                    </td>
                `);
                
                // Attendance Percentage (calculated)
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance-percentage" 
                               name="attendance_percentage[]" min="0" max="100" step="0.1" 
                               value="${trainee.AttendancePercentage !== null ? trainee.AttendancePercentage : 0}" readonly>
                    </td>
                `);
                
                tableBody.append(row);
                
                // Calculate initial attendance percentage
                calculateAttendance(row);
            });
            
            $('#loading').hide();
            $('#attendanceEntryForm').show();
        },
        error: function(xhr, status, error) {
            $('#errorText').text('Failed to load trainees: ' + error);
            $('#errorMessage').show();
            $('#loading').hide();
        }
    });
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
</script>

<?php
// Include the footer
include_once("../includes/footer.php");
?>
