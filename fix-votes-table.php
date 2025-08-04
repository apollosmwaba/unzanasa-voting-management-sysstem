<?php
// Fix the votes table by adding the missing computer_number column
require_once __DIR__ . '/init.php';

try {
    $db = new Database();
    
    echo "Checking votes table structure...\n";
    
    // Check if the computer_number column exists
    $db->query("SHOW COLUMNS FROM votes LIKE 'computer_number'");
    $result = $db->single();
    
    if (!$result) {
        echo "Adding computer_number column to votes table...\n";
        
        // Add the computer_number column
        $db->query("ALTER TABLE votes ADD COLUMN computer_number VARCHAR(20) NOT NULL DEFAULT '' AFTER candidate_id");
        
        if ($db->execute()) {
            echo "Successfully added computer_number column.\n";
            
            // Try to add unique constraint (may fail if there are duplicate entries)
            try {
                echo "Adding unique constraint...\n";
                $db->query("ALTER TABLE votes ADD CONSTRAINT unique_vote_per_election UNIQUE (election_id, computer_number)");
                
                if ($db->execute()) {
                    echo "Successfully added unique constraint.\n";
                } else {
                    echo "Warning: Could not add unique constraint. There may be duplicate entries.\n";
                }
            } catch (Exception $e) {
                echo "Warning: Could not add unique constraint: " . $e->getMessage() . "\n";
            }
            
        } else {
            throw new Exception("Failed to add computer_number column.");
        }
        
    } else {
        echo "computer_number column already exists.\n";
    }
    
    // Show the current table structure
    echo "\nCurrent votes table structure:\n";
    $db->query("DESCRIBE votes");
    $columns = $db->resultSet();
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\nVotes table fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
