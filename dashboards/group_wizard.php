<?php
// This file should only contain the HTML content for the modal body.
// It should NOT include header.php or footer.php, as it's loaded via AJAX.

// Ensure database connection is available (from parent page's header.php)
// and authentication/permissions are handled by the API endpoint.
// If this file is accessed directly, it should redirect or show an error.
if (!isset($conn)) {
    // Fallback if accessed directly without $conn being set (e.g., for testing)
    // In a production environment, this should be handled more robustly,
    // e.g., redirect to an error page or login.
    // For now, we'll include config to get $conn for local testing/development.
    require_once '../includes/config.php';
}
require_once '../includes/auth.php'; // Add auth.php

// RBAC guard: Only users with 'manage_groups' permission can access this page.
if (!hasPermission('manage_groups')) {
    // Since this is loaded via AJAX, we should output an error message and exit.
    echo '<div class="alert alert-danger" role="alert">You do not have permission to create groups.</div>';
    die();
}

// Fetch coordinators for Step 1
$coordinators = [];
$stmt = $conn->prepare("SELECT UserID, FirstName, LastName FROM Users WHERE Role = 'Coordinator' AND IsActive = 1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $coordinators[] = $row;
    }
    $stmt->close();
}

// Fetch instructors for Step 3
$instructors = [];
$stmt = $conn->prepare("SELECT UserID, FirstName, LastName FROM Users WHERE Role = 'Instructor' AND IsActive = 1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
    $stmt->close();
}

// Fetch courses for Step 3
$courses = [];
$stmt = $conn->prepare("SELECT CourseID, CourseName, CourseCode FROM Courses WHERE Status = 'Active'");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
}

// Close the database connection if it was opened specifically for this file
if (isset($conn) && $conn instanceof mysqli && $conn->thread_id && !isset($original_conn)) {
    $conn->close();
}
?>

<div class="card">
    <div class="card-body">
        <div id="wizard-modal" class="bs-stepper wizard-numbered mt-2">
            <div class="bs-stepper-header">
                <div class="step" data-target="#group-details">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Group Details</span>
                            <span class="bs-stepper-subtitle">Set up group information</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="bx bx-chevron-right"></i>
                </div>
                <div class="step" data-target="#add-trainees">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Add Trainees</span>
                            <span class="bs-stepper-subtitle">Bulk add new trainees</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="bx bx-chevron-right"></i>
                </div>
                <div class="step" data-target="#assign-courses">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Assign Courses</span>
                            <span class="bs-stepper-subtitle">Link courses to group</span>
                        </span>
                    </button>
                </div>
                <div class="line">
                    <i class="bx bx-chevron-right"></i>
                </div>
                <div class="step" data-target="#confirmation">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Confirmation</span>
                            <span class="bs-stepper-subtitle">Review & Finish</span>
                        </span>
                    </button>
    </div>
</div>

