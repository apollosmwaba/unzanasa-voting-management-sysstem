<?php
// Fix the votes table by adding missing columns
require_once __DIR__ . '/init.php';

try {
    $db = new Database();
    
    echo "Checking votes table structure...\n";
    
    // Get current table structure
    $db->query("DESCRIBE votes");
    $columns = $db->resultSet();
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column['Field'];
    }
    
    echo "Current columns: " . implode(', ', $existingColumns) . "\n\n";
    
    // Define required columns
    $requiredColumns = [
        'ip_address' => 'VARCHAR(45) DEFAULT NULL',
        'user_agent' => 'TEXT DEFAULT NULL',
        'voted_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    $columnsAdded = 0;
    
    foreach ($requiredColumns as $columnName => $columnDefinition) {
        if (!in_array($columnName, $existingColumns)) {
            echo "Adding missing column: $columnName\n";
            
            $db->query("ALTER TABLE votes ADD COLUMN $columnName $columnDefinition");
            
            if ($db->execute()) {
                echo "✓ Successfully added $columnName column\n";
                $columnsAdded++;
            } else {
                echo "✗ Failed to add $columnName column\n";
                $errorInfo = $db->getErrorInfo();
                if ($errorInfo[0] !== '00000') {
                    echo "  Error: " . $errorInfo[2] . "\n";
                }
            }
        } else {
            echo "✓ Column $columnName already exists\n";
        }
    }
    
    if ($columnsAdded > 0) {
        echo "\nUpdated votes table structure:\n";
        $db->query("DESCRIBE votes");
        $newColumns = $db->resultSet();
        
        foreach ($newColumns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")" . 
                 ($column['Null'] == 'YES' ? ' NULL' : ' NOT NULL') . 
                 ($column['Default'] ? " DEFAULT '" . $column['Default'] . "'" : '') . "\n";
        }
    }
    
    echo "\nVotes table fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
