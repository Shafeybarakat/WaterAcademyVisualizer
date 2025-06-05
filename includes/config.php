<?php
// --- Error Reporting and Logging Configuration ---
ini_set('display_errors', 'Off'); // IMPORTANT: Never show errors in production
ini_set('log_errors', 'On');     // Enable error logging
ini_set('error_reporting', E_ALL); // Report all errors, warnings, and notices

// Define custom log file path
define('DEBUG_LOG_FILE', __DIR__ . '/../debug.log'); // Path to debug.log in the root directory
ini_set('error_log', DEBUG_LOG_FILE); // Set the error log file

// Attempt to start the session if not already started.
// This should be one of the very first things your application does.

// Enable output buffering to prevent "headers already sent" errors
ob_start();

// Explicitly set session cookie path to ensure it's scoped correctly for the subdirectory
ini_set('session.cookie_path', '/wa/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debugging: Check if session is active after session_start()
if (session_status() === PHP_SESSION_ACTIVE) {
    error_log(date('[Y-m-d H:i:s]') . " DEBUG: Session started successfully in config.php. Session ID: " . session_id() . " (File: " . basename($_SERVER['PHP_SELF']) . ")", 3, DEBUG_LOG_FILE);
} else {
    error_log(date('[Y-m-d H:i:s]') . " DEBUG: Session NOT started in config.php. Session status: " . session_status() . " (File: " . basename($_SERVER['PHP_SELF']) . ")", 3, DEBUG_LOG_FILE);
}

// --- Database Configuration ---
// Ensure these details are 100% correct for your environment.
define("DB_HOST", "localhost");
define('DB_USER', 'u652025084_new_wa_user'); // Replace with your actual DB user
define('DB_PASS', 'Afb@1976');              // Replace with your actual DB password
define('DB_NAME', 'u652025084_new_wa_db');  // Replace with your actual DB name

// --- Establish Database Connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Connection Check ---
if ($conn->connect_error) {
    // Log detailed error to server's error log.
    // Avoid displaying detailed DB errors to the public in a production environment.
    error_log(date('[Y-m-d H:i:s]') . " Database Connection Failed: " . $conn->connect_errno . " - " . $conn->connect_error . " (File: " . basename($_SERVER['PHP_SELF']) . ")", 3, DEBUG_LOG_FILE);
    
    // For development, you might want to see the error directly, but disable for production.
    // die("Database Connection Failed. Please check server logs or contact support."); 
    
    // It's often better to let scripts that include this file check if $conn is valid,
    // rather than dying here, so they can handle the error more gracefully.
} else {
    // Set charset to utf8mb4 for broader character support.
    if (!$conn->set_charset("utf8mb4")) {
        error_log(date('[Y-m-d H:i:s]') . " Error loading character set utf8mb4: " . $conn->error . " (File: " . basename($_SERVER['PHP_SELF']) . ")", 3, DEBUG_LOG_FILE);
    }
    // Optionally, set collation for the connection if needed, though usually handled by table/db defaults.
    // if (!$conn->query("SET collation_connection = 'utf8mb4_unicode_ci'")) {
    //     error_log("Error setting collation_connection: " . $conn->error);
    // }
}

// Define BASE_ASSET_PATH for consistent asset linking
define('BASE_ASSET_PATH', '/wa/assets/');

// Define BASE_URL for consistent internal linking to PHP pages/APIs
define('BASE_URL', '/wa/');


// Note: $conn will be used by other scripts that include this file.
// The connection is typically closed in a global footer or at the end of script execution.
?>
