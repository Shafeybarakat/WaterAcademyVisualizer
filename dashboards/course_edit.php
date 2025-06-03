<?php
$pageTitle = "Edit Course";
require_once "../includes/config.php";
require_once "../includes/auth.php";
include_once "../includes/header.php";

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

// Get course ID from URL
$courseId = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($courseId)) {
    header("Location: courses.php");
    exit;
}

$success = false;
$errorMessage = '';

// Get course data
$courseQuery = "SELECT CourseID, CourseName, DurationWeeks, TotalHours FROM Courses WHERE CourseID = ?";
$courseStmt = $conn->prepare($courseQuery);
$courseStmt->bind_param("s", $courseId);
$courseStmt->execute();
$courseResult = $courseStmt->get_result();
$course = $courseResult->fetch_assoc();

if (!$course) {
    header("Location: courses.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $courseName = $_POST['course_name'] ?? '';
    $durationWeeks = $_POST['duration_weeks'] ? intval($_POST['duration_weeks']) : null;
    $totalHours = $_POST['total_hours'] ? intval($_POST['total_hours']) : null;

    // Validate form data
    if (empty($courseName)) {
        $errorMessage = "Course name is a required field.";
    } else {
        // Update course - only update fields that exist in the database
        $updateQuery = "UPDATE Courses SET 
                            CourseName = ?, 
                            DurationWeeks = ?, 
                            TotalHours = ?
                        WHERE CourseID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("siii", $courseName, $durationWeeks, $totalHours, $courseId);
        
        if ($updateStmt->execute()) {
            $success = true;
            
            // Reload course data
            $courseStmt->execute();
            $courseResult = $courseStmt->get_result();
            $course = $courseResult->fetch_assoc();
        } else {
            $errorMessage = "Error updating course: " . $conn->error;
        }
    }
}
?>

<main class="main-content">
    <div class="container-fluid py-4">
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Edit Course: <?php echo htmlspecialchars($course['CourseName']); ?></h4>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Courses
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Course updated successfully!
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
                    <div class="col-md-12 mb-3">
                        <label for="course_name" class="form-label">Course Name*</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required
                               value="<?php echo htmlspecialchars($course['CourseName']); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="duration_weeks" class="form-label">Duration (Weeks)</label>
                        <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" min="1"
                               value="<?php echo isset($course['DurationWeeks']) ? $course['DurationWeeks'] : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="total_hours" class="form-label">Total Hours</label>
                        <input type="number" class="form-control" id="total_hours" name="total_hours" min="1"
                               value="<?php echo isset($course['TotalHours']) ? $course['TotalHours'] : ''; ?>">
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="courses.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate duration when start and end dates change
        const durationInput = document.getElementById('duration_weeks');
        const totalHoursInput = document.getElementById('total_hours');
        
        // You could add additional JavaScript functionality here if needed
    });
</script>

<?php include_once "../includes/footer.php"; ?>
