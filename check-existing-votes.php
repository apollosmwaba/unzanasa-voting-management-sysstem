<?php
require_once __DIR__ . '/init.php';

echo "<h2>Checking Existing Votes</h2>";

try {
    $db = new Database();
    
    echo "<h3>All Votes in Database:</h3>";
    $db->query('SELECT * FROM votes ORDER BY id DESC');
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
            foreach ($vote as $key => $value) {
                if ($key === 'computer_number' && empty($value)) {
                    echo "<td style='background-color: #ffcccc;'><strong>EMPTY</strong></td>";
                } else {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for votes with empty computer_number
        $db->query('SELECT COUNT(*) as count FROM votes WHERE computer_number = "" OR computer_number IS NULL');
        $emptyCount = $db->single();
        echo "<p><strong>Votes with empty computer_number:</strong> " . $emptyCount['count'] . "</p>";
        
        // Check for votes in election 14
        $db->query('SELECT * FROM votes WHERE election_id = 14');
        $election14Votes = $db->resultSet();
        echo "<h4>Votes in Election 14:</h4>";
        if (!empty($election14Votes)) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Computer Number</th><th>Voter ID</th><th>Candidate ID</th><th>Voted At</th></tr>";
            foreach ($election14Votes as $vote) {
                echo "<tr>";
                echo "<td>" . $vote['id'] . "</td>";
                echo "<td style='" . (empty($vote['computer_number']) ? 'background-color: #ffcccc;' : '') . "'>" . 
                     htmlspecialchars($vote['computer_number'] ?: 'EMPTY') . "</td>";
                echo "<td>" . $vote['voter_id'] . "</td>";
                echo "<td>" . $vote['candidate_id'] . "</td>";
                echo "<td>" . $vote['voted_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No votes found in election 14</p>";
        }
        
    } else {
        echo "<p>No votes found in database</p>";
    }
    
    echo "<h3>Solution Options:</h3>";
    echo "<ol>";
    echo "<li><strong>Option 1:</strong> Delete existing votes with empty computer_number</li>";
    echo "<li><strong>Option 2:</strong> Update existing votes to have proper computer_number values</li>";
    echo "<li><strong>Option 3:</strong> Change the unique constraint to use voter_id instead of computer_number</li>";
    echo "</ol>";
    
    echo "<h3>Recommended Action:</h3>";
    echo "<p>Since there are existing votes with empty computer_number, we should clean them up first.</p>";
    
    if (isset($_GET['cleanup'])) {
        echo "<h4>Cleaning up empty computer_number votes...</h4>";
        
        // Update existing votes to have computer_number from voters table
        $db->query('UPDATE votes v 
                    INNER JOIN voters vr ON v.voter_id = vr.id 
                    SET v.computer_number = vr.voter_id 
                    WHERE v.computer_number = "" OR v.computer_number IS NULL');
        
        if ($db->execute()) {
            echo "<p>✅ Updated existing votes with proper computer numbers</p>";
        } else {
            echo "<p>❌ Failed to update existing votes</p>";
        }
    } else {
        echo "<p><a href='?cleanup=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Clean Up Empty Computer Numbers</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
