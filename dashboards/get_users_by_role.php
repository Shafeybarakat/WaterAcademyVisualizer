<?php
// get_users_by_role.php - AJAX endpoint to get users by role
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Protect this page - only users with manage_roles permission can access
if (!isLoggedIn() || !hasPermission('manage_roles')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get role from request
$role = isset($_GET['role']) ? $_GET['role'] : '';

if (empty($role)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Role parameter is required']);
    exit;
}

// Get the role ID for the requested role
$role_stmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = ?");
$role_stmt->bind_param("s", $role);
$role_stmt->execute();
$role_result = $role_stmt->get_result();

if ($role_row = $role_result->fetch_assoc()) {
    $role_id = $role_row['RoleID'];
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Get the current user's role ID
$current_role_id = $_SESSION['original_role_id'] ?? $_SESSION['role_id'];

// Check if the requested role is below the current user's role in the hierarchy
// For now, we'll use a simple check based on role IDs (lower ID = higher role)
// In a more sophisticated system, you might query a role_hierarchy table
if ($role_id <= $current_role_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'You cannot switch to this role']);
    exit;
}

// Get users with the selected role ID
$stmt = $conn->prepare("
    SELECT UserID, CONCAT(FirstName, ' ', LastName) AS FullName 
    FROM Users 
    WHERE RoleID = ? AND Status = 'Active'
    ORDER BY FirstName, LastName
");
$stmt->bind_param("i", $role_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);
