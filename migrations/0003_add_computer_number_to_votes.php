<?php
// Add computer_number column to votes table
$migration = new class {
    public function up($db) {
        // First check if the column already exists
        $db->query("SHOW COLUMNS FROM votes LIKE 'computer_number'");
        $result = $db->single();
        
        if (!$result) {
            // Add the computer_number column
            $query = "ALTER TABLE votes ADD COLUMN computer_number VARCHAR(20) NOT NULL AFTER candidate_id";
            $db->query($query);
            
            if (!$db->execute()) {
                return false;
            }
            
            // Add unique constraint for election_id and computer_number
            $constraintQuery = "ALTER TABLE votes ADD CONSTRAINT unique_vote_per_election UNIQUE (election_id, computer_number)";
            $db->query($constraintQuery);
            
            return $db->execute();
        }
        
        return true; // Column already exists
    }
    
    public function down($db) {
        // Remove the unique constraint first
        $db->query("ALTER TABLE votes DROP INDEX IF EXISTS unique_vote_per_election");
        $db->execute();
        
        // Remove the computer_number column
        $query = "ALTER TABLE votes DROP COLUMN IF EXISTS computer_number";
        $db->query($query);
        return $db->execute();
    }
};

return $migration;
