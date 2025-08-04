<?php
// Check database structure
require_once __DIR__ . '/init.php';

echo "<h2>Database Structure Check</h2>";

try {
    $db = new Database();
    
    // Check if tables exist
    $tables = ['elections', 'candidates', 'votes', 'positions', 'voters', 'admins'];
    
    echo "<h3>Table Existence Check:</h3>";
    foreach ($tables as $table) {
        try {
            $db->query("SHOW TABLES LIKE '$table'");
            $result = $db->single();
            if ($result) {
                echo "<p>✓ Table '$table' exists</p>";
                
                // Show table structure
                $db->query("DESCRIBE $table");
                $columns = $db->resultSet();
                echo "<details><summary>Show columns for $table</summary>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>" . $column['Field'] . "</td>";
                    echo "<td>" . $column['Type'] . "</td>";
                    echo "<td>" . $column['Null'] . "</td>";
                    echo "<td>" . $column['Key'] . "</td>";
                    echo "<td>" . $column['Default'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</details>";
            } else {
                echo "<p>❌ Table '$table' does not exist</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error checking table '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    // Check database name
    $db->query("SELECT DATABASE() as db_name");
    $result = $db->single();
    echo "<h3>Current Database:</h3>";
    echo "<p>Database: " . ($result['db_name'] ?? 'Unknown') . "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
