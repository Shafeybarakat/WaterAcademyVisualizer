<?php
$pageTitle = "Coordinators Management";
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/header.php";

// Check authorization - only users with view_users permission can access
if (!hasPermission('view_users')) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

// Handle coordinator update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_coordinator') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($userId > 0) {
        // Get the RoleID for Coordinator
        $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Coordinator'");
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        $roleRow = $roleResult->fetch_assoc();
        $roleID = $roleRow['RoleID'];
        $roleStmt->close();
        
        if (!empty($password)) {
            // Update with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE Users SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Password = ?, IsActive = ? WHERE UserID = ? AND RoleID = ?");
            $stmt->bind_param("sssssiii", $firstname, $lastname, $email, $phone, $hashedPassword, $isActive, $userId, $roleID);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE Users SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, IsActive = ? WHERE UserID = ? AND RoleID = ?");
            $stmt->bind_param("ssssiii", $firstname, $lastname, $email, $phone, $isActive, $userId, $roleID);
        }
        
        if ($stmt->execute()) {
            $successMessage = "Coordinator updated successfully.";
        } else {
            $errorMessage = "Failed to update coordinator: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle coordinator status toggle if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $newStatus = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;
    
    if ($userId > 0) {
        // Get the RoleID for Coordinator
        $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Coordinator'");
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        $roleRow = $roleResult->fetch_assoc();
        $roleID = $roleRow['RoleID'];
        $roleStmt->close();
        
        $stmt = $conn->prepare("UPDATE Users SET IsActive = ? WHERE UserID = ? AND RoleID = ?");
        $stmt->bind_param("iii", $newStatus, $userId, $roleID);
        
        if ($stmt->execute()) {
            $successMessage = "Coordinator status updated successfully.";
        } else {
            $errorMessage = "Failed to update coordinator status: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle coordinator deletion if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_coordinator') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($userId > 0) {
        // Check if coordinator has any associated groups
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Groups WHERE CoordinatorID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $groupCount = $row['count'];
        $stmt->close();
        
        if ($groupCount > 0) {
            $errorMessage = "Cannot delete coordinator. They are assigned to $groupCount group(s). Please reassign the groups first.";
        } else {
            // Get the RoleID for Coordinator
            $roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Coordinator'");
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $roleRow = $roleResult->fetch_assoc();
            $roleID = $roleRow['RoleID'];
            $roleStmt->close();
            
            $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND RoleID = ?");
            $stmt->bind_param("ii", $userId, $roleID);
            
            if ($stmt->execute()) {
                $successMessage = "Coordinator deleted successfully.";
            } else {
                $errorMessage = "Failed to delete coordinator: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all coordinators
$coordinators = [];

// Get the RoleID for Coordinator
$roleStmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = 'Coordinator'");
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$roleRow = $roleResult->fetch_assoc();
$roleID = $roleRow['RoleID'];
$roleStmt->close();

$sql = "SELECT 
            u.UserID, 
            u.Username, 
            u.FirstName, 
            u.LastName, 
            u.Email, 
            u.Phone, 
            u.IsActive,
            u.Status,
            u.LastLogin,
            (SELECT COUNT(*) FROM Groups WHERE CoordinatorID = u.UserID) as GroupCount
        FROM Users u 
        WHERE u.RoleID = ? 
        ORDER BY u.IsActive DESC, u.LastName, u.FirstName";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $roleID);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coordinators[] = $row;
    }
    $result->free();
} else {
    $errorMessage = "Failed to fetch coordinators: " . $conn->error;
}
$stmt->close();
?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">
    
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <?= htmlspecialchars($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <?= htmlspecialchars($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Coordinators List</h5>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" id="coordinatorSearch" class="form-control" placeholder="Search coordinators..." data-search-input data-search-target="coordinatorsTable">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="coordinatorsTable" class="table table-striped table-hover align-middle" data-table>
                    <thead>
                        <tr>
                            <th data-sort="name">Name</th>
                            <th data-sort="email">Email</th>
                            <th data-sort="phone">Phone</th>
                            <th data-sort="groups">Groups</th>
                            <th data-sort="login">Last Login</th>
                            <th style="width: 220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coordinators)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No coordinators found.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($coordinators as $coordinator): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($coordinator['FirstName'] . ' ' . $coordinator['LastName']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($coordinator['Email']) ?></td>
                                <td><?= htmlspecialchars($coordinator['Phone'] ?: 'N/A') ?></td>

                                <td>
                                    <?php if ($coordinator['GroupCount'] > 0): ?>
                                    <a href="groups.php?coordinator=<?= $coordinator['UserID'] ?>" class="badge bg-label-info">
                                        <?= $coordinator['GroupCount'] ?> group<?= $coordinator['GroupCount'] > 1 ? 's' : '' ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="badge bg-label-secondary">No groups</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $coordinator['LastLogin'] ? date('M d, Y H:i', strtotime($coordinator['LastLogin'])) : 'Never' ?>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-primary me-2 view-details-btn" 
                                            data-modal-target="coordinatorDetailsModal"
                                            data-modal-action="show"
                                            data-user-id="<?= $coordinator['UserID'] ?>"
                                            data-username="<?= htmlspecialchars($coordinator['Username']) ?>"
                                            data-firstname="<?= htmlspecialchars($coordinator['FirstName']) ?>"
                                            data-lastname="<?= htmlspecialchars($coordinator['LastName']) ?>"
                                            data-email="<?= htmlspecialchars($coordinator['Email']) ?>"
                                            data-phone="<?= htmlspecialchars($coordinator['Phone']) ?>"
                                            data-is-active="<?= $coordinator['IsActive'] ?>"
                                            data-status="<?= htmlspecialchars($coordinator['Status']) ?>">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-danger delete-btn" 
                                            data-modal-target="deleteConfirmModal"
                                            data-modal-action="show"
                                            data-user-id="<?= $coordinator['UserID'] ?>"
                                            data-name="<?= htmlspecialchars($coordinator['FirstName'] . ' ' . $coordinator['LastName']) ?>"
                                            data-group-count="<?= $coordinator['GroupCount'] ?>">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Coordinator Details Modal -->
<div class="modal fade" id="coordinatorDetailsModal" tabindex="-1" aria-labelledby="coordinatorDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="coordinatorDetailsModalLabel"><i class="bx bx-edit me-2"></i>Coordinator Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="coordinatorDetailsContent"></div>
                <form id="coordinatorDetailsForm" method="post" action="coordinators.php" style="display: none;">
                    <input type="hidden" name="action" value="update_coordinator">
                    <input type="hidden" id="edit_user_id" name="user_id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_username">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_status">Account Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_firstname">First Name</label>
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_lastname">Last Name</label>
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_phone">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_password">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDetailsBtn">Save Changes</button>
                <form id="toggleStatusForm" method="post" action="" class="d-inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" id="toggle_user_id" name="user_id" value="">
                    <input type="hidden" id="toggle_is_active" name="is_active" value="">
                    <button type="submit" class="btn btn-warning" id="toggleStatusBtn">
                        <i class="bx bx-power-off me-1"></i> <span id="toggleStatusBtnText">Deactivate</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bx bx-trash me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the coordinator <strong id="deleteCoordinatorName"></strong>?</p>
                <div id="deleteWarning" class="alert alert-warning d-none">
                    <i class="bx bx-error me-1"></i> This coordinator is assigned to <span id="deleteGroupCount"></span> group(s). 
                    Please reassign these groups before deleting.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="post" action="">
                    <input type="hidden" name="action" value="delete_coordinator">
                    <input type="hidden" id="delete_user_id" name="user_id" value="">
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>

<!-- Include pure JS modal handlers -->
<script>
// Pure JavaScript modal implementation that doesn't rely on Bootstrap or jQuery
(function() {
    console.log('Using pure JavaScript modal implementation');
    
    // Initialize modal event listeners
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Adding event listeners to coordinator buttons');
        
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
        document.querySelectorAll('[data-modal-target="coordinatorDetailsModal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                console.log('Edit button clicked for coordinator ID:', userId);
                
                // Show loading spinner
                const modalContent = document.getElementById('coordinatorDetailsContent');
                if (modalContent) {
                    modalContent.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading coordinator details...</p></div>';
                }
                
                // Show the modal using our custom function
                showModal('coordinatorDetailsModal');
                
                // Fetch coordinator details via AJAX
                fetch(`get_coordinator.php?id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Set form values
                        document.getElementById('edit_user_id').value = data.UserID;
                        document.getElementById('edit_username').value = data.Username;
                        document.getElementById('edit_firstname').value = data.FirstName;
                        document.getElementById('edit_lastname').value = data.LastName;
                        document.getElementById('edit_email').value = data.Email;
                        document.getElementById('edit_phone').value = data.Phone || '';
                        document.getElementById('edit_is_active').checked = data.IsActive == 1;
                        
                        // Set toggle status form values
                        document.getElementById('toggle_user_id').value = data.UserID;
                        document.getElementById('toggle_is_active').value = data.IsActive ? '0' : '1';
                        document.getElementById('toggleStatusBtnText').textContent = data.IsActive ? 'Deactivate' : 'Activate';
                        document.getElementById('toggleStatusBtn').className = data.IsActive ? 'btn btn-warning' : 'btn btn-success';
                        
                        // Remove loading spinner
                        if (modalContent) {
                            modalContent.innerHTML = '';
                        }
                        
                        // Show the form
                        document.getElementById('coordinatorDetailsForm').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching coordinator details:', error);
                        if (modalContent) {
                            modalContent.innerHTML = `<div class="alert alert-danger">Error loading coordinator details: ${error.message}</div>`;
                        }
                    });
            });
        });
        
        // Add click handlers for all Delete buttons
        document.querySelectorAll('[data-modal-target="deleteConfirmModal"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const name = this.getAttribute('data-name');
                const groupCount = parseInt(this.getAttribute('data-group-count'));
                
                console.log('Delete button clicked for coordinator:', name);
                
                // Set coordinator details in the modal
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('deleteCoordinatorName').textContent = name;
                
                // Show warning and disable delete button if coordinator has groups
                const deleteWarning = document.getElementById('deleteWarning');
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                
                if (groupCount > 0) {
                    deleteWarning.classList.remove('d-none');
                    document.getElementById('deleteGroupCount').textContent = groupCount;
                    confirmDeleteBtn.disabled = true;
                } else {
                    deleteWarning.classList.add('d-none');
                    confirmDeleteBtn.disabled = false;
                }
                
                // Show the modal using our custom function
                showModal('deleteConfirmModal');
            });
        });
        
        // Handle save details button
        const saveDetailsBtn = document.getElementById('saveDetailsBtn');
        if (saveDetailsBtn) {
            saveDetailsBtn.addEventListener('click', function() {
                const form = document.getElementById('coordinatorDetailsForm');
                const password = document.getElementById('edit_password').value;
                const confirmPassword = document.getElementById('edit_confirm_password').value;
                
                // Validate password match if provided
                if (password && password !== confirmPassword) {
                    alert('Passwords do not match!');
                    return;
                }
                
                // Set the is_active value based on checkbox
                const isActiveCheckbox = document.getElementById('edit_is_active');
                const isActiveInput = document.createElement('input');
                isActiveInput.type = 'hidden';
                isActiveInput.name = 'is_active';
                isActiveInput.value = isActiveCheckbox.checked ? '1' : '0';
                form.appendChild(isActiveInput);
                
                // Submit the form
                form.submit();
            });
        }
        
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
        
        // Make sure table headers and modal content are readable in dark mode
        const darkModeHandler = () => {
            if (document.body.classList.contains('theme-dark')) {
                document.querySelectorAll('th').forEach(th => {
                    th.style.color = '#ffffff';
                });
                document.querySelectorAll('.modal-body label').forEach(label => {
                    label.style.color = '#ffffff';
                });
            } else {
                document.querySelectorAll('th').forEach(th => {
                    th.style.color = '';
                });
                document.querySelectorAll('.modal-body label').forEach(label => {
                    label.style.color = '';
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
    });
})();
</script>

<script src="../assets/js/wa-table.js"></script>
