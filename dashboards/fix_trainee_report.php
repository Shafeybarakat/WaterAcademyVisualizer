<?php
// This script fixes the error in trainee_report.php
// Error: Unknown column 'gc_inner.CourseID' in 'WHERE' in /home/u652025084/domains/wa.shafey.net/public_html/wa/dashboards/trainee_report.php:57

// The issue is in the courses query where it's trying to use gc_inner.CourseID
// Let's examine the GradeComponents table structure to find the correct column name

// Connect to the database
require_once "../includes/config.php";
require_once "../includes/auth.php"; // Add auth.php

// RBAC guard: Only users with 'manage_system_settings' permission can access this page.
if (!require_permission('manage_system_settings', '../login.php')) {
    echo "Access denied. You must have system management permissions to access this page.";
    die(); // Terminate script
}

// Check if the GradeComponents table exists
$tableCheckQuery = "SHOW TABLES LIKE 'GradeComponents'";
$tableCheckResult = $conn->query($tableCheckQuery);

if ($tableCheckResult->num_rows > 0) {
    // Table exists, now check its columns
    $columnCheckQuery = "SHOW COLUMNS FROM GradeComponents";
    $columnCheckResult = $conn->query($columnCheckQuery);
    
    $columns = [];
    while ($row = $columnCheckResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    echo "GradeComponents table columns: " . implode(", ", $columns) . "\n";
    
    // Look for a column that might be the course ID
    $courseIdColumn = null;
    foreach ($columns as $column) {
        if (stripos($column, 'course') !== false) {
            $courseIdColumn = $column;
            break;
        }
    }
    
    if ($courseIdColumn) {
        echo "Found potential course ID column: $courseIdColumn\n";
        
        // Now let's fix the query in trainee_report.php
        $filePath = "trainee_report.php";
        $fileContent = file_get_contents($filePath);
        
        // Replace the problematic part of the query
        $oldQuery = "(SELECT COUNT(*) FROM GradeComponents gc_inner WHERE gc_inner.CourseID = c.CourseID) AS TotalComponents";
        $newQuery = "(SELECT COUNT(*) FROM GradeComponents gc_inner WHERE gc_inner.$courseIdColumn = c.CourseID) AS TotalComponents";
        
        $updatedContent = str_replace($oldQuery, $newQuery, $fileContent);
        
        // Save the updated file
        if (file_put_contents($filePath, $updatedContent)) {
            echo "Successfully updated trainee_report.php with the correct column name.\n";
        } else {
            echo "Failed to update trainee_report.php.\n";
        }
    } else {
        echo "Could not find a column related to course ID in the GradeComponents table.\n";
    }
} else {
    echo "GradeComponents table does not exist.\n";
}

echo "Script completed.\n";
?>
