<?php
require_once __DIR__ . '/init.php';

try {
    $db = new Database();
    
    echo "Checking votes table structure...\n\n";
    
    // Check if table exists
    $db->query("SHOW TABLES LIKE 'votes'");
    $table = $db->single();
    
    if ($table) {
        echo "✓ Votes table exists\n\n";
        
        // Show table structure
        echo "Current table structure:\n";
        $db->query("DESCRIBE votes");
        $columns = $db->resultSet();
        
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ") " . 
                 ($column['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . 
                 ($column['Default'] ? " DEFAULT '" . $column['Default'] . "'" : '') . "\n";
        }
        
        // Test a simple insert to see what happens
        echo "\nTesting simple insert...\n";
        try {
            $db->query("INSERT INTO votes (election_id, candidate_id, computer_number, ip_address, user_agent) VALUES (1, 1, '9999999999', '127.0.0.1', 'test')");
            $result = $db->execute();
            echo "Insert test: " . ($result ? "✓ Success" : "✗ Failed") . "\n";
            
            if ($result) {
                // Clean up test record
                $db->query("DELETE FROM votes WHERE computer_number = '9999999999'");
                $db->execute();
                echo "✓ Test record cleaned up\n";
            }
        } catch (Exception $e) {
            echo "Insert test failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ Votes table does not exist!\n";
        
        // Create the votes table
        echo "Creating votes table...\n";
        $createSQL = "
            CREATE TABLE votes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                election_id INT NOT NULL,
                candidate_id INT NOT NULL,
                computer_number VARCHAR(20) NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
                FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
                UNIQUE KEY unique_vote (election_id, computer_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->query($createSQL);
        if ($db->execute()) {
            echo "✓ Votes table created successfully\n";
        } else {
            echo "✗ Failed to create votes table\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
