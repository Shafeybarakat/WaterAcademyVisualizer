<?php
// switch_back.php - A dedicated script to handle switching back to original role
// This is a simplified version that focuses only on the switch back functionality

// Force session to start fresh
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL); // Alternative way to set error reporting level

// Log function for debugging
function logDebug($message) {
    error_log("[SWITCH_BACK_DEBUG] " . $message);
}

logDebug("Script started. GET data: " . print_r($_GET, true));
logDebug("Current session: " . print_r($_SESSION, true));

// Check if we have original user data
if (!isset($_SESSION['original_user_id'])) {
    logDebug("No original user data found in session");
    header("Location: index.php?message=no_original_user");
    exit;
}

// Include auth.php for loadUserPermissions function
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Store temporary variables
$originalUserId = $_SESSION['original_user_id'];
$originalRoleId = $_SESSION['original_role_id'];
$originalUserRole = $_SESSION['original_user_role'];
$originalFullName = $_SESSION['original_full_name'];
$originalAvatarPath = $_SESSION['original_avatar_path'];

// Clear ALL switch-related session variables
unset($_SESSION['original_user_id']);
unset($_SESSION['original_role_id']);
unset($_SESSION['original_user_role']);
unset($_SESSION['original_full_name']);
unset($_SESSION['original_avatar_path']);
unset($_SESSION['switched_user_id']);
unset($_SESSION['switched_user_role']);
unset($_SESSION['switched_user_name']);

// Set the regular user session variables
$_SESSION['user_id'] = $originalUserId;
$_SESSION['role_id'] = $originalRoleId;
$_SESSION['user_role'] = $originalUserRole;
$_SESSION['FullName'] = $originalFullName;
$_SESSION['AvatarPath'] = $originalAvatarPath;

// Reload permissions for the original user
loadUserPermissions();

// Force session write
session_write_close();
session_start();

logDebug("Session after switch back: " . print_r($_SESSION, true));

// Tell browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect to profile page instead of index to break any potential redirect loop
header("Location: profile.php?message=switched_back&t=" . time());
exit;
