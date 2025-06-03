<?php
$pageTitle = "Instructor Dashboard";
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Protect this page - only users with view_courses permission can access
if (!isLoggedIn() || !hasPermission('view_courses')) {
    header("Location: ../login.php?message=access_denied");
    exit;
}

require_once "../includes/header.php";

// Get instructor ID
$instructorId = $_SESSION['user_id'];

// Get groups and courses assigned to this instructor
$assignedGroups = [];
$assignedCourses = [];

// Query for groups assigned to this instructor
$groupsQuery = "
    SELECT DISTINCT g.GroupID, g.GroupName, g.Program, 
           COUNT(DISTINCT t.TID) as TraineeCount
    FROM GroupCourses gc
    JOIN Groups g ON gc.GroupID = g.GroupID
    LEFT JOIN Trainees t ON t.GroupID = g.GroupID
    WHERE gc.InstructorID = ?
    GROUP BY g.GroupID, g.GroupName, g.Program
    ORDER BY g.GroupName
";

$stmt = $conn->prepare($groupsQuery);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $assignedGroups[] = $row;
}

// Query for courses assigned to this instructor
$coursesQuery = "
    SELECT gc.ID as GroupCourseID, c.CourseID, c.CourseName, g.GroupID, g.GroupName,
           gc.StartDate, gc.EndDate, 
           (CASE WHEN gc.EndDate < CURDATE() THEN 'Completed' 
                 WHEN gc.StartDate > CURDATE() THEN 'Upcoming'
                 ELSE 'In Progress' END) as CourseStatus,
           (SELECT COUNT(*) FROM Attendance a WHERE a.GroupCourseID = gc.ID) as HasAttendance,
           (SELECT COUNT(*) FROM TraineeGrades tg WHERE tg.GroupCourseID = gc.ID) as HasGrades
    FROM GroupCourses gc
    JOIN Courses c ON gc.CourseID = c.CourseID
    JOIN Groups g ON gc.GroupID = g.GroupID
    WHERE gc.InstructorID = ?
    ORDER BY gc.EndDate DESC
";

