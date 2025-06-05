<?php
include_once "../includes/config.php";
include_once "../includes/auth.php"; // Add auth.php

// RBAC guard: Only users with 'view_users' permission can access this page.
if (!hasPermission('view_users')) {
    // For AJAX requests, return an empty response or a specific error message
    echo '<option value="">Access Denied</option>'; // Or return JSON error
    die(); 
}

// Get all active instructors
$instructorsQuery = "SELECT UserID, FirstName, LastName FROM Users 
                     WHERE RoleID = (SELECT RoleID FROM Roles WHERE RoleName = 'Instructor') AND IsActive = 1
                     ORDER BY LastName, FirstName";
$instructors = $conn->query($instructorsQuery);

// Generate HTML for instructor dropdown
echo '<option value="">Select Instructor</option>';

if ($instructors && $instructors->num_rows > 0) {
    while ($instructor = $instructors->fetch_assoc()) {
        echo '<option value="' . $instructor['UserID'] . '">' . 
             htmlspecialchars($instructor['FirstName'] . ' ' . $instructor['LastName']) . 
             '</option>';
    }
}
?>
