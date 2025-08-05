<?php
require_once __DIR__ . '/init.php';

echo "<h2>Debug View Results Page</h2>";

try {
    // Test Election model
    echo "<h3>Testing Election Model</h3>";
    $electionModel = new Election();
    $elections = $electionModel->getAllElections();
    echo "<p>✅ Election model works. Found " . count($elections) . " elections.</p>";
    
    if (!empty($elections)) {
        $selectedElection = $elections[0];
        echo "<p>Testing with election: " . htmlspecialchars($selectedElection['title']) . " (ID: {$selectedElection['id']})</p>";
        
        // Test getting election by ID
        $election = $electionModel->getElectionById($selectedElection['id']);
        if ($election) {
            echo "<p>✅ getElectionById works</p>";
        } else {
            echo "<p>❌ getElectionById failed</p>";
        }
        
        // Test Candidate model
        echo "<h3>Testing Candidate Model</h3>";
        try {
            $candidateModel = new Candidate();
            echo "<p>✅ Candidate model instantiated</p>";
            
            $candidates = $candidateModel->getCandidatesByElection($selectedElection['id']);
            echo "<p>✅ getCandidatesByElection works. Found " . count($candidates) . " candidates.</p>";
            
            if (!empty($candidates)) {
                echo "<h4>Candidates:</h4>";
                echo "<ul>";
                foreach ($candidates as $candidate) {
                    echo "<li>" . htmlspecialchars($candidate['name']) . " (ID: {$candidate['id']})</li>";
                }
                echo "</ul>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Candidate model error: " . $e->getMessage() . "</p>";
        }
        
        // Test vote counts
        echo "<h3>Testing Vote Counts</h3>";
        $db = new Database();
        $db->query('SELECT candidate_id, COUNT(*) as vote_count FROM votes WHERE election_id = :election_id GROUP BY candidate_id');
        $db->bind(':election_id', $selectedElection['id']);
        $voteCounts = $db->resultSet();
        echo "<p>Vote counts query returned " . count($voteCounts) . " results</p>";
        
        if (!empty($voteCounts)) {
            echo "<ul>";
            foreach ($voteCounts as $vc) {
                echo "<li>Candidate {$vc['candidate_id']}: {$vc['vote_count']} votes</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p>❌ No elections found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "<h3>Class Check</h3>";
echo "<p>Election class exists: " . (class_exists('Election') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>Candidate class exists: " . (class_exists('Candidate') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>Database class exists: " . (class_exists('Database') ? '✅ Yes' : '❌ No') . "</p>";

?>
