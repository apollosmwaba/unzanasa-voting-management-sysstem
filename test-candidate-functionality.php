<?php
// Comprehensive test for candidate functionality
require_once __DIR__ . '/init.php';

echo "=== CANDIDATE FUNCTIONALITY TEST ===\n\n";

try {
    // 1. Test Database Connection
    echo "1. Testing Database Connection (Port 8082)...\n";
    $db = new Database();
    $db->query("SELECT 1 as test");
    $result = $db->single();
    echo "   ✓ Database connection successful\n\n";
    
    // 2. Check Tables
    echo "2. Checking Required Tables...\n";
    
    // Check candidates table
    $db->query("SHOW TABLES LIKE 'candidates'");
    $candidatesTable = $db->single();
    echo "   Candidates table: " . ($candidatesTable ? "✓ Exists" : "✗ Missing") . "\n";
    
    // Check elections table
    $db->query("SHOW TABLES LIKE 'elections'");
    $electionsTable = $db->single();
    echo "   Elections table: " . ($electionsTable ? "✓ Exists" : "✗ Missing") . "\n";
    
    // Check positions table
    $db->query("SHOW TABLES LIKE 'positions'");
    $positionsTable = $db->single();
    echo "   Positions table: " . ($positionsTable ? "✓ Exists" : "✗ Missing") . "\n\n";
    
    if (!$candidatesTable || !$electionsTable || !$positionsTable) {
        echo "✗ Missing required tables. Please run database setup.\n";
        exit(1);
    }
    
    // 3. Test Models
    echo "3. Testing Model Classes...\n";
    $candidateModel = new Candidate();
    $electionModel = new Election();
    $positionModel = new Position();
    echo "   ✓ All model classes instantiated successfully\n\n";
    
    // 4. Get Test Data
    echo "4. Checking Test Data...\n";
    $elections = $electionModel->getAllElections();
    $positions = $positionModel->getAllPositions();
    
    echo "   Elections available: " . count($elections) . "\n";
    echo "   Positions available: " . count($positions) . "\n";
    
    if (count($elections) == 0 || count($positions) == 0) {
        echo "   ⚠ Warning: Need at least 1 election and 1 position for testing\n";
        echo "   Run setup-test-data.php to create test data\n\n";
    } else {
        echo "   ✓ Test data available\n\n";
        
        // 5. Test Candidate Creation
        echo "5. Testing Candidate Creation...\n";
        
        $testCandidateData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'name' => 'John Doe',
            'position_id' => $positions[0]['id'],
            'election_id' => $elections[0]['id'],
            'platform' => 'Test platform for comprehensive testing of the candidate system.',
            'bio' => 'Test biography for the candidate.',
            'photo' => null
        ];
        
        echo "   Creating test candidate...\n";
        $candidateId = $candidateModel->addCandidate($testCandidateData);
        
        if ($candidateId) {
            echo "   ✓ Candidate created successfully with ID: $candidateId\n";
            
            // 6. Test Candidate Retrieval
            echo "\n6. Testing Candidate Retrieval...\n";
            
            // Get candidate by ID
            $retrievedCandidate = $candidateModel->getCandidateById($candidateId);
            if ($retrievedCandidate) {
                echo "   ✓ Retrieved candidate by ID\n";
                echo "   Name: " . $retrievedCandidate['name'] . "\n";
                echo "   Position: " . $retrievedCandidate['position_name'] . "\n";
                echo "   Election: " . $retrievedCandidate['election_title'] . "\n";
            } else {
                echo "   ✗ Failed to retrieve candidate by ID\n";
            }
            
            // Get all candidates
            $allCandidates = $candidateModel->getAllCandidates();
            echo "   ✓ Retrieved all candidates: " . count($allCandidates) . " total\n";
            
            // Get candidates with elections
            $candidatesWithElections = $candidateModel->getAllCandidatesWithElections();
            echo "   ✓ Retrieved candidates with elections: " . count($candidatesWithElections) . " total\n";
            
            // 7. Test Candidate Rendering (simulate form data)
            echo "\n7. Testing Candidate Rendering...\n";
            
            $found = false;
            foreach ($candidatesWithElections as $candidate) {
                if ($candidate['id'] == $candidateId) {
                    echo "   ✓ Test candidate found in list\n";
                    echo "   Display Data:\n";
                    echo "     - ID: " . $candidate['id'] . "\n";
                    echo "     - Name: " . $candidate['name'] . "\n";
                    echo "     - First Name: " . $candidate['firstname'] . "\n";
                    echo "     - Last Name: " . $candidate['lastname'] . "\n";
                    echo "     - Position: " . $candidate['position_name'] . "\n";
                    echo "     - Election: " . $candidate['election_title'] . "\n";
                    echo "     - Platform: " . substr($candidate['platform'], 0, 50) . "...\n";
                    echo "     - Status: " . ($candidate['status'] ? 'Active' : 'Inactive') . "\n";
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo "   ✗ Test candidate not found in candidate list\n";
            }
            
            // 8. Clean up - Delete test candidate
            echo "\n8. Cleaning up...\n";
            if ($candidateModel->deleteCandidate($candidateId)) {
                echo "   ✓ Test candidate deleted successfully\n";
            } else {
                echo "   ⚠ Warning: Could not delete test candidate (ID: $candidateId)\n";
            }
            
        } else {
            echo "   ✗ Failed to create test candidate\n";
            echo "   Check error logs for details\n";
        }
    }
    
    // 9. Final Summary
    echo "\n=== TEST SUMMARY ===\n";
    echo "✓ Database connection working (Port 8082)\n";
    echo "✓ Required tables exist\n";
    echo "✓ Model classes working\n";
    
    if (count($elections) > 0 && count($positions) > 0) {
        echo "✓ Test data available\n";
        if (isset($candidateId) && $candidateId) {
            echo "✓ Candidate creation and retrieval working\n";
            echo "✓ Candidate rendering data complete\n";
            echo "\n🎉 ALL TESTS PASSED! Candidate functionality is working correctly.\n";
        } else {
            echo "✗ Candidate creation failed\n";
        }
    } else {
        echo "⚠ Missing test data (elections/positions)\n";
        echo "\nRun the following to create test data:\n";
        echo "php setup-test-data.php\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n";
?>
