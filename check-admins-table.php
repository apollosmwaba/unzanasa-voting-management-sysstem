<?php
// Check if admins table exists and create it if needed
require_once __DIR__ . '/init.php';

echo "<h2>Checking Admins Table</h2>";

try {
    $db = new Database();
    
    // Check if table exists
    echo "<h3>1. Checking if admins table exists...</h3>";
    $db->query("SHOW TABLES LIKE 'admins'");
    $tableExists = $db->single();
    
    if ($tableExists) {
        echo "<div style='color: green;'>✅ Admins table exists</div>";
        
        // Show table structure
        echo "<h3>2. Table structure:</h3>";
        $db->query("DESCRIBE admins");
        $columns = $db->resultSet();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<div style='color: red;'>❌ Admins table does not exist</div>";
        echo "<h3>Creating admins table...</h3>";
        
        $createTableSQL = "
        CREATE TABLE admins (
            id INT(11) NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_username (username),
            UNIQUE KEY unique_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($createTableSQL);
        $result = $db->execute();
        
        if ($result) {
            echo "<div style='color: green;'>✅ Admins table created successfully</div>";
            
            // Create default admin
            echo "<h3>Creating default admin...</h3>";
            $defaultAdmin = [
                'username' => 'admin',
                'email' => 'admin@unzanasa.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'full_name' => 'System Administrator'
            ];
            
            $db->query("INSERT INTO admins (username, email, password, full_name) VALUES (:username, :email, :password, :full_name)");
            $db->bind(':username', $defaultAdmin['username']);
            $db->bind(':email', $defaultAdmin['email']);
            $db->bind(':password', $defaultAdmin['password']);
            $db->bind(':full_name', $defaultAdmin['full_name']);
            
            if ($db->execute()) {
                echo "<div style='color: green;'>✅ Default admin created (username: admin, password: admin123)</div>";
            } else {
                echo "<div style='color: red;'>❌ Failed to create default admin</div>";
            }
        } else {
            echo "<div style='color: red;'>❌ Failed to create admins table</div>";
        }
    }
    
    // Test insert
    echo "<h3>3. Testing direct insert...</h3>";
    $testData = [
        'username' => 'test_' . time(),
        'email' => 'test' . time() . '@example.com',
        'password' => password_hash('testpass', PASSWORD_DEFAULT),
        'full_name' => 'Test User'
    ];
    
    $db->query("INSERT INTO admins (username, email, password, full_name) VALUES (:username, :email, :password, :full_name)");
    $db->bind(':username', $testData['username']);
    $db->bind(':email', $testData['email']);
    $db->bind(':password', $testData['password']);
    $db->bind(':full_name', $testData['full_name']);
    
    if ($db->execute()) {
        echo "<div style='color: green;'>✅ Test insert successful</div>";
        echo "<div>Inserted: " . json_encode($testData) . "</div>";
    } else {
        echo "<div style='color: red;'>❌ Test insert failed</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></div>";
}

echo "<br><br><a href='admin-register.php'>Go to Admin Registration</a>";
echo "<br><a href='admin-login.php'>Go to Admin Login</a>";
?>
