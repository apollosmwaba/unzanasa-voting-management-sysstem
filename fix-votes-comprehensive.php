<?php
// Comprehensive fix for votes table
require_once __DIR__ . '/init.php';

try {
    $db = new Database();
    
    echo "=== COMPREHENSIVE VOTES TABLE FIX ===\n\n";
    
    // 1. Check current table structure
    echo "1. Checking current votes table structure...\n";
    $db->query("DESCRIBE votes");
    $columns = $db->resultSet();
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // 2. Check for foreign key constraints
    echo "\n2. Checking foreign key constraints...\n";
    $db->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'votes' AND TABLE_SCHEMA = 'unzanasa_voting' 
                AND REFERENCED_TABLE_NAME IS NOT NULL");
    $constraints = $db->resultSet();
    
    if (count($constraints) > 0) {
        echo "Existing foreign key constraints:\n";
        foreach ($constraints as $constraint) {
            echo "- " . $constraint['CONSTRAINT_NAME'] . ": " . $constraint['COLUMN_NAME'] . 
                 " -> " . $constraint['REFERENCED_TABLE_NAME'] . "." . $constraint['REFERENCED_COLUMN_NAME'] . "\n";
        }
    } else {
        echo "No foreign key constraints found.\n";
    }
    
    // 3. Test simple insert without foreign keys
    echo "\n3. Testing simple insert without foreign key validation...\n";
    
    // First, let's try to insert a test record
    $testElectionId = 1;
    $testCandidateId = 1;
    $testComputerNumber = '9999999999';
    
    // Check if test election exists
    $db->query("SELECT id FROM elections LIMIT 1");
    $election = $db->single();
    if ($election) {
        $testElectionId = $election['id'];
        echo "Using existing election ID: $testElectionId\n";
    } else {
        echo "No elections found. Creating test election...\n";
        $db->query("INSERT INTO elections (title, description, start_date, end_date, status) VALUES ('Test Election', 'Test', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active')");
        if ($db->execute()) {
            $testElectionId = $db->lastInsertId();
            echo "Created test election with ID: $testElectionId\n";
        }
    }
    
    // Check if test candidate exists
    $db->query("SELECT id FROM candidates LIMIT 1");
    $candidate = $db->single();
    if ($candidate) {
        $testCandidateId = $candidate['id'];
        echo "Using existing candidate ID: $testCandidateId\n";
    } else {
        echo "No candidates found. This might be the issue.\n";
        echo "Please ensure you have candidates in the database.\n";
    }
    
    // Try the insert
    echo "\nAttempting vote insert...\n";
    try {
        // Clean up any existing test vote first
        $db->query("DELETE FROM votes WHERE computer_number = :computer_number");
        $db->bind(':computer_number', $testComputerNumber);
        $db->execute();
        
        // Now try to insert
        $db->query("INSERT INTO votes (election_id, candidate_id, computer_number, ip_address, user_agent) 
                    VALUES (:election_id, :candidate_id, :computer_number, :ip_address, :user_agent)");
        $db->bind(':election_id', $testElectionId);
        $db->bind(':candidate_id', $testCandidateId);
        $db->bind(':computer_number', $testComputerNumber);
        $db->bind(':ip_address', '127.0.0.1');
        $db->bind(':user_agent', 'Test User Agent');
        
        if ($db->execute()) {
            echo "✓ Vote insert successful!\n";
            
            // Verify the insert
            $db->query("SELECT * FROM votes WHERE computer_number = :computer_number");
            $db->bind(':computer_number', $testComputerNumber);
            $vote = $db->single();
            
            if ($vote) {
                echo "✓ Vote verified in database\n";
                echo "Vote details:\n";
                foreach ($vote as $key => $value) {
                    echo "  $key: $value\n";
                }
                
                // Clean up
                $db->query("DELETE FROM votes WHERE id = :id");
                $db->bind(':id', $vote['id']);
                $db->execute();
                echo "✓ Test vote cleaned up\n";
            }
        } else {
            echo "✗ Vote insert failed\n";
            $errorInfo = $db->getErrorInfo();
            echo "Error: " . $errorInfo[2] . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Exception during vote insert: " . $e->getMessage() . "\n";
        
        // Check if it's a foreign key constraint error
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            echo "\nThis appears to be a foreign key constraint issue.\n";
            echo "Let's check if the referenced records exist...\n";
            
            // Check election
            $db->query("SELECT COUNT(*) as count FROM elections WHERE id = :id");
            $db->bind(':id', $testElectionId);
            $electionExists = $db->single()['count'] > 0;
            echo "Election ID $testElectionId exists: " . ($electionExists ? "Yes" : "No") . "\n";
            
            // Check candidate
            $db->query("SELECT COUNT(*) as count FROM candidates WHERE id = :id");
            $db->bind(':id', $testCandidateId);
            $candidateExists = $db->single()['count'] > 0;
            echo "Candidate ID $testCandidateId exists: " . ($candidateExists ? "Yes" : "No") . "\n";
        }
    }
    
    echo "\n=== FIX SUMMARY ===\n";
    echo "The votes table structure appears to be correct.\n";
    echo "If vote casting is still failing, the issue is likely:\n";
    echo "1. Missing election or candidate records\n";
    echo "2. Foreign key constraint issues\n";
    echo "3. Invalid data being passed to the castVote method\n";
    echo "\nPlease ensure you have:\n";
    echo "- At least one active election\n";
    echo "- At least one candidate for that election\n";
    echo "- Valid computer numbers in the valid_computer_numbers table\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
