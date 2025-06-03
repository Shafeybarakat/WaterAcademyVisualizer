<?php
$pageTitle = "Data Entry";
require_once "../includes/config.php";
require_once "../includes/auth.php"; // Ensure user is logged in and authorized
include_once "../includes/header.php"; // Includes sidebar via its own include

// Determine form type from GET parameter
$formType = $_GET['form_type'] ?? ''; // Default to empty if not set

// Initialize variables for form pre-population (especially for edit mode if implemented)
$editData = null; // Placeholder for data if editing an existing entry

// Fetch data needed for dropdowns - this should ideally be done based on $formType
// For simplicity, fetching all common ones here. Refine if performance is an issue.
$coursesResult = $conn->query("SELECT CourseID, CourseName FROM Courses ORDER BY CourseName");
$traineesResult = $conn->query("SELECT TID, CONCAT(FirstName, ' ', LastName) AS FullName FROM Trainees ORDER BY LastName, FirstName");
$componentsResult = $conn->query("SELECT ComponentID, ComponentName, MaxPoints FROM GradeComponents ORDER BY ComponentName");

// TODO: Add logic here if an 'edit_id' is passed to populate $editData for a specific form type

?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">Data Entry Forms</div>
                <div class="list-group list-group-flush">
                    <a href="data_entry.php?form_type=attendance" class="list-group-item list-group-item-action <?php echo ($formType === 'attendance' ? 'active' : ''); ?>"><i class="bi bi-calendar-check me-2"></i>Attendance</a>
                    <a href="data_entry.php?form_type=grade" class="list-group-item list-group-item-action <?php echo ($formType === 'grade' ? 'active' : ''); ?>"><i class="bi bi-card-checklist me-2"></i>Grade</a>
                    <a href="data_entry.php?form_type=feedback" class="list-group-item list-group-item-action <?php echo ($formType === 'feedback' ? 'active' : ''); ?>"><i class="bi bi-chat-square-text me-2"></i>Feedback</a>
                    <a href="data_entry.php?form_type=lgi" class="list-group-item list-group-item-action <?php echo ($formType === 'lgi' ? 'active' : ''); ?>"><i class="bi bi-graph-up me-2"></i>LGI Pre-Test</a>
                    <a href="data_entry.php?form_type=batch" class="list-group-item list-group-item-action <?php echo ($formType === 'batch' ? 'active' : ''); ?>"><i class="bi bi-collection me-2"></i>Batch Entry</a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <?php if ($formType === 'attendance'): ?>
                <!-- Attendance Entry Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Attendance Entry</h5></div>
                    <div class="card-body">
                        <form method="post" action="data_entry_process.php"> {/* TODO: Create data_entry_process.php or similar */}
                            <input type="hidden" name="form_type" value="attendance">
                            {/* Add attendance form fields: Course, Trainee, Date, Status, Notes */}
                            <p>Attendance form fields go here...</p>
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        </form>
                    </div>
                </div>
            <?php elseif ($formType === 'grade'): ?>
                <!-- Grade Entry Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Grade Entry</h5></div>
                    <div class="card-body">
                        <form method="post" action="data_entry_process.php">
                            <input type="hidden" name="form_type" value="grade">
                            {/* Add grade form fields: Course, Trainee, Component, Score, Comments, Grade Date */}
                            <p>Grade form fields go here...</p>
                            <button type="submit" class="btn btn-primary">Save Grade</button>
                        </form>
                    </div>
                </div>
            <?php elseif ($formType === 'feedback'): ?>
                <!-- Feedback Entry Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Feedback Entry</h5></div>
                    <div class="card-body">
                        <form method="post" action="data_entry_process.php">
                            <input type="hidden" name="form_type" value="feedback">
                            {/* Assuming $coursesResult and $traineesResult are available */}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="feedback_course_id" class="form-label">Course</label>
                                    <select class="form-select" id="feedback_course_id" name="course_id" required>
                                        <option value="">Select Course</option>
                                        <?php 
                                        if ($coursesResult) $coursesResult->data_seek(0);
                                        while ($coursesResult && $course = $coursesResult->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $course['CourseID']; ?>" <?php echo $editData && isset($editData['CourseID']) && $editData['CourseID'] === $course['CourseID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course['CourseName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="feedback_trainee_id" class="form-label">Trainee</label>
                                    <select class="form-select" id="feedback_trainee_id" name="trainee_id" required>
                                        <option value="">Select Trainee</option>
                                        <?php 
                                        if ($traineesResult) $traineesResult->data_seek(0);
                                        while ($traineesResult && $trainee = $traineesResult->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $trainee['TID']; ?>" <?php echo $editData && isset($editData['TID']) && $editData['TID'] === $trainee['TID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($trainee['FullName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="feedback_date" class="form-label">Feedback Date</label>
                                <input type="date" class="form-control" id="feedback_date" name="feedback_date" value="<?php echo $editData && isset($editData['FeedbackDate']) ? $editData['FeedbackDate'] : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="positive_feedback" class="form-label">Positive Feedback</label>
                                        <textarea class="form-control" id="positive_feedback" name="positive_feedback" rows="3"><?php echo $editData ? htmlspecialchars($editData['PositiveFeedback']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="areas_to_improve" class="form-label">Areas to Improve</label>
                                        <textarea class="form-control" id="areas_to_improve" name="areas_to_improve" rows="3"><?php echo $editData ? htmlspecialchars($editData['AreasToImprove']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Save Feedback</button>
                                    </div>
                        </form>
                    </div>
                </div>

                    <?php elseif ($formType === 'lgi'): ?>
                        <!-- LGI Entry Form -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Learning Gap Indicator Entry</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    The Learning Gap Indicator (LGI) measures improvement from pre-test to final assessment.
                                    Enter the pre-test score here. The LGI percentage will be calculated automatically when final grades are entered.
                                </div>
                                
                                <form method="post" action="data_entry.php">
                                    <input type="hidden" name="form_type" value="lgi">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="course_id" class="form-label">Course</label>
                                            <select class="form-select" id="course_id" name="course_id" required>
                                                <option value="">Select Course</option>
                                                <?php 
                                                if ($coursesResult) $coursesResult->data_seek(0);
                                                while ($coursesResult && $course = $coursesResult->fetch_assoc()): 
                                                ?>
                                                    <option value="<?php echo $course['CourseID']; ?>" <?php echo $editData && isset($editData['CourseID']) && $editData['CourseID'] === $course['CourseID'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($course['CourseName']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="trainee_id" class="form-label">Trainee</label>
                                            <select class="form-select" id="trainee_id" name="trainee_id" required>
                                                <option value="">Select Trainee</option>
                                                <?php 
                                                if ($traineesResult) $traineesResult->data_seek(0);
                                                while ($traineesResult && $trainee = $traineesResult->fetch_assoc()): 
                                                ?>
                                                    <option value="<?php echo $trainee['TID']; ?>" <?php echo $editData && isset($editData['TID']) && $editData['TID'] === $trainee['TID'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($trainee['FullName']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="pretest_score" class="form-label">Pre-Test Score</label>
                                        <input type="number" class="form-control" id="pretest_score" name="pretest_score" min="0" max="50" step="0.1" value="<?php echo $editData ? $editData['PreTestScore'] : ''; ?>" required>
                                        <div class="form-text">Enter score from 0-50</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="comments" class="form-label">Comments</label>
                                        <textarea class="form-control" id="comments" name="comments" rows="3"><?php echo $editData ? htmlspecialchars($editData['Comments']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Save LGI Data</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                    <?php elseif ($formType === 'batch'): ?>
                        <!-- Batch Entry Form -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Batch Data Entry</h5>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs" id="batchEntryTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-batch" type="button" role="tab">Attendance</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades-batch" type="button" role="tab">Grades</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="batchEntryContent">
                                    <div class="tab-pane fade show active" id="attendance-batch" role="tabpanel">
                                        <form method="post" action="batch_process.php">
                                            <input type="hidden" name="form_type" value="batch_attendance">
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="batch_course_id" class="form-label">Course</label>
                                                    <select class="form-select" id="batch_course_id" name="batch_course_id" required>
                                                        <option value="">Select Course</option>
                                                        <?php 
                                                        if ($coursesResult) $coursesResult->data_seek(0);
                                                        while ($coursesResult && $course = $coursesResult->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $course['CourseID']; ?>">
                                                                <?php echo htmlspecialchars($course['CourseName']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="batch_date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="batch_date" name="batch_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div id="trainees_attendance_container">
                                                <div class="alert alert-info">
                                                    Select a course to load enrolled trainees
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid mt-3">
                                                <button type="submit" class="btn btn-primary">Save Batch Attendance</button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="grades-batch" role="tabpanel">
                                        <form method="post" action="batch_process.php">
                                            <input type="hidden" name="form_type" value="batch_grades">
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label for="batch_grade_course_id" class="form-label">Course</label>
                                                    <select class="form-select" id="batch_grade_course_id" name="batch_grade_course_id" required>
                                                        <option value="">Select Course</option>
                                                        <?php 
                                                        if ($coursesResult) $coursesResult->data_seek(0);
                                                        while ($coursesResult && $course = $coursesResult->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $course['CourseID']; ?>">
                                                                <?php echo htmlspecialchars($course['CourseName']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="batch_component_id" class="form-label">Component</label>
                                                    <select class="form-select" id="batch_component_id" name="batch_component_id" required>
                                                        <option value="">Select Component</option>
                                                        <?php 
                                                        if ($componentsResult) $componentsResult->data_seek(0);
                                                        while ($componentsResult && $component = $componentsResult->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $component['ComponentID']; ?>">
                                                                <?php echo htmlspecialchars($component['ComponentName']); ?> (Max: <?php echo $component['MaxPoints']; ?>)
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="batch_grade_date" class="form-label">Grade Date</label>
                                                    <input type="date" class="form-control" id="batch_grade_date" name="batch_grade_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div id="trainees_grades_container">
                                                <div class="alert alert-info">
                                                    Select a course and component to load enrolled trainees
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid mt-3">
                                                <button type="submit" class="btn btn-primary">Save Batch Grades</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No form type selected - show instruction page -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Data Entry Instructions</h5>
                            </div>
                            <div class="card-body">
                                <p>Select one of the data entry forms from the menu on the left:</p>
                                
                                <div class="list-group mt-3">
                                    <div class="list-group-item">
                                        <h5><i class="bi bi-calendar-check me-2"></i> Attendance Entry</h5>
                                        <p class="mb-0">Record daily attendance for trainees, including status (Present, Late, Excused, Absent) and notes.</p>
                                    </div>
                                    <div class="list-group-item">
                                        <h5><i class="bi bi-card-checklist me-2"></i> Grade Entry</h5>
                                        <p class="mb-0">Enter grades for specific components of courses, such as quizzes, participation, and final exams.</p>
                                    </div>
                                    <div class="list-group-item">
                                        <h5><i class="bi bi-chat-square-text me-2"></i> Feedback Entry</h5>
                                        <p class="mb-0">Provide qualitative feedback for trainees, including positive observations and areas for improvement.</p>
                                    </div>
                                    <div class="list-group-item">
                                        <h5><i class="bi bi-graph-up me-2"></i> LGI Entry</h5>
                                        <p class="mb-0">Record pre-test scores for trainees to establish baselines for learning gap improvement metrics.</p>
                                    </div>
                                    <div class="list-group-item">
                                        <h5><i class="bi bi-collection me-2"></i> Batch Entry</h5>
                                        <p class="mb-0">Enter data for multiple trainees at once, ideal for recording daily attendance or component grades.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <!-- end .col-md-9 -->
        </div> <!-- end .row -->
</div> <!-- end .container-xxl -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle batch attendance loading
    const batchCourseSelect = document.getElementById('batch_course_id');
    if (batchCourseSelect) {
        batchCourseSelect.addEventListener('change', function() {
            const courseId = this.value;
            if (courseId) {
                loadTraineesForBatchAttendance(courseId);
            }
        });
    }
    
    // Handle batch grades loading
    const batchGradeCourseSelect = document.getElementById('batch_grade_course_id');
    if (batchGradeCourseSelect) {
        batchGradeCourseSelect.addEventListener('change', function() {
            const courseId = this.value;
            const componentId = document.getElementById('batch_component_id').value;
            if (courseId && componentId) {
                loadTraineesForBatchGrades(courseId, componentId);
            }
        });
    }
    
    const batchComponentSelect = document.getElementById('batch_component_id');
    if (batchComponentSelect) {
        batchComponentSelect.addEventListener('change', function() {
            const componentId = this.value;
            const courseId = document.getElementById('batch_grade_course_id').value;
            if (courseId && componentId) {
                loadTraineesForBatchGrades(courseId, componentId);
            }
        });
    }
});

function loadTraineesForBatchAttendance(courseId) {
    const container = document.getElementById('trainees_attendance_container');
    
    // Show loading
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Fetch enrolled trainees
    fetch(`batch_load.php?action=attendance&course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.trainees.length > 0) {
                let html = '<table class="table table-striped">';
                html += '<thead><tr><th>Trainee</th><th>Status</th><th>Notes</th></tr></thead><tbody>';
                
                data.trainees.forEach(trainee => {
                    html += `<tr>
                        <td>${trainee.FullName} <input type="hidden" name="trainee_ids[]" value="${trainee.TID}"></td>
                        <td>
                            <select class="form-select" name="statuses[${trainee.TID}]" required>
                                <option value="Present">Present</option>
                                <option value="Late">Late</option>
                                <option value="Excused">Excused</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="notes[${trainee.TID}]">
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-warning">No trainees enrolled in this course.</div>';
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">Error loading trainees: ' + error.message + '</div>';
        });
}

function loadTraineesForBatchGrades(courseId, componentId) {
    const container = document.getElementById('trainees_grades_container');
    
    // Show loading
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Fetch enrolled trainees
    fetch(`batch_load.php?action=grades&course_id=${courseId}&component_id=${componentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.trainees.length > 0) {
                let html = '<table class="table table-striped">';
                html += '<thead><tr><th>Trainee</th><th>Score</th><th>Comments</th></tr></thead><tbody>';
                
                data.trainees.forEach(trainee => {
                    html += `<tr>
                        <td>${trainee.FullName} <input type="hidden" name="trainee_ids[]" value="${trainee.TID}"></td>
                        <td>
                            <input type="number" class="form-control" name="scores[${trainee.TID}]" min="0" max="${data.max_points}" step="0.1" required value="${trainee.ExistingScore || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="comments[${trainee.TID}]">
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-warning">No trainees enrolled in this course.</div>';
            }
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">Error loading trainees: ' + error.message + '</div>';
        });
}
</script>

<?php include_once "../includes/footer.php"; ?>
