<?php
require_once __DIR__ . '/init.php';

try {
    echo "Testing database connection...\n";
    $db = new Database();
    
    // Test basic query
    $db->query("SELECT 1 as test");
    $result = $db->single();
    echo "Database connection: " . ($result ? "✓ OK" : "✗ Failed") . "\n";
    
    // Check candidates table
    $db->query("SHOW TABLES LIKE 'candidates'");
    $table = $db->single();
    echo "Candidates table exists: " . ($table ? "✓ Yes" : "✗ No") . "\n";
    
    if ($table) {
        // Check table structure
        $db->query("DESCRIBE candidates");
        $columns = $db->resultSet();
        echo "Candidates table columns: " . count($columns) . "\n";
        
        // Count existing candidates
        $db->query("SELECT COUNT(*) as count FROM candidates");
        $count = $db->single();
        echo "Existing candidates: " . $count['count'] . "\n";
    }
    
    // Test models
    echo "\nTesting models...\n";
    $candidateModel = new Candidate();
    $electionModel = new Election();
    $positionModel = new Position();
    
    $elections = $electionModel->getAllElections();
    $positions = $positionModel->getAllPositions();
    
    echo "Elections found: " . count($elections) . "\n";
    echo "Positions found: " . count($positions) . "\n";
    
    if (count($elections) > 0 && count($positions) > 0) {
        echo "✓ Ready to test candidate creation\n";
    } else {
        echo "✗ Need elections and positions to test candidates\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
