<?php
// Simple test for vote casting
require_once __DIR__ . '/init.php';

try {
    echo "Testing vote casting functionality...\n";
    
    $voteModel = new Vote();
    $electionModel = new Election();
    $candidateModel = new Candidate();
    
    // Get test data
    $elections = $electionModel->getAllElections();
    if (count($elections) == 0) {
        echo "No elections found. Please create an election first.\n";
        exit(1);
    }
    
    $election = $elections[0];
    $candidates = $candidateModel->getCandidatesByElection($election['id']);
    
    if (count($candidates) == 0) {
        echo "No candidates found for election. Please add candidates first.\n";
        exit(1);
    }
    
    $candidate = $candidates[0];
    $testComputerNumber = '1234567890';
    
    echo "Election: " . $election['title'] . "\n";
    echo "Candidate: " . $candidate['name'] . "\n";
    echo "Computer Number: " . $testComputerNumber . "\n\n";
    
    // Clean up any existing vote
    $db = new Database();
    $db->query('DELETE FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
    $db->bind(':election_id', $election['id']);
    $db->bind(':computer_number', $testComputerNumber);
    $db->execute();
    
    // Test vote casting
    echo "Casting vote...\n";
    $success = $voteModel->castVote(
        $election['id'],
        $candidate['id'],
        $testComputerNumber,
        '127.0.0.1',
        'Test User Agent'
    );
    
    if ($success) {
        echo "✓ Vote cast successfully!\n";
        
        // Verify vote was recorded
        $db->query('SELECT * FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
        $db->bind(':election_id', $election['id']);
        $db->bind(':computer_number', $testComputerNumber);
        $db->execute();
        $vote = $db->single();
        
        if ($vote) {
            echo "✓ Vote verified in database\n";
            echo "Vote details:\n";
            echo "  ID: " . $vote['id'] . "\n";
            echo "  Election ID: " . $vote['election_id'] . "\n";
            echo "  Candidate ID: " . $vote['candidate_id'] . "\n";
            echo "  Computer Number: " . $vote['computer_number'] . "\n";
            echo "  IP Address: " . $vote['ip_address'] . "\n";
            echo "  User Agent: " . substr($vote['user_agent'], 0, 50) . "...\n";
            echo "  Voted At: " . $vote['voted_at'] . "\n";
            
            // Test duplicate prevention
            echo "\nTesting duplicate vote prevention...\n";
            $duplicateSuccess = $voteModel->castVote(
                $election['id'],
                $candidate['id'],
                $testComputerNumber,
                '127.0.0.1',
                'Test User Agent'
            );
            
            if (!$duplicateSuccess) {
                echo "✓ Duplicate vote correctly prevented\n";
            } else {
                echo "✗ Duplicate vote was allowed (problem!)\n";
            }
            
            // Clean up
            $db->query('DELETE FROM votes WHERE id = :id');
            $db->bind(':id', $vote['id']);
            $db->execute();
            echo "✓ Test vote cleaned up\n";
            
        } else {
            echo "✗ Vote not found in database\n";
        }
    } else {
        echo "✗ Vote casting failed\n";
    }
    
    echo "\n✅ Vote casting functionality test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
