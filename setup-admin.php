<?php
// Setup default admin user
require_once __DIR__ . '/init.php';

echo "<h2>Setting up Admin User</h2>";

try {
    $db = new Database();
    
    // Check if admin table exists
    $db->query("SHOW TABLES LIKE 'admins'");
    $tableExists = $db->single();
    
    if (!$tableExists) {
        echo "<div style='color: red;'>❌ Admins table doesn't exist. Creating it...</div>";
        
        $createTableSQL = "
        CREATE TABLE `admins` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `full_name` varchar(100) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `last_login` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $db->query($createTableSQL);
        if ($db->execute()) {
            echo "<div style='color: green;'>✅ Admins table created successfully</div>";
        } else {
            echo "<div style='color: red;'>❌ Failed to create admins table</div>";
            exit;
        }
    } else {
        echo "<div style='color: green;'>✅ Admins table exists</div>";
    }
    
    // Check if admin user exists
    $db->query("SELECT * FROM admins WHERE username = 'admin'");
    $adminExists = $db->single();
    
    if ($adminExists) {
        echo "<div style='color: orange;'>⚠️ Admin user already exists</div>";
        echo "<div>Updating password to ensure it works...</div>";
        
        // Update password to ensure it's correct
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("UPDATE admins SET password = :password WHERE username = 'admin'");
        $db->bind(':password', $hashedPassword);
        
        if ($db->execute()) {
            echo "<div style='color: green;'>✅ Admin password updated</div>";
        } else {
            echo "<div style='color: red;'>❌ Failed to update admin password</div>";
        }
    } else {
        echo "<div style='color: blue;'>Creating new admin user...</div>";
        
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("INSERT INTO admins (username, email, password, full_name, created_at) VALUES (:username, :email, :password, :full_name, NOW())");
        $db->bind(':username', 'admin');
        $db->bind(':email', 'admin@unzanasa.com');
        $db->bind(':password', $hashedPassword);
        $db->bind(':full_name', 'System Administrator');
        
        if ($db->execute()) {
            echo "<div style='color: green;'>✅ Admin user created successfully</div>";
        } else {
            echo "<div style='color: red;'>❌ Failed to create admin user</div>";
        }
    }
    
    // Test authentication
    echo "<h3>Testing Authentication</h3>";
    $admin = new Admin();
    $result = $admin->authenticate('admin', 'admin123');
    
    if ($result) {
        echo "<div style='color: green;'>✅ Authentication test successful!</div>";
        echo "<div><strong>Login Credentials:</strong></div>";
        echo "<div>Username: <strong>admin</strong></div>";
        echo "<div>Password: <strong>admin123</strong></div>";
    } else {
        echo "<div style='color: red;'>❌ Authentication test failed</div>";
    }
    
    // Show all admins
    echo "<h3>Current Admin Users</h3>";
    $db->query("SELECT id, username, email, full_name, created_at, last_login FROM admins");
    $admins = $db->resultSet();
    
    if ($admins) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th><th>Last Login</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['full_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($admin['created_at'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($admin['last_login'] ?? 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<br><br><a href='admin-login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a>";
?>
