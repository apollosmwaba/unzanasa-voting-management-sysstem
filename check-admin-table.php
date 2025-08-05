<?php
require_once __DIR__ . '/init.php';

echo "<h2>Admin Table Structure Check</h2>";

try {
    $db = new Database();
    
    // Check if admins table exists
    $db->query("SHOW TABLES LIKE 'admins'");
    $tableExists = $db->single();
    
    if ($tableExists) {
        echo "<p>✅ Admins table exists</p>";
        
        // Get table structure
        $db->query("DESCRIBE admins");
        $columns = $db->resultSet();
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check existing admins
        $db->query("SELECT * FROM admins");
        $admins = $db->resultSet();
        
        echo "<h3>Existing Admins:</h3>";
        if (!empty($admins)) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach (array_keys($admins[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                foreach ($admin as $key => $value) {
                    if ($key === 'password') {
                        echo "<td>[HIDDEN]</td>";
                    } else {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No admins found in the database</p>";
        }
        
    } else {
        echo "<p>❌ Admins table does not exist</p>";
        
        // Create the table
        echo "<h3>Creating Admins Table...</h3>";
        $createTableSQL = "
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            full_name VARCHAR(100) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )";
        
        $db->query($createTableSQL);
        if ($db->execute()) {
            echo "<p>✅ Admins table created successfully</p>";
            
            // Create default admin
            echo "<h3>Creating Default Admin...</h3>";
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $db->query("INSERT INTO admins (username, email, password, first_name, last_name) 
                       VALUES ('admin', 'admin@unzanasa.com', :password, 'System', 'Administrator')");
            $db->bind(':password', $defaultPassword);
            
            if ($db->execute()) {
                echo "<p>✅ Default admin created (username: admin, password: admin123)</p>";
            } else {
                echo "<p>❌ Failed to create default admin</p>";
            }
        } else {
            echo "<p>❌ Failed to create admins table</p>";
        }
    }
    
    // Check admin_sessions table
    echo "<h3>Admin Sessions Table:</h3>";
    $db->query("SHOW TABLES LIKE 'admin_sessions'");
    $sessionTableExists = $db->single();
    
    if (!$sessionTableExists) {
        echo "<p>❌ Admin sessions table does not exist. Creating...</p>";
        $createSessionTableSQL = "
        CREATE TABLE admin_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
            UNIQUE KEY unique_admin_session (admin_id, session_id)
        )";
        
        $db->query($createSessionTableSQL);
        if ($db->execute()) {
            echo "<p>✅ Admin sessions table created successfully</p>";
        } else {
            echo "<p>❌ Failed to create admin sessions table</p>";
        }
    } else {
        echo "<p>✅ Admin sessions table exists</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
