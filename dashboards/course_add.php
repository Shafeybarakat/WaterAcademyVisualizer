<?php
$pageTitle = "Add Course";
// Include the header - this also includes config.php and auth.php
include_once "../includes/header.php";

// RBAC guard: Only users with 'manage_courses' permission can access this page.
if (!require_permission('manage_courses', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

$success = false;
$errorMessage = '';

// Get groups for dropdown
$groupsQuery = "SELECT GroupID, GroupName FROM Groups ORDER BY GroupName";
$groupsResult = $conn->query($groupsQuery);
$groups = [];
while ($row = $groupsResult->fetch_assoc()) {
    $groups[] = $row;
}

// Get instructors for dropdown
$instructorsQuery = "SELECT InstructorID, FullName as InstructorName FROM vw_Instructors WHERE IsActive = 1 ORDER BY LastName, FirstName";
$instructorsResult = $conn->query($instructorsQuery);
$instructors = [];
while ($row = $instructorsResult->fetch_assoc()) {
    $instructors[] = $row;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $courseName = $_POST['course_name'] ?? '';
    $courseCode = $_POST['course_code'] ?? '';
    $groupId = $_POST['group_id'] ?? '';
    $instructorId = $_POST['instructor_id'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $durationWeeks = $_POST['duration_weeks'] ?? '';
    $description = $_POST['description'] ?? '';
    $isActive = isset($_POST['is_active']) ? 'Active' : 'Archived';

    // Validate form data
    if (empty($courseName) || empty($groupId) || empty($instructorId)) {
        $errorMessage = "Course name, group, and instructor are required fields.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert course into Courses table
            $insertCourseQuery = "INSERT INTO Courses (CourseName, CourseCode, Description, DurationWeeks, TotalHours, Status) 
                                VALUES (?, ?, ?, ?, ?, ?)";
            $insertCourseStmt = $conn->prepare($insertCourseQuery);
            
            // Calculate total hours (assuming 5 hours per week)
            $totalHours = $durationWeeks * 5;
            
            $insertCourseStmt->bind_param("ssssss", $courseName, $courseCode, $description, $durationWeeks, $totalHours, $isActive);
            $insertCourseStmt->execute();
            
            // Get the auto-generated CourseID
            $courseId = $conn->insert_id;
            
            // Insert into GroupCourses table
            $insertGroupCourseQuery = "INSERT INTO GroupCourses (GroupID, CourseID, InstructorID, StartDate, EndDate, Status) 
                                      VALUES (?, ?, ?, ?, ?, 'Scheduled')";
            $insertGroupCourseStmt = $conn->prepare($insertGroupCourseQuery);
            $insertGroupCourseStmt->bind_param("iiiss", $groupId, $courseId, $instructorId, $startDate, $endDate);
            $insertGroupCourseStmt->execute();
            
            // Get the auto-generated GroupCourseID
            $groupCourseId = $conn->insert_id;
            
            // Commit transaction
            $conn->commit();
            
            $success = true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errorMessage = "Error creating course: " . $e->getMessage();
        }
    }
}
?>

<main class="main-content">
    <div class="container-fluid py-4">
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Add New Course</h4>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Courses
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Course created successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="course_name" class="form-label">Course Name*</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required
                               value="<?php echo $_POST['course_name'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="course_code" name="course_code"
                               value="<?php echo $_POST['course_code'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="group_id" class="form-label">Group*</label>
                        <select class="form-select" id="group_id" name="group_id" required>
                            <option value="">Select Group</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['GroupID']; ?>" <?php echo (isset($_POST['group_id']) && $_POST['group_id'] == $group['GroupID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['GroupName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="instructor_id" class="form-label">Instructor*</label>
                        <select class="form-select" id="instructor_id" name="instructor_id" required>
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['InstructorID']; ?>" <?php echo (isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['InstructorID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['InstructorName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo $_POST['start_date'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo $_POST['end_date'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="duration_weeks" class="form-label">Duration (Weeks)</label>
                        <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" min="1"
                               value="<?php echo $_POST['duration_weeks'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $_POST['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                    <label class="form-check-label" for="is_active">Active Course</label>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate duration when start and end dates change
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const durationInput = document.getElementById('duration_weeks');
        
        function calculateDuration() {
            if (startDateInput.value && endDateInput.value) {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (endDate >= startDate) {
                    // Calculate difference in days and convert to weeks (rounded up)
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const diffWeeks = Math.ceil(diffDays / 7);
                    
                    durationInput.value = diffWeeks;
                }
            }
        }
        
        startDateInput.addEventListener('change', calculateDuration);
        endDateInput.addEventListener('change', calculateDuration);
    });
</script>

<?php include_once "../includes/footer.php"; ?>
