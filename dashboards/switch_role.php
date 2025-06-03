<?php
// switch_role.php - Script to handle role switching for admin users
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Force session to start fresh
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL); // Alternative way to set error reporting level

// Log function for debugging
function logDebug($message) {
    error_log("[SWITCH_ROLE_DEBUG] " . $message);
}

logDebug("Script started. POST data: " . print_r($_POST, true));
logDebug("Current session: " . print_r($_SESSION, true));

// Protect this page - only users with manage_roles permission can access
if (!isLoggedIn() || !hasPermission('manage_roles')) {
    logDebug("Access denied - not logged in or doesn't have manage_roles permission");
    header("Location: ../login.php?message=access_denied");
    exit;
}

// Store original user information if not already stored
if (!isset($_SESSION['original_user_id'])) {
    logDebug("Storing original user info");
    $_SESSION['original_user_id'] = $_SESSION['user_id'];
    $_SESSION['original_user_role'] = $_SESSION['user_role'];
    $_SESSION['original_role_id'] = $_SESSION['role_id'] ?? null;
    $_SESSION['original_full_name'] = $_SESSION['FullName'];
    $_SESSION['original_avatar_path'] = $_SESSION['AvatarPath'] ?? null;
    logDebug("Original user info stored: " . print_r([
        'id' => $_SESSION['original_user_id'],
        'role' => $_SESSION['original_user_role'],
        'role_id' => $_SESSION['original_role_id'],
        'name' => $_SESSION['original_full_name']
    ], true));
}

// Handle switch role request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    logDebug("Processing POST request with action: " . $_POST['action']);
    
    // Switch back to original role
    if ($_POST['action'] === 'switch_back' && isset($_SESSION['original_user_id'])) {
        logDebug("Switching back to original role");
        logDebug("Original user data: " . print_r([
            'id' => $_SESSION['original_user_id'],
            'role' => $_SESSION['original_user_role'],
            'name' => $_SESSION['original_full_name']
        ], true));
        
        // Restore original user session data
        $_SESSION['user_id'] = $_SESSION['original_user_id'];
        $_SESSION['user_role'] = $_SESSION['original_user_role'];
        $_SESSION['role_id'] = $_SESSION['original_role_id'];
        $_SESSION['FullName'] = $_SESSION['original_full_name'];
        $_SESSION['AvatarPath'] = $_SESSION['original_avatar_path'];
        
        // Reload permissions for the original user
        loadUserPermissions();
        
        // Clear the switched user data
        unset($_SESSION['switched_user_id']);
        unset($_SESSION['switched_user_role']);
        unset($_SESSION['switched_user_name']);
        
        // Force session write
        session_write_close();
        session_start();
        
        logDebug("Session after switch back: " . print_r($_SESSION, true));
        
        // Clear any potential cache
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
        
        // Redirect to dashboard with a unique parameter to prevent caching
        $redirectUrl = "index.php?message=switched_back&nocache=" . time();
        logDebug("Redirecting to: " . $redirectUrl);
        header("Location: " . $redirectUrl);
        exit;
    }
    
    // Switch to selected user's role
    if ($_POST['action'] === 'switch_role' && isset($_POST['user_id']) && isset($_POST['role'])) {
        $user_id = intval($_POST['user_id']);
        $role = $_POST['role'];
        
        // Get the role ID for the requested role
        $stmt = $conn->prepare("SELECT RoleID FROM Roles WHERE RoleName = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $role_id = $row['RoleID'];
        } else {
            header("Location: index.php?message=invalid_role");
            exit;
        }
        
        // Get the current user's role ID
        $current_role_id = $_SESSION['original_role_id'] ?? $_SESSION['role_id'];
        
        // Check if the requested role is below the current user's role in the hierarchy
        // For now, we'll use a simple check based on role IDs (lower ID = higher role)
        // In a more sophisticated system, you might query a role_hierarchy table
        if ($role_id <= $current_role_id) {
            header("Location: index.php?message=invalid_role");
            exit;
        }
        
        // Verify the user exists and has the selected role ID
        $stmt = $conn->prepare("SELECT UserID, r.RoleName as Role, u.RoleID, CONCAT(FirstName, ' ', LastName) AS FullName, AvatarPath 
                               FROM Users u 
                               JOIN Roles r ON u.RoleID = r.RoleID 
                               WHERE UserID = ? AND u.RoleID = ?");
        $stmt->bind_param("ii", $user_id, $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Store the switched user's information
            $_SESSION['switched_user_id'] = $row['UserID'];
            $_SESSION['switched_user_role'] = $row['Role'];
            $_SESSION['switched_user_name'] = $row['FullName'];
            
            // Update session with the switched user's data
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['user_role'] = $row['Role'];
            $_SESSION['role_id'] = $row['RoleID'];
            $_SESSION['FullName'] = $row['FullName'];
            $_SESSION['AvatarPath'] = $row['AvatarPath'];
            
            // Load permissions for the new role
            loadUserPermissions();
            
            // Redirect to appropriate dashboard based on role
            header("Location: index.php?message=role_switched");
            exit;
        } else {
            // User not found
            header("Location: index.php?message=user_not_found");
            exit;
        }
    }
}

// If we get here, it's an invalid request
header("Location: index.php?message=invalid_request");
exit;
