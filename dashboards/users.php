<?php
$pageTitle = "Users Management";
// Include config.php and auth.php via header.php
include_once "../includes/header.php";

// Check if user is logged in and has permission to manage users
if (!require_permission('manage_users', '../login.php')) {
    echo '<div class="container-xxl flex-grow-1 container-p-y"><div class="alert alert-danger" role="alert">' . ($_SESSION['access_denied_message'] ?? 'You do not have permission to access this page.') . '</div></div>';
    include_once "../includes/footer.php"; // Ensure footer is included
    die(); // Terminate script
}

// Process user actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                // Get form data
                $username = $_POST['username'] ?? '';
                $firstName = $_POST['first_name'] ?? '';
                $lastName = $_POST['last_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
                $role = $_POST['role'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $specialty = $_POST['specialty'] ?? '';
                
                // Insert new user
                // Get the RoleID for the selected role
                $roleQuery = "SELECT RoleID FROM Roles WHERE RoleName = ?";
                $roleStmt = $conn->prepare($roleQuery);
                $roleStmt->bind_param("s", $role);
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                $roleRow = $roleResult->fetch_assoc();
                $roleID = $roleRow['RoleID'];
                $roleStmt->close();
                
                $query = "INSERT INTO Users (Username, FirstName, LastName, Email, Password, Role, RoleID, Phone, Specialty, IsActive) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssssisss", $username, $firstName, $lastName, $email, $password, $role, $roleID, $phone, $specialty);
                
                if ($stmt->execute()) {
                    $successMessage = "User added successfully.";
                } else {
                    $errorMessage = "Error adding user: " . $conn->error;
                }
                break;
                
            case 'edit':
                // Get form data
                $userId = $_POST['user_id'] ?? '';
                $username = $_POST['username'] ?? '';
                $firstName = $_POST['first_name'] ?? '';
                $lastName = $_POST['last_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $role = $_POST['role'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $specialty = $_POST['specialty'] ?? '';
                $isActive = $_POST['status'] ?? 0;
                
                // Update password only if provided
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                // Get the RoleID for the selected role
                $roleQuery = "SELECT RoleID FROM Roles WHERE RoleName = ?";
                $roleStmt = $conn->prepare($roleQuery);
                $roleStmt->bind_param("s", $role);
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                $roleRow = $roleResult->fetch_assoc();
                $roleID = $roleRow['RoleID'];
                $roleStmt->close();
                
                $query = "UPDATE Users SET Username = ?, FirstName = ?, LastName = ?, Email = ?, Password = ?, 
                              Role = ?, RoleID = ?, Phone = ?, Specialty = ?, IsActive = ? WHERE UserID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssssssissii", $username, $firstName, $lastName, $email, $password, $role, $roleID, $phone, $specialty, $isActive, $userId);
                } else {
                // Get the RoleID for the selected role
                $roleQuery = "SELECT RoleID FROM Roles WHERE RoleName = ?";
                $roleStmt = $conn->prepare($roleQuery);
                $roleStmt->bind_param("s", $role);
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                $roleRow = $roleResult->fetch_assoc();
                $roleID = $roleRow['RoleID'];
                $roleStmt->close();
                
                $query = "UPDATE Users SET Username = ?, FirstName = ?, LastName = ?, Email = ?, 
                              Role = ?, RoleID = ?, Phone = ?, Specialty = ?, IsActive = ? WHERE UserID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssssisiii", $username, $firstName, $lastName, $email, $role, $roleID, $phone, $specialty, $isActive, $userId);
                }
                
                if ($stmt->execute()) {
                    $successMessage = "User updated successfully.";
                } else {
                    $errorMessage = "Error updating user: " . $conn->error;
                }
                break;
                
            case 'delete':
                // Get user ID
                $userId = $_POST['user_id'] ?? '';
                
                // Don't allow deletion of the current user
                if ($userId == $_SESSION['user_id']) {
                    $errorMessage = "You cannot delete your own account.";
                    break;
                }
                
                // Check if user is referenced in other tables
                $checkCoursesQuery = "SELECT COUNT(*) as count FROM Courses WHERE InstructorID = ?";
                $stmt = $conn->prepare($checkCoursesQuery);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $courseCount = $stmt->get_result()->fetch_assoc()['count'];
                
                if ($courseCount > 0) {
                    $errorMessage = "Cannot delete user. They are assigned to $courseCount courses.";
                    break;
                }
                
                // Delete user
                $query = "DELETE FROM Users WHERE UserID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    $successMessage = "User deleted successfully.";
                } else {
                    $errorMessage = "Error deleting user: " . $conn->error;
                }
                break;
        }
    }
}

// Fetch all users with their role names
$usersQuery = "SELECT u.*, r.RoleName FROM Users u 
               LEFT JOIN Roles r ON u.RoleID = r.RoleID 
               ORDER BY u.UpdatedAt DESC";
$usersResult = $conn->query($usersQuery);
?>