<!-- Include the wizard's JavaScript file -->
<script src="../assets/dashjs/group_wizard.js?v=<?php echo time(); ?>"></script>
            <div class="bs-stepper-content">
                <form id="wizard-form" onsubmit="return false">
                    <!-- Group Details -->
                    <div id="group-details" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Group Information</h5>
                            <small>Enter the details for the new training group.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="groupName">Group Name <span class="text-danger">*</span></label>
                                <input type="text" id="groupName" name="groupName" class="form-control" placeholder="e.g., Water Chemistry Batch 2025" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="program">Program</label>
                                <input type="text" id="program" name="program" class="form-control" placeholder="e.g., Water Quality Training" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="duration">Duration (Weeks)</label>
                                <input type="number" id="duration" name="duration" class="form-control" placeholder="e.g., 12" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="semesters">Semesters</label>
                                <input type="number" id="semesters" name="semesters" class="form-control" placeholder="e.g., 1" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="startDate">Start Date</label>
                                <input type="date" id="startDate" name="startDate" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="endDate">End Date</label>
                                <input type="date" id="endDate" name="endDate" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="status">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Planned">Planned</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="room">Room</label>
                                <input type="text" id="room" name="room" class="form-control" placeholder="e.g., A101" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="coordinatorID">Coordinator</label>
                                <select id="coordinatorID" name="coordinatorID" class="form-select">
                                    <option value="">Select Coordinator</option>
                                    <?php foreach ($coordinators as $coordinator): ?>
                                        <option value="<?php echo $coordinator['UserID']; ?>"><?php echo htmlspecialchars($coordinator['FirstName'] . ' ' . $coordinator['LastName']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief description of the group"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm me-sm-1 me-0"></i>
                                <span class="align-middle d-sm-inline-block d-none">Previous</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="align-middle d-sm-inline-block d-none">Next</span>
                                <i class="bx bx-chevron-right bx-sm ms-sm-1 ms-0"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Add Trainees -->
                    <div id="add-trainees" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Add New Trainees</h5>
                            <small>Enter trainee details, one per line (FirstName,LastName,Email,GovID,Phone).</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="traineeData">Trainee Data (CSV format)</label>
                                <textarea id="traineeData" name="traineeData" class="form-control" rows="10" placeholder="e.g., John,Doe,john.doe@example.com,12345,0501234567&#10;Jane,Smith,jane.smith@example.com,67890,0509876543"></textarea>
                                <small class="text-muted">Format: FirstName,LastName,Email,GovID (optional),Phone (optional)</small>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="traineePreviewTable">
                                        <thead>
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Gov ID</th>
                                                <th>Phone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Trainee data preview will be inserted here by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-label-secondary btn-prev">
                                <i class="bx bx-chevron-left bx-sm me-sm-1 me-0"></i>
                                <span class="align-middle d-sm-inline-block d-none">Previous</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="align-middle d-sm-inline-block d-none">Next</span>
                                <i class="bx bx-chevron-right bx-sm ms-sm-1 ms-0"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Assign Courses -->
                    <div id="assign-courses" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Assign Courses</h5>
                            <small>Select courses for this group and assign instructors.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div id="course-list-container">
                                    <?php if (empty($courses)): ?>
                                        <p>No active courses available. Please add courses first.</p>
                                    <?php else: ?>
                                        <?php foreach ($courses as $course): ?>
                                            <div class="card mb-3 course-assignment-card" data-course-id="<?php echo $course['CourseID']; ?>">
                                                <div class="card-body">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input course-checkbox" type="checkbox" id="course_<?php echo $course['CourseID']; ?>" name="selectedCourses[]" value="<?php echo $course['CourseID']; ?>" />
                                                        <label class="form-check-label" for="course_<?php echo $course['CourseID']; ?>">
                                                            <strong><?php echo htmlspecialchars($course['CourseName']); ?></strong> (<?php echo htmlspecialchars($course['CourseCode']); ?>)
                                                        </label>
                                                    </div>
                                                    <div class="course-details-fields" style="display: none;">
                                                        <div class="row g-2 mt-2">
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="instructor_<?php echo $course['CourseID']; ?>">Instructor</label>
                                                                <select id="instructor_<?php echo $course['CourseID']; ?>" name="course_instructor[<?php echo $course['CourseID']; ?>]" class="form-select">
                                                                    <option value="">Select Instructor</option>
                                                                    <?php foreach ($instructors as $instructor): ?>
                                                                        <option value="<?php echo $instructor['UserID']; ?>"><?php echo htmlspecialchars($instructor['FirstName'] . ' ' . $instructor['LastName']); ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="courseStartDate_<?php echo $course['CourseID']; ?>">Start Date</label>
                                                                <input type="date" id="courseStartDate_<?php echo $course['CourseID']; ?>" name="course_startDate[<?php echo $course['CourseID']; ?>]" class="form-control" />
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="courseEndDate_<?php echo $course['CourseID']; ?>">End Date</label>
                                                                <input type="date" id="courseEndDate_<?php echo $course['CourseID']; ?>" name="course_endDate[<?php echo $course['CourseID']; ?>]" class="form-control" />
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="location_<?php echo $course['CourseID']; ?>">Location</label>
                                                                <input type="text" id="location_<?php echo $course['CourseID']; ?>" name="course_location[<?php echo $course['CourseID']; ?>]" class="form-control" placeholder="e.g., Room 101" />
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label" for="scheduleDetails_<?php echo $course['CourseID']; ?>">Schedule Details</label>
                                                                <textarea id="scheduleDetails_<?php echo $course['CourseID']; ?>" name="course_scheduleDetails[<?php echo $course['CourseID']; ?>]" class="form-control" rows="2" placeholder="e.g., Mon-Wed-Fri 9AM-12PM"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none">Next</span>
                                    <i class="bx bx-chevron-right bx-sm ms-sm-1 ms-0"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div id="confirmation" class="content">
                            <div class="content-header mb-3">
                                <h5 class="mb-0">Confirmation</h5>
                                <small>Review the details before finalizing.</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6>Group Details:</h6>
                                    <p id="confirmGroupName"></p>
                                    <p id="confirmGroupDates"></p>
                                    <p id="confirmCoordinator"></p>
                                    <hr>
                                    <h6>Trainees to Add:</h6>
                                    <ul id="confirmTraineeList"></ul>
                                    <hr>
                                    <h6>Courses to Assign:</h6>
                                    <ul id="confirmCourseList"></ul>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm me-sm-1 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                </button>
                                <button class="btn btn-success btn-submit">
                                    <span class="align-middle d-sm-inline-block d-none">Submit</span>
                                    <i class="bx bx-check bx-sm ms-sm-1 ms-0"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Custom styles for Group Wizard modal content */
.bs-stepper-content {
    padding: 1.5rem; /* Add padding around the content of each step */
}

.bs-stepper-header {
    padding: 1rem 0; /* Add vertical padding to the header */
    background-color: var(--bs-tertiary-bg); /* Match modal header background */
    border-bottom: 1px solid var(--bs-border-color);
    border-top-left-radius: var(--bs-modal-border-radius);
    border-top-right-radius: var(--bs-modal-border-radius);
}

.bs-stepper-label {
    color: var(--bs-heading-color); /* Ensure step labels are visible */
}

.bs-stepper-circle {
    background-color: var(--bs-primary); /* Highlight active step */
    color: var(--bs-white);
}

.bs-stepper .step.active .bs-stepper-circle {
    background-color: var(--bs-primary);
    color: var(--bs-white);
}

.bs-stepper .step.active .bs-stepper-label {
    color: var(--bs-primary); /* Active step label color */
}

.bs-stepper .step.active .bs-stepper-subtitle {
    color: var(--bs-primary); /* Active step subtitle color */
}

.bs-stepper .step.completed .bs-stepper-circle {
    background-color: var(--bs-success); /* Completed step color */
    color: var(--bs-white);
}

.bs-stepper .step.completed .bs-stepper-label {
    color: var(--bs-success); /* Completed step label color */
}

.bs-stepper .step.completed .bs-stepper-subtitle {
    color: var(--bs-success); /* Completed step subtitle color */
}

.bs-stepper .line {
    color: var(--bs-border-color); /* Line color between steps */
}

/* Adjust form-label color within the wizard */
.bs-stepper-content .form-label {
    color: var(--bs-body-color); /* Ensure form labels are readable */
}

/* Style for the course assignment cards */
.course-assignment-card {
    background-color: var(--bs-secondary-bg-subtle); /* Slightly different background for nested cards */
    border: 1px solid var(--bs-border-color);
    box-shadow: none; /* Remove extra shadow */
}

.course-assignment-card .form-check-label {
    color: var(--bs-heading-color); /* Make course names stand out */
}

.course-details-fields {
    border-top: 1px dashed var(--bs-border-color);
    padding-top: 1rem;
    margin-top: 1rem;
}
</style>
