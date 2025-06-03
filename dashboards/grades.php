<?php
$pageTitle = "Enter Grades"; // Set page title
include_once("../includes/config.php");
include_once("../includes/auth.php");

// Protect this page - only users with record_grades permission can access
if (!isLoggedIn() || !hasPermission('record_grades')) {
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
        <span class="text-muted fw-light">Instructor /</span> Enter Grades
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
            <form id="courseSelectionForm" method="get" action="grades.php">
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
    <!-- Grade Entry Form -->
    <div class="card mb-4">
        <h5 class="card-header">Enter Grades</h5>
        <div class="card-body">
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading trainees...</p>
            </div>
            
            <form id="gradeEntryForm" method="post" action="submit_grades.php" style="display: none;">
                <input type="hidden" name="group_course_id" value="<?php echo $groupCourseId; ?>">
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Trainee</th>
                                <th>Pre-Test</th>
                                <th>Attendance</th>
                                <th>Participation</th>
                                <th>Quiz 1</th>
                                <th>Quiz 2</th>
                                <th>Quiz 3</th>
                                <th>Quiz Avg</th>
                                <th>Final Exam</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="traineesTableBody">
                            <!-- Trainees will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <a href="grades.php" class="btn btn-outline-secondary me-2">Back to Course Selection</a>
                        <button type="submit" class="btn btn-primary">Save Grades</button>
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
    
    // Handle input changes to calculate quiz average and total
    $(document).on('input', '.grade-input', function() {
        const row = $(this).closest('tr');
        calculateQuizAvg(row);
        calculateTotal(row);
    });
});

function loadTrainees(groupCourseId) {
    $.ajax({
        url: 'get_trainees_for_grades.php',
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
                
                // Pre-Test
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input pretest" 
                               name="pretest[]" min="0" max="100" step="0.1" 
                               value="${trainee.PreTest !== null ? trainee.PreTest : ''}">
                    </td>
                `);
                
                // Attendance (read-only)
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm attendance" 
                               name="att_grade[]" min="0" max="10" step="0.1" 
                               value="${trainee.AttGrade !== null ? trainee.AttGrade : 0}" readonly>
                    </td>
                `);
                
                // Participation
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input participation" 
                               name="participation[]" min="0" max="10" step="0.1" 
                               value="${trainee.Participation !== null ? trainee.Participation : 0}" required>
                    </td>
                `);
                
                // Quiz 1
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input quiz1" 
                               name="quiz1[]" min="0" max="30" step="0.1" 
                               value="${trainee.Quiz1 !== null ? trainee.Quiz1 : 0}" required>
                    </td>
                `);
                
                // Quiz 2
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input quiz2" 
                               name="quiz2[]" min="0" max="30" step="0.1" 
                               value="${trainee.Quiz2 !== null ? trainee.Quiz2 : ''}">
                    </td>
                `);
                
                // Quiz 3
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input quiz3" 
                               name="quiz3[]" min="0" max="30" step="0.1" 
                               value="${trainee.Quiz3 !== null ? trainee.Quiz3 : ''}">
                    </td>
                `);
                
                // Quiz Average (calculated)
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm quiz-avg" 
                               name="quiz_avg[]" min="0" max="30" step="0.1" 
                               value="${trainee.QuizAvg !== null ? trainee.QuizAvg : 0}" readonly>
                    </td>
                `);
                
                // Final Exam
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm grade-input final-exam" 
                               name="final_exam[]" min="0" max="50" step="0.1" 
                               value="${trainee.FinalExam !== null ? trainee.FinalExam : 0}" required>
                    </td>
                `);
                
                // Total (calculated)
                row.append(`
                    <td>
                        <input type="number" class="form-control form-control-sm course-total" 
                               name="course_total[]" min="0" max="100" step="0.1" 
                               value="${trainee.CourseTotal !== null ? trainee.CourseTotal : 0}" readonly>
                    </td>
                `);
                
                tableBody.append(row);
                
                // Calculate initial values
                calculateQuizAvg(row);
                calculateTotal(row);
            });
            
            $('#loading').hide();
            $('#gradeEntryForm').show();
        },
        error: function(xhr, status, error) {
            $('#errorText').text('Failed to load trainees: ' + error);
            $('#errorMessage').show();
            $('#loading').hide();
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
</script>

<?php
// Include the footer
include_once("../includes/footer.php");
?>