$stmt = $conn->prepare($coursesQuery);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $assignedCourses[] = $row;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Instructor Dashboard</h4>
    
    <!-- Groups and Trainees Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">My Groups</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($assignedGroups) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($assignedGroups)): ?>
                        <p class="text-muted">You are not assigned to any groups yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($assignedGroups as $group): ?>
                                <a href="group_details.php?id=<?= $group['GroupID'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($group['GroupName']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($group['Program']) ?></small>
                                    </div>
                                    <span class="badge bg-info rounded-pill"><?= $group['TraineeCount'] ?> trainees</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">My Courses</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($assignedCourses) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($assignedCourses)): ?>
                        <p class="text-muted">You are not assigned to any courses yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['CourseName']) ?></td>
                                            <td><?= htmlspecialchars($course['GroupName']) ?></td>
                                            <td>
                                                <span class="badge bg-label-<?= 
                                                    $course['CourseStatus'] == 'Completed' ? 'success' : 
                                                    ($course['CourseStatus'] == 'Upcoming' ? 'info' : 'warning') 
                                                ?>">
                                                    <?= $course['CourseStatus'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($course['CourseStatus'] == 'Completed' || $course['CourseStatus'] == 'In Progress'): ?>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="javascript:void(0);" 
                                                               onclick="openAttendanceModal(<?= $course['GroupCourseID'] ?>, '<?= htmlspecialchars($course['CourseName']) ?>', '<?= htmlspecialchars($course['GroupName']) ?>')">
                                                                <i class="bx bx-calendar-check me-1"></i> Enter Attendance
                                                            </a>
                                                            <a class="dropdown-item" href="javascript:void(0);" 
                                                               onclick="openGradesModal(<?= $course['GroupCourseID'] ?>, '<?= htmlspecialchars($course['CourseName']) ?>', '<?= htmlspecialchars($course['GroupName']) ?>')">
                                                                <i class="bx bx-edit me-1"></i> Enter Grades
                                                            </a>
                                                            <?php if ($course['HasGrades'] > 0): ?>
                                                                <a class="dropdown-item" href="trainee_report.php?course_id=<?= $course['GroupCourseID'] ?>">
                                                                    <i class="bx bx-bar-chart-alt-2 me-1"></i> View Reports
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#attendanceEntryModal">
                                <i class="bx bx-calendar-check me-1"></i> Enter Attendance
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#gradeEntryModal">
                                <i class="bx bx-edit me-1"></i> Enter Grades
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="report_trainee_performance.php" class="btn btn-primary w-100">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Entry Modal -->
<div class="modal fade" id="attendanceEntryModal" tabindex="-1" aria-labelledby="attendanceEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceEntryModalLabel">Attendance Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm" method="post" action="submit_attendance.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="attendance_group" class="form-label">Group</label>
                            <select class="form-select" id="attendance_group" name="group_id" required>
                                <option value="">Select Group</option>
                                <?php foreach ($assignedGroups as $group): ?>
                                    <option value="<?= $group['GroupID'] ?>"><?= htmlspecialchars($group['GroupName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="attendance_course" class="form-label">Course</label>
                            <select class="form-select" id="attendance_course" name="group_course_id" required disabled>
                                <option value="">Select Group First</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="attendanceTableContainer" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="attendanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Gov. ID</th>
                                        <th>P</th>
                                        <th>E</th>
                                        <th>L</th>
                                        <th>A</th>
                                        <th>Taken sessions</th>
                                        <th>Points</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceBtn" disabled>Save Attendance</button>
            </div>
        </div>
    </div>
</div>

<!-- Grade Entry Modal -->
<div class="modal fade" id="gradeEntryModal" tabindex="-1" aria-labelledby="gradeEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeEntryModalLabel">Grade Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="gradeForm" method="post" action="submit_grades.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="grade_group" class="form-label">Group</label>
                            <select class="form-select" id="grade_group" name="group_id" required>
                                <option value="">Select Group</option>
                                <?php foreach ($assignedGroups as $group): ?>
                                    <option value="<?= $group['GroupID'] ?>"><?= htmlspecialchars($group['GroupName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="grade_course" class="form-label">Course</label>
                            <select class="form-select" id="grade_course" name="group_course_id" required disabled>
                                <option value="">Select Group First</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="gradeTableContainer" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="gradeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Gov. ID</th>
                                        <th>Pre-Test<br><small>50 MAX</small></th>
                                        <th>Att. Grade<br><small>10 MAX</small></th>
                                        <th>Participation<br><small>10 MAX</small></th>
                                        <th>Course Quiz 1<br><small>30 MAX</small></th>
                                        <th>Course Quiz 2<br><small>30 MAX</small></th>
                                        <th>Course Quiz 3<br><small>30 MAX</small></th>
                                        <th>Quiz Avg<br><small>30 MAX</small></th>
                                        <th>Final Exam<br><small>50 MAX</small></th>
                                        <th>Course Total<br><small>100</small></th>
                                    </tr>
                                </thead>
                                <tbody id="gradeTableBody">
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveGradesBtn" disabled>Save Grades</button>
            </div>
        </div>
    </div>
</div>

<script>
// Attendance form handling
document.addEventListener('DOMContentLoaded', function() {
    const attendanceGroup = document.getElementById('attendance_group');
    const attendanceCourse = document.getElementById('attendance_course');
    const attendanceTableContainer = document.getElementById('attendanceTableContainer');
    const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
    
    // When group is selected, load courses
    attendanceGroup.addEventListener('change', function() {
        const groupId = this.value;
        if (groupId) {
            // Enable course dropdown
            attendanceCourse.disabled = false;
            
            // Fetch courses for this group
            fetch(`get_instructor_courses.php?group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear previous options
                    attendanceCourse.innerHTML = '<option value="">Select Course</option>';
                    
                    // Add new options
                    data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.GroupCourseID;
                        option.textContent = course.CourseName;
                        attendanceCourse.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching courses:', error));
        } else {
            // Disable course dropdown if no group selected
            attendanceCourse.disabled = true;
            attendanceCourse.innerHTML = '<option value="">Select Group First</option>';
            attendanceTableContainer.classList.add('d-none');
            saveAttendanceBtn.disabled = true;
        }
    });
    
    // When course is selected, load trainees
    attendanceCourse.addEventListener('change', function() {
        const groupCourseId = this.value;
        if (groupCourseId) {
            // Fetch trainees for this course
            fetch(`get_trainees_for_attendance.php?group_course_id=${groupCourseId}`)
                .then(response => response.json())
                .then(data => {
                    // Show table container
                    attendanceTableContainer.classList.remove('d-none');
                    
                    // Clear previous rows
                    const tableBody = document.getElementById('attendanceTableBody');
                    tableBody.innerHTML = '';
                    
                    // Add trainees to table
                    data.forEach(trainee => {
                        const row = document.createElement('tr');
                        
                        // Add hidden input for trainee ID
                        const tidInput = `<input type="hidden" name="trainee_ids[]" value="${trainee.TID}">`;
                        
                        // Create cells
                        row.innerHTML = `
                            <td>${tidInput}${trainee.FirstName}</td>
                            <td>${trainee.LastName}</td>
                            <td>${trainee.GovID}</td>
                            <td><input type="number" class="form-control p-hours" name="present_hours[]" min="0" required></td>
                            <td><input type="number" class="form-control e-hours" name="excused_hours[]" min="0" required></td>
                            <td><input type="number" class="form-control l-hours" name="late_hours[]" min="0" required></td>
                            <td><input type="number" class="form-control a-hours" name="absent_hours[]" min="0" required></td>
                            <td><input type="number" class="form-control sessions" name="taken_sessions[]" min="0" required></td>
                            <td><input type="text" class="form-control points" name="points[]" required></td>
                            <td><input type="number" class="form-control percentage" name="attendance_percentage[]" min="0" max="100" step="0.1" required></td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                    
                    // Enable save button
                    saveAttendanceBtn.disabled = false;
                    
                    // Add event listeners for auto-calculation
                    setupAttendanceCalculations();
                })
                .catch(error => console.error('Error fetching trainees:', error));
        } else {
            // Hide table if no course selected
            attendanceTableContainer.classList.add('d-none');
            saveAttendanceBtn.disabled = true;
        }
    });
    
    // Save attendance button
    saveAttendanceBtn.addEventListener('click', function() {
        const form = document.getElementById('attendanceForm');
        if (form.checkValidity()) {
            form.submit();
        } else {
            // Trigger browser's native validation
            form.reportValidity();
        }
    });
    
    // Grade form handling
    const gradeGroup = document.getElementById('grade_group');
    const gradeCourse = document.getElementById('grade_course');
    const gradeTableContainer = document.getElementById('gradeTableContainer');
    const saveGradesBtn = document.getElementById('saveGradesBtn');
    
    // When group is selected, load courses
    gradeGroup.addEventListener('change', function() {
        const groupId = this.value;
        if (groupId) {
            // Enable course dropdown
            gradeCourse.disabled = false;
            
            // Fetch courses for this group
            fetch(`get_instructor_courses.php?group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear previous options
                    gradeCourse.innerHTML = '<option value="">Select Course</option>';
                    
                    // Add new options
                    data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.GroupCourseID;
                        option.textContent = course.CourseName;
                        gradeCourse.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching courses:', error));
        } else {
            // Disable course dropdown if no group selected
            gradeCourse.disabled = true;
            gradeCourse.innerHTML = '<option value="">Select Group First</option>';
            gradeTableContainer.classList.add('d-none');
            saveGradesBtn.disabled = true;
        }
    });
    
    // When course is selected, load trainees
    gradeCourse.addEventListener('change', function() {
        const groupCourseId = this.value;
        if (groupCourseId) {
            // Fetch trainees for this course
            fetch(`get_trainees_for_grades.php?group_course_id=${groupCourseId}`)
                .then(response => response.json())
                .then(data => {
                    // Show table container
                    gradeTableContainer.classList.remove('d-none');
                    
                    // Clear previous rows
                    const tableBody = document.getElementById('gradeTableBody');
                    tableBody.innerHTML = '';
                    
                    // Add trainees to table
                    data.forEach(trainee => {
                        const row = document.createElement('tr');
                        
                        // Add hidden input for trainee ID
                        const tidInput = `<input type="hidden" name="trainee_ids[]" value="${trainee.TID}">`;
                        
                        // Create cells with existing data if available
                        row.innerHTML = `
                            <td>${tidInput}${trainee.FirstName}</td>
                            <td>${trainee.LastName}</td>
                            <td>${trainee.GovID}</td>
                            <td><input type="number" class="form-control pretest" name="pretest[]" min="0" max="50" step="0.1" value="${trainee.PreTest || ''}"></td>
                            <td><input type="number" class="form-control att-grade" name="att_grade[]" min="0" max="10" step="0.1" value="${trainee.AttGrade || ''}" readonly></td>
                            <td><input type="number" class="form-control participation" name="participation[]" min="0" max="10" step="0.1" value="${trainee.Participation || ''}" required></td>
                            <td><input type="number" class="form-control quiz1" name="quiz1[]" min="0" max="30" step="0.1" value="${trainee.Quiz1 || ''}" required></td>
                            <td><input type="number" class="form-control quiz2" name="quiz2[]" min="0" max="30" step="0.1" value="${trainee.Quiz2 || ''}"></td>
                            <td><input type="number" class="form-control quiz3" name="quiz3[]" min="0" max="30" step="0.1" value="${trainee.Quiz3 || ''}"></td>
                            <td><input type="number" class="form-control quiz-avg" name="quiz_avg[]" min="0" max="30" step="0.1" value="${trainee.QuizAvg || ''}" readonly></td>
                            <td><input type="number" class="form-control final-exam" name="final_exam[]" min="0" max="50" step="0.1" value="${trainee.FinalExam || ''}" required></td>
                            <td><input type="number" class="form-control course-total" name="course_total[]" min="0" max="100" step="0.1" value="${trainee.CourseTotal || ''}" readonly></td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                    
                    // Enable save button
                    saveGradesBtn.disabled = false;
                    
                    // Add event listeners for auto-calculation
                    setupGradeCalculations();
                })
                .catch(error => console.error('Error fetching trainees:', error));
        } else {
            // Hide table if no course selected
            gradeTableContainer.classList.add('d-none');
            saveGradesBtn.disabled = true;
        }
    });
    
    // Save grades button
    saveGradesBtn.addEventListener('click', function() {
        const form = document.getElementById('gradeForm');
        if (form.checkValidity()) {
            form.submit();
        } else {
            // Trigger browser's native validation
            form.reportValidity();
        }
    });
});

// Setup attendance calculations
function setupAttendanceCalculations() {
    const rows = document.querySelectorAll('#attendanceTableBody tr');
    
    rows.forEach(row => {
        const pHoursInput = row.querySelector('.p-hours');
        const eHoursInput = row.querySelector('.e-hours');
        const lHoursInput = row.querySelector('.l-hours');
        const aHoursInput = row.querySelector('.a-hours');
        const sessionsInput = row.querySelector('.sessions');
        const pointsInput = row.querySelector('.points');
        const percentageInput = row.querySelector('.percentage');
        
        // Function to calculate points and percentage
        const calculateAttendance = () => {
            const pHours = parseFloat(pHoursInput.value) || 0;
            const eHours = parseFloat(eHoursInput.value) || 0;
            const lHours = parseFloat(lHoursInput.value) || 0;
            const aHours = parseFloat(aHoursInput.value) || 0;
            const sessions = parseFloat(sessionsInput.value) || 0;
            
            if (sessions > 0) {
                const totalHours = pHours + eHours + lHours + aHours;
                const totalPoints = sessions * 2; // Assuming 2 points per session
                const earnedPoints = pHours * 2; // Full points for present hours
                
                pointsInput.value = `${earnedPoints} / ${totalPoints}`;
                
                // Calculate percentage (based on present hours)
                const percentage = (pHours / sessions) * 100;
                percentageInput.value = percentage.toFixed(1);
            }
        };
        
        // Add event listeners to inputs
        [pHoursInput, eHoursInput, lHoursInput, aHoursInput, sessionsInput].forEach(input => {
            input.addEventListener('input', calculateAttendance);
        });
    });
}

// Setup grade calculations
function setupGradeCalculations() {
    const rows = document.querySelectorAll('#gradeTableBody tr');
    
    rows.forEach(row => {
        const pretestInput = row.querySelector('.pretest');
        const attGradeInput = row.querySelector('.att-grade');
        const participationInput = row.querySelector('.participation');
        const quiz1Input = row.querySelector('.quiz1');
        const quiz2Input = row.querySelector('.quiz2');
        const quiz3Input = row.querySelector('.quiz3');
        const quizAvgInput = row.querySelector('.quiz-avg');
        const finalExamInput = row.querySelector('.final-exam');
        const courseTotalInput = row.querySelector('.course-total');
        
        // Function to calculate quiz average
        const calculateQuizAvg = () => {
            const quiz1 = parseFloat(quiz1Input.value) || 0;
            const quiz2 = parseFloat(quiz2Input.value);
            const quiz3 = parseFloat(quiz3Input.value);
            
            let quizCount = 1; // Quiz 1 is mandatory
            let quizSum = quiz1;
            
            // Only include Quiz 2 if it has a value
            if (!isNaN(quiz2)) {
                quizCount++;
                quizSum += quiz2;
            }
            
            // Only include Quiz 3 if it has a value
            if (!isNaN(quiz3)) {
                quizCount++;
                quizSum += quiz3;
            }
            
            // Calculate average
            const quizAvg = quizSum / quizCount;
            quizAvgInput.value = quizAvg.toFixed(1);
            
            // Trigger course total calculation
            calculateCourseTotal();
        };
        
        // Function to calculate course total
        const calculateCourseTotal = () => {
            const attGrade = parseFloat(attGradeInput.value) || 0;
            const participation = parseFloat(participationInput.value) || 0;
            const quizAvg = parseFloat(quizAvgInput.value) || 0;
            const finalExam = parseFloat(finalExamInput.value) || 0;
            
            // Course Total = Attendance (10) + Participation (10) + Quiz Avg (30) + Final Exam (50)
            const courseTotal = attGrade + participation + quizAvg + finalExam;
            courseTotalInput.value = courseTotal.toFixed(1);
        };
        
        // Add event listeners to inputs
        [participationInput, quiz1Input, quiz2Input, quiz3Input, finalExamInput].forEach(input => {
            input.addEventListener('input', calculateQuizAvg);
        });
        
        // Special handling for attendance grade
        // This would typically be populated from the attendance data
        // For now, we'll just set it to 0
    });
}

// Function to open attendance modal with pre-selected course
function openAttendanceModal(groupCourseId, courseName, groupName) {
    // Set modal title
    document.getElementById('attendanceEntryModalLabel').textContent = 
        `Attendance Submission: ${courseName} (${groupName})`;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('attendanceEntryModal'));
    modal.show();
    
    // Load trainees for this course
    fetch(`get_trainees_for_attendance.php?group_course_id=${groupCourseId}`)
        .then(response => response.json())
        .then(data => {
            // Show table container
            document.getElementById('attendanceTableContainer').classList.remove('d-none');
            
            // Clear previous rows
            const tableBody = document.getElementById('attendanceTableBody');
            tableBody.innerHTML = '';
            
            // Add trainees to table
            data.forEach(trainee => {
                const row = document.createElement('tr');
                
                // Add hidden input for trainee ID and group course ID
                const tidInput = `<input type="hidden" name="trainee_ids[]" value="${trainee.TID}">`;
                const gcidInput = `<input type="hidden" name="group_course_id" value="${groupCourseId}">`;
                
                // Create cells
                row.innerHTML = `
                    <td>${tidInput}${gcidInput}${trainee.FirstName}</td>
                    <td>${trainee.LastName}</td>
                    <td>${trainee.GovID}</td>
                    <td><input type="number" class="form-control p-hours" name="present_hours[]" min="0" required></td>
                    <td><input type="number" class="form-control e-hours" name="excused_hours[]" min="0" required></td>
                    <td><input type="number" class="form-control l-hours" name="late_hours[]" min="0" required></td>
                    <td><input type="number" class="form-control a-hours" name="absent_hours[]" min="0" required></td>
                    <td><input type="number" class="form-control sessions" name="taken_sessions[]" min="0" required></td>
                    <td><input type="text" class="form-control points" name="points[]" required></td>
                    <td><input type="number" class="form-control percentage" name="attendance_percentage[]" min="0" max="100" step="0.1" required></td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Enable save button
            document.getElementById('saveAttendanceBtn').disabled = false;
            
            // Add event listeners for auto-calculation
            setupAttendanceCalculations();
        })
        .catch(error => console.error('Error fetching trainees:', error));
}

// Function to open grades modal with pre-selected course
function openGradesModal(groupCourseId, courseName, groupName) {
    // Set modal title
    document.getElementById('gradeEntryModalLabel').textContent = 
        `Grade Submission: ${courseName} (${groupName})`;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('gradeEntryModal'));
    modal.show();
    
    // Load trainees for this course
    fetch(`get_trainees_for_grades.php?group_course_id=${groupCourseId}`)
        .then(response => response.json())
        .then(data => {
            // Show table container
            document.getElementById('gradeTableContainer').classList.remove('d-none');
            
            // Clear previous rows
            const tableBody = document.getElementById('gradeTableBody');
            tableBody.innerHTML = '';
            
            // Add trainees to table
            data.forEach(trainee => {
                const row = document.createElement('tr');
                
                // Add hidden input for trainee ID and group course ID
                const tidInput = `<input type="hidden" name="trainee_ids[]" value="${trainee.TID}">`;
                const gcidInput = `<input type="hidden" name="group_course_id" value="${groupCourseId}">`;
                
                // Create cells with existing data if available
                row.innerHTML = `
                    <td>${tidInput}${gcidInput}${trainee.FirstName}</td>
                    <td>${trainee.LastName}</td>
                    <td>${trainee.GovID}</td>
                    <td><input type="number" class="form-control pretest" name="pretest[]" min="0" max="50" step="0.1" value="${trainee.PreTest || ''}"></td>
                    <td><input type="number" class="form-control att-grade" name="att_grade[]" min="0" max="10" step="0.1" value="${trainee.AttGrade || ''}" readonly></td>
                    <td><input type="number" class="form-control participation" name="participation[]" min="0" max="10" step="0.1" value="${trainee.Participation || ''}" required></td>
                    <td><input type="number" class="form-control quiz1" name="quiz1[]" min="0" max="30" step="0.1" value="${trainee.Quiz1 || ''}" required></td>
                    <td><input type="number" class="form-control quiz2" name="quiz2[]" min="0" max="30" step="0.1" value="${trainee.Quiz2 || ''}"></td>
                    <td><input type="number" class="form-control quiz3" name="quiz3[]" min="0" max="30" step="0.1" value="${trainee.Quiz3 || ''}"></td>
                    <td><input type="number" class="form-control quiz-avg" name="quiz_avg[]" min="0" max="30" step="0.1" value="${trainee.QuizAvg || ''}" readonly></td>
                    <td><input type="number" class="form-control final-exam" name="final_exam[]" min="0" max="50" step="0.1" value="${trainee.FinalExam || ''}" required></td>
                    <td><input type="number" class="form-control course-total" name="course_total[]" min="0" max="100" step="0.1" value="${trainee.CourseTotal || ''}" readonly></td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Enable save button
            document.getElementById('saveGradesBtn').disabled = false;
            
            // Add event listeners for auto-calculation
            setupGradeCalculations();
        })
        .catch(error => console.error('Error fetching trainees:', error));
}
</script>
