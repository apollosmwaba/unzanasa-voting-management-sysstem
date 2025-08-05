<?php
require_once __DIR__ . '/init.php';

echo "<h2>Debug Vote Casting Process</h2>";

// Get real data
$db = new Database();
$db->query('SELECT * FROM elections ORDER BY id DESC LIMIT 1');
$election = $db->single();

$db->query('SELECT * FROM candidates WHERE election_id = :election_id LIMIT 1');
$db->bind(':election_id', $election['id']);
$candidate = $db->single();

if (!$election || !$candidate) {
    echo "<p>❌ No election or candidate data found</p>";
    exit;
}

echo "<p><strong>Election:</strong> " . htmlspecialchars($election['title']) . " (ID: {$election['id']})</p>";
echo "<p><strong>Candidate:</strong> " . htmlspecialchars($candidate['name']) . " (ID: {$candidate['id']})</p>";

$computerNumber = '9999999999'; // Use a unique number
echo "<p><strong>Test Computer:</strong> $computerNumber</p>";

// Step-by-step debugging
echo "<h3>Step-by-Step Vote Casting Debug:</h3>";

try {
    $voteModel = new Vote();
    $db = new Database();
    
    echo "<h4>Step 1: Check if computer has already voted</h4>";
    $db->query('SELECT COUNT(*) as count FROM votes v INNER JOIN voters vr ON v.voter_id = vr.id WHERE v.election_id = :election_id AND vr.voter_id = :computer_number');
    $db->bind(':election_id', $election['id']);
    $db->bind(':computer_number', $computerNumber);
    $result = $db->single();
    echo "<p>Existing votes for computer $computerNumber: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        echo "<p>❌ Computer has already voted - this is why it's failing</p>";
    } else {
        echo "<p>✅ Computer hasn't voted yet - continuing...</p>";
        
        echo "<h4>Step 2: Check if voter record exists</h4>";
        $db->query('SELECT id FROM voters WHERE voter_id = :computer_number');
        $db->bind(':computer_number', $computerNumber);
        $existingVoter = $db->single();
        
        if ($existingVoter) {
            echo "<p>✅ Voter record exists with ID: " . $existingVoter['id'] . "</p>";
        } else {
            echo "<p>⚠️ Voter record doesn't exist - will be created</p>";
            
            // Try to create voter record
            echo "<h4>Step 2a: Creating voter record</h4>";
            $db->query('INSERT INTO voters (voter_id, firstname, lastname, password, status) VALUES (:voter_id, :firstname, :lastname, :password, 1)');
            $db->bind(':voter_id', $computerNumber);
            $db->bind(':firstname', 'Student');
            $db->bind(':lastname', substr($computerNumber, -4));
            $db->bind(':password', password_hash($computerNumber, PASSWORD_DEFAULT));
            
            if ($db->execute()) {
                $voterId = $db->lastInsertId();
                echo "<p>✅ Voter record created with ID: $voterId</p>";
            } else {
                echo "<p>❌ Failed to create voter record</p>";
                $errorInfo = $db->getErrorInfo();
                echo "<p>Database error: " . print_r($errorInfo, true) . "</p>";
            }
        }
        
        echo "<h4>Step 3: Check candidate exists</h4>";
        $db->query('SELECT position_id FROM candidates WHERE id = :id');
        $db->bind(':id', $candidate['id']);
        $candidateCheck = $db->single();
        
        if ($candidateCheck) {
            echo "<p>✅ Candidate exists with position_id: " . $candidateCheck['position_id'] . "</p>";
            
            echo "<h4>Step 4: Try to insert vote</h4>";
            
            // Get voter ID again
            $db->query('SELECT id FROM voters WHERE voter_id = :computer_number');
            $db->bind(':computer_number', $computerNumber);
            $voterRecord = $db->single();
            
            if ($voterRecord) {
                $voterId = $voterRecord['id'];
                echo "<p>Using voter ID: $voterId</p>";
                
                $db->query('INSERT INTO votes (voter_id, election_id, position_id, candidate_id, voted_at) VALUES (:voter_id, :election_id, :position_id, :candidate_id, NOW())');
                $db->bind(':voter_id', $voterId);
                $db->bind(':election_id', $election['id']);
                $db->bind(':position_id', $candidateCheck['position_id']);
                $db->bind(':candidate_id', $candidate['id']);
                
                if ($db->execute()) {
                    echo "<p>✅ Vote inserted successfully!</p>";
                } else {
                    echo "<p>❌ Failed to insert vote</p>";
                    $errorInfo = $db->getErrorInfo();
                    echo "<p>Database error: " . print_r($errorInfo, true) . "</p>";
                }
            } else {
                echo "<p>❌ Could not find voter record after creation</p>";
            }
        } else {
            echo "<p>❌ Candidate not found</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "<h3>Final Test: Use Vote Model</h3>";
try {
    $testComputer = '8888888888';
    $result = $voteModel->castVote($election['id'], $candidate['id'], $testComputer, '127.0.0.1', 'Test Browser');
    if ($result) {
        echo "<p>✅ Vote model worked for computer $testComputer</p>";
    } else {
        echo "<p>❌ Vote model failed for computer $testComputer</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Vote model exception: " . $e->getMessage() . "</p>";
}

?>
