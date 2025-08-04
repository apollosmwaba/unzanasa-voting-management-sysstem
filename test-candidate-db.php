<?php
// Test candidate database operations
require_once __DIR__ . '/init.php';

try {
    echo "Testing database connection and candidate operations...\n\n";
    
    // Test database connection
    $db = new Database();
    echo "✓ Database connection successful\n";
    
    // Check if candidates table exists
    $db->query("SHOW TABLES LIKE 'candidates'");
    $result = $db->single();
    
    if ($result) {
        echo "✓ Candidates table exists\n";
        
        // Show table structure
        echo "\nCandidates table structure:\n";
        $db->query("DESCRIBE candidates");
        $columns = $db->resultSet();
        
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        
        // Test candidate model
        echo "\nTesting Candidate model...\n";
        $candidateModel = new Candidate();
        
        // Get all candidates
        $candidates = $candidateModel->getAllCandidates();
        echo "✓ getAllCandidates() returned " . count($candidates) . " candidates\n";
        
        // Test elections and positions
        $electionModel = new Election();
        $positionModel = new Position();
        
        $elections = $electionModel->getAllElections();
        $positions = $positionModel->getAllPositions();
        
        echo "✓ Found " . count($elections) . " elections\n";
        echo "✓ Found " . count($positions) . " positions\n";
        
        // Show current candidates
        if (!empty($candidates)) {
            echo "\nCurrent candidates:\n";
            foreach ($candidates as $candidate) {
                echo "- ID: " . $candidate['id'] . ", Name: " . $candidate['name'] . 
                     ", Position: " . ($candidate['position_name'] ?? 'N/A') . 
                     ", Election: " . ($candidate['election_title'] ?? 'N/A') . "\n";
            }
        } else {
            echo "\nNo candidates found in database.\n";
        }
        
        // Test creating a sample candidate if we have elections and positions
        if (!empty($elections) && !empty($positions)) {
            echo "\nTesting candidate creation...\n";
            
            $testData = [
                'firstname' => 'Test',
                'lastname' => 'Candidate',
                'name' => 'Test Candidate',
                'position_id' => $positions[0]['id'],
                'election_id' => $elections[0]['id'],
                'platform' => 'Test platform for database testing',
                'bio' => 'Test bio',
                'photo' => null
            ];
            
            $candidateId = $candidateModel->addCandidate($testData);
            
            if ($candidateId) {
                echo "✓ Test candidate created successfully with ID: $candidateId\n";
                
                // Clean up - delete the test candidate
                if ($candidateModel->deleteCandidate($candidateId)) {
                    echo "✓ Test candidate cleaned up successfully\n";
                }
            } else {
                echo "✗ Failed to create test candidate\n";
            }
        }
        
    } else {
        echo "✗ Candidates table does not exist!\n";
        
        // Try to create the table
        echo "\nAttempting to create candidates table...\n";
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS candidates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                firstname VARCHAR(100) NOT NULL,
                lastname VARCHAR(100) NOT NULL,
                name VARCHAR(200) NOT NULL,
                position_id INT NOT NULL,
                election_id INT NOT NULL,
                photo VARCHAR(255) DEFAULT NULL,
                platform TEXT NOT NULL,
                bio TEXT DEFAULT NULL,
                status TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
                FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->query($createTableSQL);
        if ($db->execute()) {
            echo "✓ Candidates table created successfully\n";
        } else {
            echo "✗ Failed to create candidates table\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDatabase test completed.\n";
