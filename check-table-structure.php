<?php
require_once __DIR__ . '/init.php';

echo "<h2>📋 Database Table Structure</h2>";

try {
    $db = new Database();
    
    echo "<h3>Admins Table Structure</h3>";
    $db->query('DESCRIBE admins');
    $adminColumns = $db->resultSet();
    
    if ($adminColumns) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($adminColumns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Could not get table structure</p>";
    }
    
    echo "<h3>Sample Admin Data</h3>";
    $db->query('SELECT * FROM admins LIMIT 3');
    $admins = $db->resultSet();
    
    if ($admins) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        $firstAdmin = reset($admins);
        echo "<tr>";
        foreach (array_keys($firstAdmin) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        foreach ($admins as $admin) {
            echo "<tr>";
            foreach ($admin as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Votes Table Structure</h3>";
    $db->query('DESCRIBE votes');
    $voteColumns = $db->resultSet();
    
    if ($voteColumns) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($voteColumns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
