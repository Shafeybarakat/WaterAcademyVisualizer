<?php
$pageTitle = "Instructors";
include_once "../includes/config.php";
include_once "../includes/auth.php";   // Session and permission checks
include_once "../includes/header.php";

// Check if user is logged in and has permission to manage instructors
if (!isLoggedIn()) {
    // isLoggedIn() is defined in auth.php, protect_authenticated_area in header.php should also catch this.
    // This is an additional safeguard.
    redirect($baseLinkPath . "login.php?message=login_required_for_page"); // $baseLinkPath from header.php
} elseif (!hasPermission('manage_users')) {
    // User is logged in but does not have the required permission.
    // Display access denied message within the layout.
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">You do not have permission to access this page.</div></div>';
    include_once "../includes/footer.php";
    exit;
}

// Fetch instructors from view
$instructorsQuery = "SELECT * FROM vw_Instructors ORDER BY LastName, FirstName";
$instructorsResult = $conn->query($instructorsQuery);
?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Instructors</h5>
            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="instructorSearch" class="form-control" placeholder="Search instructors..." data-search-input data-search-target="instructorsTable">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="instructorsTable" class="table table-striped table-hover align-middle" data-table>
                <thead>
                    <tr>
                        <th data-sort="name">Name</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="specialty">Specialty</th>
                        <th style="width: 220px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($instructorsResult && $instructorsResult->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while ($instructor = $instructorsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($instructor['FullName']) ?></td>
                                <td><?= htmlspecialchars($instructor['Email']) ?></td>
                                <td><?= htmlspecialchars($instructor['Specialty'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-primary me-2 edit-instructor-btn"
                                                data-modal-target="editInstructorModal" 
                                                data-modal-action="show"
                                                data-instructor-id="<?= htmlspecialchars($instructor['InstructorID']) ?>">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-info me-2 text-white courses-instructor-btn"
                                                data-modal-target="instructorCoursesModal"
                                                data-modal-action="show"
                                                data-instructor-id="<?= htmlspecialchars($instructor['InstructorID']) ?>"
                                                data-instructor-name="<?= htmlspecialchars($instructor['FullName']) ?>">
                                            <i class="bx bx-book-open me-1"></i> Courses
                                        </button>
                                        <button type="button" class="btn btn-danger delete-instructor-btn"
                                                data-modal-target="deleteInstructorModal"
                                                data-modal-action="show"
                                                data-instructor-id="<?= htmlspecialchars($instructor['InstructorID']) ?>"
                                                data-instructor-name="<?= htmlspecialchars($instructor['FullName']) ?>">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No instructors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Instructor Modal -->
<div class="modal fade" id="addInstructorModal" tabindex="-1" aria-labelledby="addInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addInstructorModalLabel"><i class="bx bx-plus-circle me-2"></i>Add New Instructor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-instructor-form" action="instructor_actions.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="add-firstname" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="add-lastname" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="add-email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="add-username" name="username" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="add-password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-specialty" class="form-label">Specialty</label>
                            <input type="text" class="form-control" id="add-specialty" name="specialty">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="add-phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-qualifications" class="form-label">Qualifications</label>
                        <textarea class="form-control" id="add-qualifications" name="qualifications" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="add-instructor-form" class="btn btn-primary">Save Instructor</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Instructor Modal -->
<div class="modal fade" id="editInstructorModal" tabindex="-1" aria-labelledby="editInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editInstructorModalLabel"><i class="bx bx-edit me-2"></i>Edit Instructor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <form id="edit-instructor-form" action="instructor_actions.php" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="instructor_id" id="edit-instructor-id">
                    
                    <!-- Same form fields as add form, but with 'edit-' prefix -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit-firstname" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit-lastname" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-specialty" class="form-label">Specialty</label>
                            <input type="text" class="form-control" id="edit-specialty" name="specialty">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="edit-phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-qualifications" class="form-label">Qualifications</label>
                        <textarea class="form-control" id="edit-qualifications" name="qualifications" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is-active">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="edit-instructor-form" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteInstructorModal" tabindex="-1" aria-labelledby="deleteInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteInstructorModalLabel"><i class="bx bx-trash me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete instructor <strong id="delete-instructor-name"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
                <form id="delete-instructor-form" action="instructor_actions.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="instructor_id" id="delete-instructor-id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="delete-instructor-form" class="btn btn-danger">Delete Instructor</button>
            </div>
        </div>
    </div>
</div>

<!-- Instructor Courses Modal -->
<div class="modal fade" id="instructorCoursesModal" tabindex="-1" aria-labelledby="instructorCoursesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="instructorCoursesModalLabel"><i class="bx bx-book-open me-2"></i>Courses for <span id="instructor-courses-name"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center loading-spinner-courses">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading courses...</p>
                </div>
                <div id="instructor-courses-content" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Course Name</th>
                                    <th>Group</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="instructor-courses-table-body">
                                <!-- Courses will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="no-courses-message" class="alert alert-info" style="display: none;">
                        This instructor is not assigned to any courses.
                    </div>
                </div>
                <div id="instructor-courses-error" class="alert alert-danger" style="display: none;">
                    Error loading courses. Please try again.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to load instructor details in the edit modal
function loadInstructorDetails(instructorId) {
    console.log('Loading instructor details:', instructorId);

    const loadingSpinner = document.querySelector('#editInstructorModal .loading-spinner');
    const editForm = document.getElementById('edit-instructor-form');

    // Set the instructor ID in the form
    document.getElementById('edit-instructor-id').value = instructorId;

    // Show loading spinner, hide form
    loadingSpinner.style.display = 'block';
    editForm.style.display = 'none';

    // Fetch instructor details via AJAX
    fetch(`get_instructor.php?id=${instructorId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Populate form with instructor data
            document.getElementById('edit-firstname').value = data.FirstName;
            document.getElementById('edit-lastname').value = data.LastName;
            document.getElementById('edit-email').value = data.Email;
            document.getElementById('edit-specialty').value = data.Specialty || '';
            document.getElementById('edit-phone').value = data.Phone || '';
            document.getElementById('edit-qualifications').value = data.Qualifications || '';
            document.getElementById('edit-is-active').checked = data.IsActive == 1;
            
            // Hide spinner, show form
            loadingSpinner.style.display = 'none';
            editForm.style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching instructor details:', error);
            loadingSpinner.style.display = 'none';
            editForm.innerHTML = '<div class="alert alert-danger">Error loading instructor details. Please try again.</div>';
            editForm.style.display = 'block';
        });
}

// Function to load instructor courses in the courses modal
function loadInstructorCourses(instructorId, instructorName) {
    console.log('Loading courses for instructor:', instructorId, instructorName);

    // Set the instructor name in the modal title
    document.getElementById('instructor-courses-name').textContent = instructorName;

    // Show loading spinner, hide content and error
    document.querySelector('.loading-spinner-courses').style.display = 'block';
    document.getElementById('instructor-courses-content').style.display = 'none';
    document.getElementById('instructor-courses-error').style.display = 'none';
    document.getElementById('no-courses-message').style.display = 'none';

    // Fetch instructor courses via AJAX with cache-busting parameter
    const url = `get_instructor_courses_admin.php?instructor_id=${instructorId}&nocache=${new Date().getTime()}`;
    console.log('Fetching courses from:', url);

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);

            // Hide loading spinner
            document.querySelector('.loading-spinner-courses').style.display = 'none';

            if (data.error) {
                console.error('Error in response:', data.error);
                // Show error message
                document.getElementById('instructor-courses-error').textContent = data.error;
                document.getElementById('instructor-courses-error').style.display = 'block';
                return;
            }

            // Show content
            document.getElementById('instructor-courses-content').style.display = 'block';

            if (data.length === 0) {
                console.log('No courses found');
                // No courses found
                document.getElementById('no-courses-message').style.display = 'block';
                return;
            }

            // Populate table with courses
            const tableBody = document.getElementById('instructor-courses-table-body');
            tableBody.innerHTML = '';
            console.log('Populating table with', data.length, 'courses');

            data.forEach(course => {
                console.log('Processing course:', course);
                // Create status badge with appropriate color
                let statusBadgeClass = '';
                switch (course.Status) {
                    case 'Active':
                        statusBadgeClass = 'bg-success';
                        break;
                    case 'Upcoming':
                        statusBadgeClass = 'bg-primary';
                        break;
                    case 'Completed':
                        statusBadgeClass = 'bg-secondary';
                        break;
                    default:
                        statusBadgeClass = 'bg-info';
                }
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${course.CourseName || 'N/A'}</td>
                    <td>${course.GroupName || 'N/A'}</td>
                    <td>${course.StartDate || 'N/A'}</td>
                    <td>${course.EndDate || 'N/A'}</td>
                    <td><span class="badge ${statusBadgeClass}">${course.Status || 'Unknown'}</span></td>
                `;
                tableBody.appendChild(row);
            });
            console.log('Table populated successfully');
        })
        .catch(error => {
            console.error('Error fetching instructor courses:', error);
            document.querySelector('.loading-spinner-courses').style.display = 'none';
            document.getElementById('instructor-courses-error').textContent = 'Error loading courses. Please try again.';
            document.getElementById('instructor-courses-error').style.display = 'block';
        });
}
</script>

