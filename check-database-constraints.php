<?php
require_once __DIR__ . '/init.php';

echo "<h2>Database Constraints Analysis</h2>";

try {
    $db = new Database();
    
    echo "<h3>Votes Table Structure:</h3>";
    $db->query('DESCRIBE votes');
    $columns = $db->resultSet();
    
    if (!empty($columns)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
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
    
    echo "<h3>Votes Table Indexes and Constraints:</h3>";
    $db->query('SHOW INDEX FROM votes');
    $indexes = $db->resultSet();
    
    if (!empty($indexes)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Key Name</th><th>Column Name</th><th>Unique</th><th>Index Type</th></tr>";
        foreach ($indexes as $index) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($index['Key_name']) . "</td>";
            echo "<td>" . htmlspecialchars($index['Column_name']) . "</td>";
            echo "<td>" . ($index['Non_unique'] == 0 ? 'YES' : 'NO') . "</td>";
            echo "<td>" . htmlspecialchars($index['Index_type']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Current Votes Data:</h3>";
    $db->query('SELECT * FROM votes ORDER BY id DESC LIMIT 10');
    $votes = $db->resultSet();
    
    if (!empty($votes)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        foreach (array_keys($votes[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            foreach ($vote as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No votes in database</p>";
    }
    
    echo "<h3>Problematic Constraint Analysis:</h3>";
    echo "<p>The error shows: <code>Duplicate entry '14-' for key 'unique_vote_per_election'</code></p>";
    echo "<p>This suggests the constraint is trying to create a unique key from election_id and another field that's empty.</p>";
    
    // Check what combination might be causing this
    $db->query('SELECT election_id, voter_id, position_id, candidate_id, COUNT(*) as count 
                FROM votes 
                WHERE election_id = 14 
                GROUP BY election_id, voter_id, position_id, candidate_id 
                HAVING count > 1');
    $duplicates = $db->resultSet();
    
    if (!empty($duplicates)) {
        echo "<h4>Found Duplicate Combinations:</h4>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Election ID</th><th>Voter ID</th><th>Position ID</th><th>Candidate ID</th><th>Count</th></tr>";
        foreach ($duplicates as $dup) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($dup['election_id']) . "</td>";
            echo "<td>" . htmlspecialchars($dup['voter_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($dup['position_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($dup['candidate_id']) . "</td>";
            echo "<td>" . htmlspecialchars($dup['count']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No duplicate combinations found in existing data</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
