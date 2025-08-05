<?php
require_once __DIR__ . '/init.php';

echo "<h2>Database Connection and Table Check</h2>";

try {
    $db = new Database();
    echo "<p>✅ Database connection established</p>";
    
    echo "<h3>Available Tables:</h3>";
    $db->query('SHOW TABLES');
    $tables = $db->resultSet();
    
    if (!empty($tables)) {
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>" . htmlspecialchars($tableName) . "</li>";
        }
        echo "</ul>";
        
        // Check if votes table exists
        $votesTableExists = false;
        foreach ($tables as $table) {
            if (array_values($table)[0] === 'votes') {
                $votesTableExists = true;
                break;
            }
        }
        
        if ($votesTableExists) {
            echo "<h3>Votes Table Details:</h3>";
            
            // Try a simple query first
            $db->query('SELECT COUNT(*) as total FROM votes');
            $count = $db->single();
            echo "<p>Total votes in database: " . ($count['total'] ?? 'ERROR') . "</p>";
            
            // Check table structure with a different approach
            $db->query('SHOW CREATE TABLE votes');
            $createTable = $db->single();
            
            if ($createTable) {
                echo "<h4>Table Creation SQL:</h4>";
                echo "<pre>" . htmlspecialchars($createTable['Create Table']) . "</pre>";
            }
            
        } else {
            echo "<p>❌ Votes table does not exist!</p>";
        }
        
    } else {
        echo "<p>❌ No tables found or query failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    
    // Try to get more details about the connection
    echo "<h3>Database Configuration:</h3>";
    echo "<p>Host: " . DB_HOST . "</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
    echo "<p>User: " . DB_USER . "</p>";
}

// Also check if we can access the database configuration
echo "<h3>Configuration Check:</h3>";
if (defined('DB_HOST')) {
    echo "<p>✅ DB_HOST defined: " . DB_HOST . "</p>";
} else {
    echo "<p>❌ DB_HOST not defined</p>";
}

if (defined('DB_NAME')) {
    echo "<p>✅ DB_NAME defined: " . DB_NAME . "</p>";
} else {
    echo "<p>❌ DB_NAME not defined</p>";
}

if (defined('DB_USER')) {
    echo "<p>✅ DB_USER defined: " . DB_USER . "</p>";
} else {
    echo "<p>❌ DB_USER not defined</p>";
}

?>