<div class="container-xxl flex-grow-1 container-p-y pt-0">
        
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="action-card mb-4">
            <div class="action-card-header d-flex justify-content-between align-items-center">
                <h5 class="action-card-title">Users List</h5>
                <div class="d-flex align-items-center">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="userSearch" data-search-input data-search-target="usersTable" class="form-control" placeholder="Search users...">
                    </div>
                    <button type="button" class="btn btn-primary ms-2" onclick="addUser()">
                        <i class="bx bx-plus me-1"></i> Add User
                    </button>
                </div>
            </div>
            <div class="action-card-content p-0">
                <div class="table-responsive">
                    <table id="usersTable" data-table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th data-sort="id">ID <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="name">Full Name <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="email">Email Address <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="role">Role <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="login">Last Login <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th data-sort="status">Status <i class="bx bx-sort-alt-2 text-muted"></i></th>
                                <th style="width: 220px">Actions</th>
                            </tr>
                        </thead>
                <tbody>
                    <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['UserID']; ?></td>
                                <td>
                                    <?php 
                                    if (!empty($user['FirstName']) || !empty($user['LastName'])) {
                                        echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']);
                                    } else {
                                        echo htmlspecialchars($user['Username']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                <td><?php echo htmlspecialchars($user['RoleName'] ?? $user['Role']); ?></td>
                                <td>
                                    <?php 
                                    echo $user['UpdatedAt'] ? date('M j, Y, g:i a', strtotime($user['UpdatedAt'])) : 'Never';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['IsActive'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $user['IsActive'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-primary me-2" 
                                                onclick="editUser(<?php echo $user['UserID']; ?>, 
                                                         '<?php echo addslashes($user['Username']); ?>', 
                                                         '<?php echo addslashes($user['FirstName'] ?? ''); ?>', 
                                                         '<?php echo addslashes($user['LastName'] ?? ''); ?>', 
                                                         '<?php echo addslashes($user['Email']); ?>', 
                                                         '<?php echo addslashes($user['Role']); ?>', 
                                                         '<?php echo addslashes($user['Phone'] ?? ''); ?>', 
                                                         '<?php echo addslashes($user['Specialty'] ?? ''); ?>', 
                                                         <?php echo $user['IsActive'] ? 1 : 0; ?>)">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </button>

                                        <button type="button" class="btn btn-danger" 
                                                onclick="confirmDelete(<?php echo $user['UserID']; ?>, 
                                                         '<?php echo addslashes(trim(($user['FirstName'] . ' ' . $user['LastName']) ?: $user['Username'])); ?>')">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
</div>

<!-- User Modal -->
<div x-data="{ open: false }" id="userModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-primary text-white px-6 py-4 flex justify-between items-center">
                <h5 class="modal-title" id="userModalLabel"><i class="bx bx-user-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close btn-close-white" @click="open = false" aria-label="Close"></button>
            </div>
            <form id="userForm" method="post" action="">
                <div class="modal-body p-6">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="user_id" name="user_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted" id="password_help">Leave blank to keep current password when editing.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <?php
                                // Get all roles from the database
                                $roles_query = "SELECT RoleName FROM Roles ORDER BY RoleID";
                                $roles_result = $conn->query($roles_query);
                                
                                if ($roles_result && $roles_result->num_rows > 0) {
                                    while ($role_row = $roles_result->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($role_row['RoleName']) . '">' . 
                                             htmlspecialchars($role_row['RoleName']) . '</option>';
                                    }
                                } else {
                                    // Fallback options if roles table query fails
                                    echo '<option value="Admin">Admin</option>';
                                    echo '<option value="Instructor">Instructor</option>';
                                    echo '<option value="Coordinator">Coordinator</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="specialty" class="form-label">Specialty</label>
                            <input type="text" class="form-control" id="specialty" name="specialty">
                        </div>
                    </div>
                    
                    <div class="mb-3" id="status_container" style="display: none;">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                    <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-data="{ open: false }" id="deleteModal" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="open = false"></div>
    
    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto overflow-hidden">
            <!-- Header -->
            <div class="bg-danger text-white px-6 py-4 flex justify-between items-center">
                <h5 class="modal-title" id="deleteModalLabel"><i class="bx bx-trash me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" @click="open = false" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <p>Are you sure you want to delete the user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
                <form id="deleteUserForm" method="post" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_user_id" name="user_id">
                </form>
            </div>
            <div class="modal-footer bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" form="deleteUserForm" class="btn btn-danger">Delete User</button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to add a new user
function addUser() {
    // Reset form fields
    document.getElementById('userModalLabel').innerText = 'Add New User';
    document.getElementById('action').value = 'add';
    document.getElementById('user_id').value = '';
    document.getElementById('username').value = '';
    document.getElementById('first_name').value = '';
    document.getElementById('last_name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_help').style.display = 'none';
    document.getElementById('role').value = 'Instructor';
    document.getElementById('phone').value = '';
    document.getElementById('specialty').value = '';
    document.getElementById('status_container').style.display = 'none';
    
    // Show the modal
    document.getElementById('userModal').__x.$data.open = true;
}

// Function to populate form for editing
function editUser(id, username, firstName, lastName, email, role, phone, specialty, isActive) {
    console.log('Edit user called for ID:', id);
    // Set form values
    document.getElementById('userModalLabel').innerText = 'Edit User';
    document.getElementById('action').value = 'edit';
    document.getElementById('user_id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('first_name').value = firstName || '';
    document.getElementById('last_name').value = lastName || '';
    document.getElementById('email').value = email;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_help').style.display = 'block';
    document.getElementById('role').value = role;
    document.getElementById('phone').value = phone || '';
    document.getElementById('specialty').value = specialty || '';
    document.getElementById('status').value = isActive ? '1' : '0';
    document.getElementById('status_container').style.display = 'block';
    
    // Show the modal
    document.getElementById('userModal').__x.$data.open = true;
}

// Function to confirm delete
function confirmDelete(id, name) {
    console.log('Confirm delete called for ID:', id);
    document.getElementById('deleteUserName').innerText = name;
    document.getElementById('delete_user_id').value = id;
    
    // Show the modal
    document.getElementById('deleteModal').__x.$data.open = true;
}
</script>

<?php include_once "../includes/footer.php"; ?>
