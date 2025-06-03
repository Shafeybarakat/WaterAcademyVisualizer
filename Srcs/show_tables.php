<?php
// Connect to the database
require_once "../includes/auth.php";   // For session and permission checks (optional for a debug script, but good practice)
require_once "../includes/config.php"; // Adjust path as needed

// Get list of all tables
$tablesResult = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tablesResult->fetch_row()) {
    $tables[] = $row[0];
}

echo "<h1>Database Structure</h1>";

// Show all tables and their columns
foreach ($tables as $table) {
    // Skip old_ tables
    if (strpos($table, 'old_') === 0) {
        continue;
    }
    
    echo "<h2>Table: $table</h2>";
    
    // Get columns
    $columnsResult = $conn->query("DESCRIBE `$table`");
    
    if (!$columnsResult) {
        echo "<p>Error retrieving structure for table $table</p>";
        continue;
    }
    
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($column = $columnsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>" . (is_null($column['Default']) ? 'NULL' : $column['Default']) . "</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show indexes for the table
    $indexesResult = $conn->query("SHOW INDEX FROM `$table`");
    
    if ($indexesResult && $indexesResult->num_rows > 0) {
        echo "<h3>Indexes for $table:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Key Name</th><th>Column</th><th>Unique</th><th>Type</th></tr>";
        
        while ($index = $indexesResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$index['Key_name']}</td>";
            echo "<td>{$index['Column_name']}</td>";
            echo "<td>" . ($index['Non_unique'] == 0 ? 'Yes' : 'No') . "</td>";
            echo "<td>{$index['Index_type']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Show sample data (first 5 rows)
    $dataResult = $conn->query("SELECT * FROM `$table` LIMIT 5");
    
    if ($dataResult && $dataResult->num_rows > 0) {
        echo "<h3>Sample Data (first 5 rows):</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 40px;'>";
        
        // Table header
        echo "<tr style='background-color: #f2f2f2;'>";
        $fields = $dataResult->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        // Reset data pointer
        $dataResult->data_seek(0);
        
        // Table data
        while ($row = $dataResult->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . (is_null($value) ? 'NULL' : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<hr>";
}

// Check for views
$viewsResult = $conn->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'");

if ($viewsResult && $viewsResult->num_rows > 0) {
    echo "<h1>Views</h1>";
    
    while ($viewRow = $viewsResult->fetch_row()) {
        $viewName = $viewRow[0];
        
        // Skip old_ views
        if (strpos($viewName, 'old_') === 0) {
            continue;
        }
        
        echo "<h2>View: $viewName</h2>";
        
        // Get view structure
        $columnsResult = $conn->query("DESCRIBE `$viewName`");
        
        if (!$columnsResult) {
            echo "<p>Error retrieving structure for view $viewName</p>";
            continue;
        }
        
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($column = $columnsResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>" . (is_null($column['Default']) ? 'NULL' : $column['Default']) . "</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Get view definition
        $viewDefResult = $conn->query("SHOW CREATE VIEW `$viewName`");
        
        if ($viewDefResult && $viewDefRow = $viewDefResult->fetch_assoc()) {
            echo "<h3>View Definition:</h3>";
            echo "<pre style='background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd; overflow: auto;'>";
            echo htmlspecialchars($viewDefRow['Create View']);
            echo "</pre>";
        }
        
        echo "<hr>";
    }
}
?>