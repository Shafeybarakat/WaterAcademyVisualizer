<?php
// Attempt to start the session if not already started.
// This should be one of the very first things your application does.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    error_log("Database Connection Failed: " . $conn->connect_errno . " - " . $conn->connect_error);
    
    // For development, you might want to see the error directly, but disable for production.
    // die("Database Connection Failed. Please check server logs or contact support."); 
    
    // It's often better to let scripts that include this file check if $conn is valid,
    // rather than dying here, so they can handle the error more gracefully.
} else {
    // Set charset to utf8mb4 for broader character support.
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error loading character set utf8mb4: " . $conn->error);
    }
    // Optionally, set collation for the connection if needed, though usually handled by table/db defaults.
    // if (!$conn->query("SET collation_connection = 'utf8mb4_unicode_ci'")) {
    //     error_log("Error setting collation_connection: " . $conn->error);
    // }
}

// Define BASE_ASSET_PATH for consistent asset linking
define('BASE_ASSET_PATH', '/assets/');

// Note: $conn will be used by other scripts that include this file.
// The connection is typically closed in a global footer or at the end of script execution.
?>
