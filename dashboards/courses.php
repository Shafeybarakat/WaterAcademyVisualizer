<?php
$pageTitle = "Manage Courses";
include_once "../includes/config.php"; // Database connection
include_once "../includes/auth.php";   // Session and permission checks
include_once "../includes/header.php"; // Header, session checks

// Start session if not already started (header.php might do this)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has permission to view courses
if (!isLoggedIn()) {
    // isLoggedIn() is defined in auth.php, protect_authenticated_area in header.php should also catch this.
    // This is an additional safeguard.
    redirect($baseLinkPath . "login.php?message=login_required_for_page"); // $baseLinkPath from header.php
} elseif (!hasPermission('view_courses')) {
    // User is logged in but does not have the required permission.
    // Display access denied message within the layout.
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">You do not have permission to access this page.</div></div>';
    include_once "../includes/footer.php";
    exit;
}


// --- Fetch Courses Data ---
$courses = [];
// It's good practice to select specific columns from the main table
// and alias any columns from joined tables if needed for clarity.
// For now, assuming CourseID, CourseName, DurationWeeks, TotalHours are directly in Courses table.
$coursesQuery = "SELECT c.CourseID, c.CourseName, c.DurationWeeks, c.TotalHours 
                 FROM Courses c 
                 ORDER BY c.CourseName";
$result = $conn->query($coursesQuery);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    $result->free();
} else {
    // Handle query error - log it or display a user-friendly message
    error_log("Failed to fetch courses: " . $conn->error);
    // Set an error message to be displayed in the main content area
    $_SESSION['course_error'] = "Error fetching course data. Please try again later or contact support.";
}

// Process form submission for editing course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $courseId = $_POST['course_id'];
    $courseName = $_POST['course_name'];
    $durationWeeks = $_POST['duration_weeks'] ? intval($_POST['duration_weeks']) : null;
    $totalHours = $_POST['total_hours'] ? intval($_POST['total_hours']) : null;
    
    // Update course information - only update fields that exist in the database
    $updateQuery = "UPDATE Courses SET CourseName = ?, DurationWeeks = ?, TotalHours = ? WHERE CourseID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("siii", $courseName, $durationWeeks, $totalHours, $courseId);
    
    if ($stmt->execute()) {
        $_SESSION['course_success'] = "Course information updated successfully.";
        // Refresh the page to show updated data
        echo "<script>window.location.href = 'courses.php';</script>";
    } else {
        $_SESSION['course_error'] = "Error updating course information: " . $stmt->error;
    }
    $stmt->close();
}

?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">
    <?php
    // Display success message
    if (isset($_SESSION['course_success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                ' . htmlspecialchars($_SESSION['course_success']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['course_success']);
    }

    // Display error message
    if (isset($_SESSION['course_error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ' . htmlspecialchars($_SESSION['course_error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['course_error']);
    }
    ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Courses</h5>
            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="courseSearch" class="form-control" placeholder="Search courses...">
                </div>
            </div>
        </div>
        <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <div class="table-responsive">
                        <table id="coursesTable" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th data-sort="name">Course Name <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                    <th data-sort="duration">Duration (Weeks) <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                    <th data-sort="hours">Total Hours <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                    <th style="width: 180px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course["CourseName"]) ?></td>
                                        <td><?= htmlspecialchars($course["DurationWeeks"] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($course["TotalHours"] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-primary me-2" title="Edit Course" 
                                                        onclick="editCourse(<?= htmlspecialchars($course['CourseID']) ?>)">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </button>
                                                
                                                <button type="button" class="btn btn-danger" title="Delete Course" 
                                                        onclick="if(confirm('Are you sure you want to delete the course: ' + <?= json_encode($course['CourseName']) ?> + '? This action cannot be undone.')){ window.location.href='course_delete.php?id=<?= htmlspecialchars($course['CourseID']) ?>'; }">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (empty($_SESSION['course_error'])): // Only show "No courses" if there wasn't a DB error ?>
                    <p class="text-muted text-center">No courses found in the database. You can add one using the button above.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCourseModalLabel"><i class="bx bx-edit me-2"></i>Edit Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCourseForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_course">
                    <input type="hidden" id="edit_course_id" name="course_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_course_name" class="form-label">Course Name*</label>
                            <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_duration_weeks" class="form-label">Duration (Weeks)</label>
                            <input type="number" class="form-control" id="edit_duration_weeks" name="duration_weeks" min="1">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_total_hours" class="form-label">Total Hours</label>
                            <input type="number" class="form-control" id="edit_total_hours" name="total_hours" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editCourse(courseId) {
        // Find the course data from the courses array
        <?php echo 'const courses = ' . json_encode($courses) . ';'; ?>
        const course = courses.find(c => c.CourseID == courseId);
        
        if (course) {
            // Populate form fields
            document.getElementById('edit_course_id').value = course.CourseID;
            document.getElementById('edit_course_name').value = course.CourseName;
            document.getElementById('edit_duration_weeks').value = course.DurationWeeks || '';
            document.getElementById('edit_total_hours').value = course.TotalHours || '';
            
            // Show modal
            var editModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            editModal.show();
        } else {
            alert('Error: Course not found.');
        }
    }
</script>

<?php include_once "../includes/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table search functionality
    const searchInput = document.getElementById('courseSearch');
    const table = document.getElementById('coursesTable');
    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Make sure table headers are readable in dark mode
    const darkModeHandler = () => {
        if (document.body.classList.contains('theme-dark')) {
            document.querySelectorAll('th').forEach(th => {
                th.style.color = '#ffffff';
            });
        } else {
            document.querySelectorAll('th').forEach(th => {
                th.style.color = '';
            });
        }
    };
    
    // Run initially and add observer for theme changes
    darkModeHandler();
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                darkModeHandler();
            }
        });
    });
    observer.observe(document.body, { attributes: true });

    // Sorting functionality
    const headers = table.querySelectorAll('th[data-sort]');
    
    headers.forEach(header => {
        header.style.cursor = 'pointer'; // Add pointer cursor to sortable columns
        header.addEventListener('click', function() {
            const sortKey = this.dataset.sort;
            const isAscending = this.classList.contains('sort-asc');
            
            // Reset all headers
            headers.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                h.querySelector('i').className = 'bx bx-sort-alt-2 text-muted';
            });
            
            // Set new sort direction
            if (isAscending) {
                this.classList.add('sort-desc');
                this.querySelector('i').className = 'bx bx-sort-down text-primary';
            } else {
                this.classList.add('sort-asc');
                this.querySelector('i').className = 'bx bx-sort-up text-primary';
            }
            
            // Get rows as array for sorting
            const rowsArray = Array.from(rows);
            
            // Sort rows
            rowsArray.sort((a, b) => {
                let aValue = a.children[Array.from(headers).indexOf(this)].textContent.trim();
                let bValue = b.children[Array.from(headers).indexOf(this)].textContent.trim();
                
                // For numeric columns, convert to numbers
                if (sortKey === 'duration' || sortKey === 'hours') {
                    aValue = aValue === 'N/A' ? 0 : parseFloat(aValue);
                    bValue = bValue === 'N/A' ? 0 : parseFloat(bValue);
                }
                
                if (aValue < bValue) return isAscending ? -1 : 1;
                if (aValue > bValue) return isAscending ? 1 : -1;
                return 0;
            });
            
            // Remove all rows
            const tbody = table.querySelector('tbody');
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }
            
            // Append sorted rows
            rowsArray.forEach(row => tbody.appendChild(row));
        });
    });
});
</script>
