<?php
// Debug admin login issues
require_once __DIR__ . '/init.php';

echo "<h2>Debug Admin Login</h2>";

// Check if admins table exists and has data
echo "<h3>1. Checking admins table...</h3>";
try {
    $db = new Database();
    $db->query("SELECT * FROM admins");
    $admins = $db->resultSet();
    
    if ($admins) {
        echo "<div style='color: green;'>✅ Found " . count($admins) . " admin(s) in database</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash</th><th>Created</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . substr(htmlspecialchars($admin['password']), 0, 20) . "...</td>";
            echo "<td>" . htmlspecialchars($admin['created_at'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: red;'>❌ No admins found in database</div>";
        
        // Create default admin
        echo "<h3>Creating default admin...</h3>";
        $defaultPassword = 'admin123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $db->query("INSERT INTO admins (username, email, password, full_name, created_at) VALUES (:username, :email, :password, :full_name, NOW())");
        $db->bind(':username', 'admin');
        $db->bind(':email', 'admin@unzanasa.com');
        $db->bind(':password', $hashedPassword);
        $db->bind(':full_name', 'System Administrator');
        
        if ($db->execute()) {
            echo "<div style='color: green;'>✅ Default admin created successfully</div>";
            echo "<div><strong>Username:</strong> admin</div>";
            echo "<div><strong>Password:</strong> admin123</div>";
        } else {
            echo "<div style='color: red;'>❌ Failed to create default admin</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Test authentication with known credentials
echo "<h3>2. Testing authentication...</h3>";
try {
    $admin = new Admin();
    
    // Test with admin/admin123
    echo "<h4>Testing admin/admin123:</h4>";
    $result = $admin->authenticate('admin', 'admin123');
    
    if ($result) {
        echo "<div style='color: green;'>✅ Authentication successful!</div>";
        echo "<div>User data: " . json_encode($result) . "</div>";
    } else {
        echo "<div style='color: red;'>❌ Authentication failed</div>";
        
        // Check if user exists
        $db = new Database();
        $db->query("SELECT * FROM admins WHERE username = :username");
        $db->bind(':username', 'admin');
        $user = $db->single();
        
        if ($user) {
            echo "<div style='color: orange;'>⚠️ User exists in database</div>";
            echo "<div>Stored password hash: " . substr($user['password'], 0, 30) . "...</div>";
            
            // Test password verification
            $passwordCheck = password_verify('admin123', $user['password']);
            echo "<div>Password verification result: " . ($passwordCheck ? 'PASS' : 'FAIL') . "</div>";
            
            if (!$passwordCheck) {
                echo "<div style='color: red;'>❌ Password hash doesn't match</div>";
                echo "<h4>Updating password hash...</h4>";
                
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $db->query("UPDATE admins SET password = :password WHERE username = :username");
                $db->bind(':password', $newHash);
                $db->bind(':username', 'admin');
                
                if ($db->execute()) {
                    echo "<div style='color: green;'>✅ Password hash updated</div>";
                    
                    // Test again
                    $result2 = $admin->authenticate('admin', 'admin123');
                    if ($result2) {
                        echo "<div style='color: green;'>✅ Authentication now works!</div>";
                    } else {
                        echo "<div style='color: red;'>❌ Authentication still fails</div>";
                    }
                } else {
                    echo "<div style='color: red;'>❌ Failed to update password</div>";
                }
            }
        } else {
            echo "<div style='color: red;'>❌ User 'admin' not found in database</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Authentication error: " . $e->getMessage() . "</div>";
}

// Test Admin class methods
echo "<h3>3. Testing Admin class...</h3>";
try {
    $admin = new Admin();
    echo "<div style='color: green;'>✅ Admin class instantiated</div>";
    
    if (method_exists($admin, 'authenticate')) {
        echo "<div style='color: green;'>✅ authenticate() method exists</div>";
    } else {
        echo "<div style='color: red;'>❌ authenticate() method missing</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Admin class error: " . $e->getMessage() . "</div>";
}

echo "<br><br><a href='admin-login.php'>Go to Admin Login</a>";
?>
