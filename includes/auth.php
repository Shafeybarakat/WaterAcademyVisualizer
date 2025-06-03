<?php
// This file assumes config.php (for $conn and session_start()) is included before it

// Check if user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user's role ID
function getUserRoleID(): ?int {
    return $_SESSION['role_id'] ?? null;
}

// Get current user's role name
function getUserRole(): string {
    return $_SESSION['user_role'] ?? 'Guest';
}

// Get current user's details from session
function getCurrentUser(): array {
    return [
        'userID' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest',
        'email' => $_SESSION['email'] ?? null,
        'firstName' => $_SESSION['FirstName'] ?? '',
        'lastName' => $_SESSION['LastName'] ?? '',
        'fullName' => $_SESSION['FullName'] ?? 'Guest User',
        'role' => $_SESSION['user_role'] ?? 'Guest',
        'roleID' => $_SESSION['role_id'] ?? null,
        'avatarPath' => $_SESSION['AvatarPath'] ?? 'assets/img/avatars/1.png'
    ];
}

// Check if current user has a specific permission
function hasPermission(string $permissionName): bool {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $roleID = getUserRoleID();
    if (!$roleID) {
        return false;
    }
    
    // Check if the role has the specified permission
    $stmt = $conn->prepare("
        SELECT 1
        FROM RolePermissions rp
        JOIN Permissions p ON rp.PermissionID = p.PermissionID
        WHERE rp.RoleID = ? AND p.PermissionName = ?
    ");
    
    if (!$stmt) {
        error_log("Error preparing permission check statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("is", $roleID, $permissionName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Check if current user has one of the specified permissions
function hasAnyPermission(array $permissionNames): bool {
    foreach ($permissionNames as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    return false;
}

// Check if current user has all of the specified permissions
function hasAllPermissions(array $permissionNames): bool {
    foreach ($permissionNames as $permission) {
        if (!hasPermission($permission)) {
            return false;
        }
    }
    return true;
}

// For backward compatibility - check if user has one of the allowed roles
function has_role(array $allowed_roles): bool {
    if (!isLoggedIn()) {
        return false;
    }
    $user_role = getUserRole();
    return in_array($user_role, $allowed_roles);
}

// Utility redirect helper
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// Function to enforce page access based on permissions
function require_permission(string $permission, string $login_redirect_url_if_not_logged_in): bool {
    if (!isLoggedIn()) {
        // If not logged in at all, redirect to login.
        redirect($login_redirect_url_if_not_logged_in);
        return false; // Unreachable due to redirect() containing exit()
    }
    
    // User is logged in, now check permission.
    if (!hasPermission($permission)) {
        // Logged in, but doesn't have the required permission.
        $_SESSION['page_access_denied'] = true; 
        $_SESSION['access_denied_message'] = 'You do not have permission to access this page.';
        return false; // Indicate access is denied
    }
    
    unset($_SESSION['page_access_denied']); // Clear any lingering denial flags if access is granted
    unset($_SESSION['access_denied_message']);
    return true; // Access granted
}

// For backward compatibility - enforce page access based on roles
function require_role(array $allowed_roles, string $login_redirect_url_if_not_logged_in): bool {
    if (!isLoggedIn()) {
        // If not logged in at all, redirect to login.
        redirect($login_redirect_url_if_not_logged_in);
        return false; // Unreachable due to redirect() containing exit()
    }
    
    // User is logged in, now check roles.
    if (!has_role($allowed_roles)) {
        // Logged in, but not the right role.
        $_SESSION['page_access_denied'] = true; 
        $_SESSION['access_denied_message'] = 'You do not have permission to access this page.';
        return false; // Indicate access is denied for this specific role requirement
    }
    
    unset($_SESSION['page_access_denied']); // Clear any lingering denial flags if access is granted
    unset($_SESSION['access_denied_message']);
    return true; // Access granted
}

// Global check for authenticated areas
function protect_authenticated_area(string $login_url = '../login.php?message=login_required'): void {
    $is_protected = false;
    // Define directories that require authentication.
    $protected_dirs = ['/dashboards/', '/reports/', '/admin/'];
    
    // Get the directory of the current script relative to the web root.
    $current_script_directory = dirname($_SERVER['PHP_SELF']);
    // Normalize: ensure leading/trailing slashes for consistent matching.
    $current_script_directory_normalized = '/' . trim($current_script_directory, '/') . '/';
    // If script is in root, dirname might return '.' or '\', normalize to '/'
    if (in_array($current_script_directory, ['.', '\\', '/'])) {
        $current_script_directory_normalized = '/';
    }

    // Check if the current script is a report page
    $current_script = $_SERVER['PHP_SELF'];
    $is_report_page = (strpos($current_script, 'report_') !== false || 
                      strpos($current_script, 'trainee_report') !== false || 
                      strpos($current_script, 'group-analytics') !== false);
    
    // Special handling for report pages
    if ($is_report_page) {
        $is_protected = true;
    } else {
        // Regular directory-based protection
        foreach ($protected_dirs as $dir) {
            if (strpos($_SERVER['PHP_SELF'], $dir) !== false) {
                $is_protected = true;
                break;
            }
        }
    }

    if ($is_protected && !isLoggedIn()) {
        redirect($login_url);
    }
}

// Load user permissions into session for faster access
function loadUserPermissions(): void {
    global $conn;
    
    if (!isLoggedIn()) {
        return;
    }
    
    $roleID = getUserRoleID();
    if (!$roleID) {
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT p.PermissionName
        FROM RolePermissions rp
        JOIN Permissions p ON rp.PermissionID = p.PermissionID
        WHERE rp.RoleID = ?
    ");
    
    if (!$stmt) {
        error_log("Error preparing permissions loading statement: " . $conn->error);
        return;
    }
    
    $stmt->bind_param("i", $roleID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['PermissionName'];
    }
    
    $_SESSION['user_permissions'] = $permissions;
}

// Check if user has permission (using session cache)
function hasPermissionFast(string $permissionName): bool {
    if (!isset($_SESSION['user_permissions'])) {
        loadUserPermissions();
    }
    
    return in_array($permissionName, $_SESSION['user_permissions'] ?? []);
}

// Protect authenticated areas globally
// This will redirect to login if a user tries to access /dashboards/* or /reports/* without being logged in.
protect_authenticated_area('../login.php?message=login_required');
?>
