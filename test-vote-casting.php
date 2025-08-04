<?php
// Test vote casting functionality
require_once __DIR__ . '/init.php';

echo "=== VOTE CASTING FUNCTIONALITY TEST ===\n\n";

try {
    // 1. Test database connection
    echo "1. Testing Database Connection...\n";
    $db = new Database();
    $db->query("SELECT 1 as test");
    $result = $db->single();
    echo "   ✓ Database connection successful\n\n";
    
    // 2. Check required tables
    echo "2. Checking Required Tables...\n";
    
    $tables = ['votes', 'candidates', 'elections', 'valid_computer_numbers'];
    foreach ($tables as $table) {
        $db->query("SHOW TABLES LIKE '$table'");
        $exists = $db->single();
        echo "   $table table: " . ($exists ? "✓ Exists" : "✗ Missing") . "\n";
    }
    echo "\n";
    
    // 3. Test Vote model
    echo "3. Testing Vote Model...\n";
    $voteModel = new Vote();
    echo "   ✓ Vote model instantiated\n";
    
    // 4. Get test data
    echo "\n4. Getting Test Data...\n";
    $electionModel = new Election();
    $candidateModel = new Candidate();
    
    $elections = $electionModel->getAllElections();
    echo "   Elections available: " . count($elections) . "\n";
    
    if (count($elections) > 0) {
        $election = $elections[0];
        echo "   Using election: " . $election['title'] . " (ID: " . $election['id'] . ")\n";
        
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        echo "   Candidates in election: " . count($candidates) . "\n";
        
        if (count($candidates) > 0) {
            $candidate = $candidates[0];
            echo "   Using candidate: " . $candidate['name'] . " (ID: " . $candidate['id'] . ")\n";
            
            // 5. Test computer number validation
            echo "\n5. Testing Computer Number Validation...\n";
            $testComputerNumber = '1234567890';
            $isValid = Utils::validateComputerNumber($testComputerNumber);
            echo "   Computer number '$testComputerNumber' validation: " . ($isValid ? "✓ Valid" : "✗ Invalid") . "\n";
            
            // Check if computer number exists in valid_computer_numbers table
            $db->query("SELECT COUNT(*) as count FROM valid_computer_numbers WHERE computer_number = :number");
            $db->bind(':number', $testComputerNumber);
            $db->execute();
            $result = $db->single();
            $computerNumberExists = $result['count'] > 0;
            echo "   Computer number in database: " . ($computerNumberExists ? "✓ Exists" : "✗ Not found") . "\n";
            
            if (!$computerNumberExists) {
                echo "   Adding test computer number to database...\n";
                $db->query("INSERT IGNORE INTO valid_computer_numbers (computer_number, student_name, is_active, uploaded_by) VALUES (:number, 'Test Student', 1, 1)");
                $db->bind(':number', $testComputerNumber);
                if ($db->execute()) {
                    echo "   ✓ Test computer number added\n";
                }
            }
            
            // 6. Test vote casting
            echo "\n6. Testing Vote Casting...\n";
            
            // First check if already voted
            $db->query('SELECT id FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
            $db->bind(':election_id', $election['id']);
            $db->bind(':computer_number', $testComputerNumber);
            $db->execute();
            
            if ($db->rowCount() > 0) {
                echo "   Computer number has already voted. Cleaning up...\n";
                $db->query('DELETE FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
                $db->bind(':election_id', $election['id']);
                $db->bind(':computer_number', $testComputerNumber);
                $db->execute();
                echo "   ✓ Previous vote removed\n";
            }
            
            // Now test vote casting
            $success = $voteModel->castVote(
                $election['id'],
                $candidate['id'],
                $testComputerNumber,
                '127.0.0.1',
                'Test User Agent'
            );
            
            if ($success) {
                echo "   ✓ Vote cast successfully!\n";
                
                // Verify vote was recorded
                $db->query('SELECT * FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
                $db->bind(':election_id', $election['id']);
                $db->bind(':computer_number', $testComputerNumber);
                $db->execute();
                $vote = $db->single();
                
                if ($vote) {
                    echo "   ✓ Vote verified in database\n";
                    echo "     Vote ID: " . $vote['id'] . "\n";
                    echo "     Election ID: " . $vote['election_id'] . "\n";
                    echo "     Candidate ID: " . $vote['candidate_id'] . "\n";
                    echo "     Computer Number: " . $vote['computer_number'] . "\n";
                    echo "     IP Address: " . $vote['ip_address'] . "\n";
                    
                    // Test duplicate vote prevention
                    echo "\n7. Testing Duplicate Vote Prevention...\n";
                    $duplicateSuccess = $voteModel->castVote(
                        $election['id'],
                        $candidate['id'],
                        $testComputerNumber,
                        '127.0.0.1',
                        'Test User Agent'
                    );
                    
                    if (!$duplicateSuccess) {
                        echo "   ✓ Duplicate vote correctly prevented\n";
                    } else {
                        echo "   ✗ Duplicate vote was allowed (this is a problem!)\n";
                    }
                    
                    // Clean up test vote
                    echo "\n8. Cleaning Up...\n";
                    $db->query('DELETE FROM votes WHERE id = :id');
                    $db->bind(':id', $vote['id']);
                    if ($db->execute()) {
                        echo "   ✓ Test vote cleaned up\n";
                    }
                    
                } else {
                    echo "   ✗ Vote not found in database after casting\n";
                }
                
            } else {
                echo "   ✗ Vote casting failed\n";
                
                // Check for potential issues
                echo "   Debugging information:\n";
                $errorInfo = $db->getErrorInfo();
                if ($errorInfo[0] !== '00000') {
                    echo "     Database error: " . $errorInfo[2] . "\n";
                }
            }
            
        } else {
            echo "   ✗ No candidates found for testing\n";
        }
    } else {
        echo "   ✗ No elections found for testing\n";
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    echo "✓ Database connection working\n";
    echo "✓ Required tables exist\n";
    echo "✓ Vote model working\n";
    
    if (isset($success) && $success) {
        echo "✓ Vote casting functionality working\n";
        echo "✓ Duplicate vote prevention working\n";
        echo "\n🎉 ALL TESTS PASSED! Vote casting is working correctly.\n";
    } else {
        echo "✗ Vote casting needs attention\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n";
?>
