<?php
$pageTitle = "Trainees";
include_once "../includes/config.php";
include_once "../includes/auth.php";   // Session and permission checks
include_once "../includes/header.php";

// Check if user has appropriate permission
if (!isLoggedIn() || !hasPermission('view_trainees')) {
    echo "<div class='alert alert-danger'>You do not have permission to access this page.</div>";
    include_once "../includes/footer.php";
    exit;
}

// Get groups for filter dropdown
$groupsQuery = "SELECT GroupID, GroupName FROM Groups ORDER BY GroupName";
$groupsResult = $conn->query($groupsQuery);

// Get filter parameters
$groupFilter = isset($_GET['group']) ? $_GET['group'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$query = "SELECT TID, FirstName, LastName, CONCAT(FirstName, ' ', LastName) AS FullName, Email, Phone, GroupID 
          FROM Trainees WHERE 1=1";

if (!empty($groupFilter)) {
    $query .= " AND GroupID = '$groupFilter'";
}

if (!empty($searchTerm)) {
    $query .= " AND (FirstName LIKE '%$searchTerm%' OR LastName LIKE '%$searchTerm%' OR Email LIKE '%$searchTerm%')";
}

$query .= " ORDER BY LastName, FirstName";
$traineesResult = $conn->query($query);

// Process form submission for editing trainee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_trainee') {
    $tid = $_POST['tid'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Update trainee information
    $updateQuery = "UPDATE Trainees SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE TID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $tid);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Trainee information updated successfully.</div>";
        // Refresh the page to show updated data
        echo "<script>window.location.href = 'trainees.php" . (isset($_GET['group']) ? "?group=" . $_GET['group'] : "") . 
             (isset($_GET['search']) ? (isset($_GET['group']) ? "&" : "?") . "search=" . $_GET['search'] : "") . "';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error updating trainee information: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Search Trainees</h5>
            </div>
            <div class="card-body">
                <form method="get" class="d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or email..." value="<?= htmlspecialchars($searchTerm) ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Trainees Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Trainees</h5>
                <?php if (hasPermission('manage_trainees')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTraineeModal">
                    <i class="bx bx-plus me-1"></i> Add Trainee
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="traineesTable" class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th data-sort="id">ID <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="name">Name <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="email">Email <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="phone">Phone <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="group">Group <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th style="width: 120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($traineesResult && $traineesResult->num_rows > 0): ?>
                                <?php while ($trainee = $traineesResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $trainee['TID'] ?></td>
                                        <td><?= htmlspecialchars($trainee['FullName']) ?></td>
                                        <td><?= htmlspecialchars($trainee['Email']) ?></td>
                                        <td><?= htmlspecialchars($trainee['Phone']) ?></td>
                                        <td>
                                            <?php
                                            // Get group name
                                            if ($trainee['GroupID']) {
                                                $groupQuery = "SELECT GroupName FROM Groups WHERE GroupID = ?";
                                                $stmt = $conn->prepare($groupQuery);
                                                $stmt->bind_param("s", $trainee['GroupID']);
                                                $stmt->execute();
                                                $groupResult = $stmt->get_result();
                                                if ($group = $groupResult->fetch_assoc()) {
                                                    echo htmlspecialchars($group['GroupName']);
                                                }
                                            } else {
                                                echo "Not Assigned";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-primary" 
                                                        onclick="editTrainee('<?= $trainee['TID'] ?>')">
                                                    <i class="bx bx-edit-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No trainees found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the trainee <strong id="deleteTraineeName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="trainee_delete.php">
                    <input type="hidden" id="deleteTraineeId" name="trainee_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Trainee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Trainee Modal -->
<div class="modal fade" id="editTraineeModal" tabindex="-1" aria-labelledby="editTraineeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editTraineeModalLabel"><i class="bx bx-edit me-2"></i>Edit Trainee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTraineeForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_trainee">
                    <input type="hidden" id="edit_tid" name="tid">
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold">Personal Information</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                        
                        <div class="col-12 mt-4 mb-3">
                            <h6 class="fw-bold">Group Information</h6>
                            <hr>
                            <p class="text-muted small">This information is view-only. A trainee's group assignment can only be changed through group management.</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Group</label>
                            <input type="text" class="form-control" id="edit_group" readonly>
                        </div>
                        
                        <div class="col-12 mt-4 mb-3">
                            <h6 class="fw-bold">Course Information</h6>
                            <hr>
                            <p class="text-muted small">This information is view-only. Courses are assigned to groups, not individual trainees.</p>
                        </div>
                        
                        <div class="col-12">
                            <div id="trainee_courses" class="list-group">
                                <!-- Course list will be populated by JavaScript -->
                            </div>
                            <div id="no_courses_message" class="alert alert-info mt-2" style="display: none;">
                                No courses assigned to this trainee's group.
                            </div>
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
    function confirmDelete(id, name) {
        document.getElementById('deleteTraineeId').value = id;
        document.getElementById('deleteTraineeName').textContent = name;
        
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    
    function editTrainee(id) {
        // Fetch trainee data via AJAX
        fetch('get_trainee_data.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_tid').value = data.trainee.TID;
                    document.getElementById('edit_first_name').value = data.trainee.FirstName;
                    document.getElementById('edit_last_name').value = data.trainee.LastName;
                    document.getElementById('edit_email').value = data.trainee.Email;
                    document.getElementById('edit_phone').value = data.trainee.Phone;
                    document.getElementById('edit_group').value = data.group ? data.group.GroupName : 'Not Assigned';
                    
                    // Populate courses
                    const coursesContainer = document.getElementById('trainee_courses');
                    const noCoursesMessage = document.getElementById('no_courses_message');
                    
                    coursesContainer.innerHTML = '';
                    
                    if (data.courses && data.courses.length > 0) {
                        data.courses.forEach(course => {
                            const courseItem = document.createElement('div');
                            courseItem.className = 'list-group-item';
                            courseItem.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">${course.CourseName}</h6>
                                        <small class="text-muted">Status: ${course.Status || 'N/A'}</small>
                                    </div>
                                </div>
                            `;
                            coursesContainer.appendChild(courseItem);
                        });
                        noCoursesMessage.style.display = 'none';
                    } else {
                        coursesContainer.innerHTML = '';
                        noCoursesMessage.style.display = 'block';
                    }
                    
                    // Show modal
                    var editModal = new bootstrap.Modal(document.getElementById('editTraineeModal'));
                    editModal.show();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching trainee data.');
            });
    }
</script>

<?php include_once "../includes/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table search functionality
    const searchInput = document.getElementById('search');
    const table = document.getElementById('traineesTable');
    if (table) {
        const rows = table.querySelectorAll('tbody tr');

        // Live search within loaded results
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
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
    }
});
</script>