<?php include_once "../includes/footer.php"; ?>

<!-- Include pure JS modal handlers -->
<script>
// Pure JavaScript modal implementation that doesn't rely on Bootstrap or jQuery
(function() {
    console.log('Using pure JavaScript modal implementation');
    
    // Initialize modal event listeners
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Adding event listeners to edit buttons');
        
        // Helper function to show a modal without jQuery or Bootstrap
        function showModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                // Add the 'show' class to display the modal
                modal.classList.add('show');
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
                
                // Create backdrop if it doesn't exist
                var backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.classList.add('modal-backdrop', 'fade', 'show');
                    document.body.appendChild(backdrop);
                }
            }
        }
        
        // Helper function to hide a modal
        function hideModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                
                // Remove backdrop
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
        }
        
        // Set up close buttons in all modals
        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                var modal = this.closest('.modal');
                if (modal) {
                    hideModal(modal.id);
                }
            });
        });
        
        // Add click handlers for all Edit buttons
        document.querySelectorAll('[data-modal-target="editInstructorModal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const instructorId = this.getAttribute('data-instructor-id');
                console.log('Edit button clicked for instructor ID:', instructorId);
                
                // Load instructor details
                loadInstructorDetails(instructorId);
                
                // Show the modal using our custom function
                showModal('editInstructorModal');
            });
        });
        
        // Add click handlers for all Courses buttons
        document.querySelectorAll('[data-modal-target="instructorCoursesModal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const instructorId = this.getAttribute('data-instructor-id');
                const instructorName = this.getAttribute('data-instructor-name');
                console.log('Courses button clicked for instructor:', instructorName);
                
                // Load instructor courses
                loadInstructorCourses(instructorId, instructorName);
                
                // Show the modal using our custom function
                showModal('instructorCoursesModal');
            });
        });
        
        // Add click handlers for all Delete buttons
        document.querySelectorAll('[data-modal-target="deleteInstructorModal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const instructorId = this.getAttribute('data-instructor-id');
                const instructorName = this.getAttribute('data-instructor-name');
                console.log('Delete button clicked for instructor:', instructorName);
                
                // Set instructor details in the modal
                document.getElementById('delete-instructor-id').value = instructorId;
                document.getElementById('delete-instructor-name').textContent = instructorName;
                
                // Show the modal using our custom function
                showModal('deleteInstructorModal');
            });
        });
        
        // Listen for clicks outside the modal to close it
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                hideModal(e.target.id);
            }
        });
        
        // Listen for ESC key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(function(modal) {
                    hideModal(modal.id);
                });
            }
        });
    });
})();
</script>

<script src="../assets/js/wa-table.js"></script>
