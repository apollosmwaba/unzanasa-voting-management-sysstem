<?php
require_once __DIR__ . '/init.php';

echo "<h2>Checking Voting Data</h2>";

try {
    $db = new Database();
    
    echo "<h3>Available Elections:</h3>";
    $db->query('SELECT * FROM elections ORDER BY id DESC LIMIT 5');
    $elections = $db->resultSet();
    
    if (!empty($elections)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>";
        foreach ($elections as $election) {
            echo "<tr>";
            echo "<td>" . $election['id'] . "</td>";
            echo "<td>" . htmlspecialchars($election['title']) . "</td>";
            echo "<td>" . $election['status'] . "</td>";
            echo "<td>" . $election['start_date'] . "</td>";
            echo "<td>" . $election['end_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No elections found</p>";
    }
    
    echo "<h3>Available Candidates:</h3>";
    $db->query('SELECT c.*, e.title as election_title, p.name as position_name FROM candidates c 
                LEFT JOIN elections e ON c.election_id = e.id 
                LEFT JOIN positions p ON c.position_id = p.id 
                ORDER BY c.id DESC LIMIT 10');
    $candidates = $db->resultSet();
    
    if (!empty($candidates)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Election</th><th>Position</th><th>Status</th></tr>";
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['id'] . "</td>";
            echo "<td>" . htmlspecialchars($candidate['name']) . "</td>";
            echo "<td>" . htmlspecialchars($candidate['election_title']) . "</td>";
            echo "<td>" . htmlspecialchars($candidate['position_name']) . "</td>";
            echo "<td>" . ($candidate['status'] ? 'Active' : 'Inactive') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No candidates found</p>";
    }
    
    echo "<h3>Recent Votes (All Elections):</h3>";
    $db->query('SELECT v.*, vr.voter_id as computer_number, c.name as candidate_name, e.title as election_title 
                FROM votes v 
                INNER JOIN voters vr ON v.voter_id = vr.id 
                LEFT JOIN candidates c ON v.candidate_id = c.id 
                LEFT JOIN elections e ON v.election_id = e.id 
                ORDER BY v.voted_at DESC LIMIT 10');
    $votes = $db->resultSet();
    
    if (!empty($votes)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Vote ID</th><th>Computer Number</th><th>Candidate</th><th>Election</th><th>Voted At</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['id'] . "</td>";
            echo "<td>" . $vote['computer_number'] . "</td>";
            echo "<td>" . htmlspecialchars($vote['candidate_name']) . "</td>";
            echo "<td>" . htmlspecialchars($vote['election_title']) . "</td>";
            echo "<td>" . $vote['voted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No votes found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}
?>
