<?php
require_once __DIR__ . '/init.php';

echo "<h2>Testing Voting Logic - Computer Number Restrictions</h2>";

// Test data
$electionId = 13; // Use an existing election ID
$candidateId = 1; // Use an existing candidate ID
$computerNumber1 = '1111111111'; // First computer
$computerNumber2 = '2222222222'; // Second computer
$ipAddress = '127.0.0.1';
$userAgent = 'Test Browser';

$voteModel = new Vote();

echo "<h3>Test 1: First vote with computer number $computerNumber1</h3>";
try {
    $result1 = $voteModel->castVote($electionId, $candidateId, $computerNumber1, $ipAddress, $userAgent);
    if ($result1) {
        echo "<p>✓ First vote with computer $computerNumber1 was successful</p>";
    } else {
        echo "<p>⚠️ First vote with computer $computerNumber1 failed (might already exist)</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Error with first vote: " . $e->getMessage() . "</p>";
}

echo "<h3>Test 2: Second vote with SAME computer number $computerNumber1 (should fail)</h3>";
try {
    $result2 = $voteModel->castVote($electionId, $candidateId, $computerNumber1, $ipAddress, $userAgent);
    if ($result2) {
        echo "<p>✗ PROBLEM: Second vote with same computer $computerNumber1 was allowed (should be blocked)</p>";
    } else {
        echo "<p>✓ CORRECT: Second vote with same computer $computerNumber1 was blocked</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Error with second vote: " . $e->getMessage() . "</p>";
}

echo "<h3>Test 3: Vote with DIFFERENT computer number $computerNumber2 (should succeed)</h3>";
try {
    $result3 = $voteModel->castVote($electionId, $candidateId, $computerNumber2, $ipAddress, $userAgent);
    if ($result3) {
        echo "<p>✓ CORRECT: Vote with different computer $computerNumber2 was successful</p>";
    } else {
        echo "<p>⚠️ Vote with different computer $computerNumber2 failed (might already exist)</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Error with different computer vote: " . $e->getMessage() . "</p>";
}

echo "<h3>Test 4: Check database state</h3>";
try {
    $db = new Database();
    
    // Check votes for computer 1
    $db->query('SELECT COUNT(*) as count FROM votes v INNER JOIN voters vr ON v.voter_id = vr.id WHERE v.election_id = :election_id AND vr.voter_id = :computer_number');
    $db->bind(':election_id', $electionId);
    $db->bind(':computer_number', $computerNumber1);
    $result = $db->single();
    echo "<p>Computer $computerNumber1 has " . $result['count'] . " votes in election $electionId</p>";
    
    // Check votes for computer 2
    $db->query('SELECT COUNT(*) as count FROM votes v INNER JOIN voters vr ON v.voter_id = vr.id WHERE v.election_id = :election_id AND vr.voter_id = :computer_number');
    $db->bind(':election_id', $electionId);
    $db->bind(':computer_number', $computerNumber2);
    $result = $db->single();
    echo "<p>Computer $computerNumber2 has " . $result['count'] . " votes in election $electionId</p>";
    
    // Show all votes for this election
    $db->query('SELECT v.*, vr.voter_id as computer_number FROM votes v INNER JOIN voters vr ON v.voter_id = vr.id WHERE v.election_id = :election_id ORDER BY v.voted_at DESC LIMIT 10');
    $db->bind(':election_id', $electionId);
    $votes = $db->resultSet();
    
    echo "<h4>Recent votes in election $electionId:</h4>";
    if (!empty($votes)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Vote ID</th><th>Computer Number</th><th>Candidate ID</th><th>Voted At</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>" . $vote['id'] . "</td>";
            echo "<td>" . $vote['computer_number'] . "</td>";
            echo "<td>" . $vote['candidate_id'] . "</td>";
            echo "<td>" . $vote['voted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No votes found in election $electionId</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Error checking database: " . $e->getMessage() . "</p>";
}

echo "<h3>Summary</h3>";
echo "<p><strong>Expected Behavior:</strong></p>";
echo "<ul>";
echo "<li>✓ Different computer numbers should be able to vote</li>";
echo "<li>✓ Same computer number should be blocked from voting twice</li>";
echo "<li>✓ Each computer number gets exactly one vote per election</li>";
echo "</ul>";

?>
