<?php
// check_tables.php - Script to check if the required tables exist and have the expected structure
require_once "../includes/config.php";
require_once "../includes/auth.php";

// Check if user is logged in and has appropriate permission
if (!isLoggedIn() || !hasPermission('manage_system_settings')) {
    echo "Access denied. You must have system management permissions to access this page.";
    exit;
}

echo "<h1>Database Table Check</h1>";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to get table structure
function getTableStructure($conn, $tableName) {
    $result = $conn->query("DESCRIBE $tableName");
    $structure = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $structure[] = $row;
        }
    }
    return $structure;
}

// Function to get sample data from a table
function getSampleData($conn, $tableName, $limit = 5) {
    $result = $conn->query("SELECT * FROM $tableName LIMIT $limit");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Tables to check
$tables = [
    'Users',
    'Instructors',
    'Courses',
    'Groups',
    'GroupCourses',
    'Enrollments',
    'vw_Instructors', // This is a view
    'View_TraineePerformanceDetails' // Check this view
];

// Check each table
foreach ($tables as $table) {
    echo "<h2>Table: $table</h2>";
    
    if (tableExists($conn, $table)) {
        echo "<p style='color: green;'>✅ Table exists</p>";
        
        // Get table structure
        $structure = getTableStructure($conn, $table);
        echo "<h3>Structure:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($structure as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Get sample data
        $data = getSampleData($conn, $table);
        echo "<h3>Sample Data:</h3>";
        if (count($data) > 0) {
            echo "<table border='1' cellpadding='5'>";
            // Table header
            echo "<tr>";
            foreach (array_keys($data[0]) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            
            // Table data
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data found in table.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Table does not exist</p>";
    }
    
    echo "<hr>";
}

// Check specific relationships for the instructor courses issue
echo "<h2>Checking GroupCourses for Instructor Assignments</h2>";

// Check if there are any courses assigned to instructors
$query = "
    SELECT 
        i.InstructorID,
        CONCAT(u.FirstName, ' ', u.LastName) as InstructorName,
        COUNT(gc.ID) as AssignedCourses
    FROM 
        Instructors i
        LEFT JOIN Users u ON i.UserID = u.UserID
        LEFT JOIN GroupCourses gc ON i.InstructorID = gc.InstructorID
    GROUP BY 
        i.InstructorID, InstructorName
    ORDER BY 
        AssignedCourses DESC
";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Instructor ID</th><th>Instructor Name</th><th>Assigned Courses</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['InstructorID']}</td>";
        echo "<td>{$row['InstructorName']}</td>";
        echo "<td>{$row['AssignedCourses']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No instructor course assignments found.</p>";
}

// Close connection
$conn->close();
?>
