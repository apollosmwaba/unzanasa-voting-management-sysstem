<?php
require_once __DIR__ . '/init.php';

try {
    $db = new Database();
    
    echo "Checking candidates table structure...\n\n";
    
    // Check if table exists
    $db->query("SHOW TABLES LIKE 'candidates'");
    $table = $db->single();
    
    if ($table) {
        echo "✓ Candidates table exists\n\n";
        
        // Show table structure
        echo "Current table structure:\n";
        $db->query("DESCRIBE candidates");
        $columns = $db->resultSet();
        
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ") " . 
                 ($column['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . 
                 ($column['Default'] ? " DEFAULT '" . $column['Default'] . "'" : '') . "\n";
        }
        
        // Check if status column exists
        $statusExists = false;
        foreach ($columns as $column) {
            if ($column['Field'] == 'status') {
                $statusExists = true;
                break;
            }
        }
        
        echo "\nStatus column exists: " . ($statusExists ? "✓ Yes" : "✗ No") . "\n";
        
        if (!$statusExists) {
            echo "\nAdding status column...\n";
            $db->query("ALTER TABLE candidates ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER bio");
            if ($db->execute()) {
                echo "✓ Status column added successfully\n";
            } else {
                echo "✗ Failed to add status column\n";
            }
        }
        
    } else {
        echo "✗ Candidates table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
