<?php
require_once __DIR__ . '/init.php';

try {
    echo "Testing candidate creation with port 8082...\n";
    
    $candidateModel = new Candidate();
    $electionModel = new Election();
    $positionModel = new Position();
    
    // Get test data
    $elections = $electionModel->getAllElections();
    $positions = $positionModel->getAllPositions();
    
    echo "Elections: " . count($elections) . "\n";
    echo "Positions: " . count($positions) . "\n";
    
    if (count($elections) > 0 && count($positions) > 0) {
        // Test candidate creation
        $testData = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'name' => 'Test User',
            'position_id' => $positions[0]['id'],
            'election_id' => $elections[0]['id'],
            'platform' => 'Test platform',
            'bio' => 'Test bio'
        ];
        
        echo "Creating candidate...\n";
        $candidateId = $candidateModel->addCandidate($testData);
        
        if ($candidateId) {
            echo "✓ Candidate created with ID: $candidateId\n";
            
            // Test retrieval
            $candidate = $candidateModel->getCandidateById($candidateId);
            if ($candidate) {
                echo "✓ Candidate retrieved successfully\n";
                echo "  Name: " . $candidate['name'] . "\n";
                echo "  Position: " . ($candidate['position_name'] ?? 'N/A') . "\n";
                
                // Clean up
                if ($candidateModel->deleteCandidate($candidateId)) {
                    echo "✓ Test candidate deleted\n";
                }
            } else {
                echo "✗ Failed to retrieve candidate\n";
            }
        } else {
            echo "✗ Failed to create candidate\n";
        }
    } else {
        echo "Need elections and positions for testing\n";
    }
    
    echo "\n✓ Candidate functionality test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
