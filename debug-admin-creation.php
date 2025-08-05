<?php
// Debug admin creation issues
require_once __DIR__ . '/init.php';

echo "<h2>Debug Admin Creation</h2>";

// Check if admins table exists
echo "<h3>1. Checking admins table structure...</h3>";
try {
    $db = new Database();
    $db->query("DESCRIBE admins");
    $columns = $db->resultSet();
    
    if ($columns) {
        echo "<div style='color: green;'>✅ Admins table exists</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
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
        echo "<div style='color: red;'>❌ Admins table does not exist or is empty</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error checking table: " . $e->getMessage() . "</div>";
}

// Check current admin records
echo "<h3>2. Current admin records...</h3>";
try {
    $db->query("SELECT * FROM admins");
    $admins = $db->resultSet();
    
    if ($admins) {
        echo "<div style='color: blue;'>Found " . count($admins) . " admin(s)</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created At</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['created_at'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: orange;'>⚠️ No admin records found</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error fetching admins: " . $e->getMessage() . "</div>";
}

// Test database connection
echo "<h3>3. Testing database connection...</h3>";
try {
    $db->query("SELECT 1 as test");
    $result = $db->single();
    if ($result && $result['test'] == 1) {
        echo "<div style='color: green;'>✅ Database connection working</div>";
    } else {
        echo "<div style='color: red;'>❌ Database connection issue</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</div>";
}

// Test Admin class instantiation
echo "<h3>4. Testing Admin class...</h3>";
try {
    $admin = new Admin();
    echo "<div style='color: green;'>✅ Admin class instantiated successfully</div>";
    
    // Test method exists
    if (method_exists($admin, 'create')) {
        echo "<div style='color: green;'>✅ create() method exists</div>";
    } else {
        echo "<div style='color: red;'>❌ create() method does not exist</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Admin class error: " . $e->getMessage() . "</div>";
}

// Test manual insert
echo "<h3>5. Testing manual database insert...</h3>";
try {
    $testData = [
        'username' => 'debugtest_' . time(),
        'email' => 'debug' . time() . '@test.com',
        'password' => password_hash('testpass123', PASSWORD_DEFAULT),
        'full_name' => 'Debug Test User'
    ];
    
    $db->query("INSERT INTO admins (username, email, password, full_name, created_at) VALUES (:username, :email, :password, :full_name, NOW())");
    $db->bind(':username', $testData['username']);
    $db->bind(':email', $testData['email']);
    $db->bind(':password', $testData['password']);
    $db->bind(':full_name', $testData['full_name']);
    
    $result = $db->execute();
    
    if ($result) {
        echo "<div style='color: green;'>✅ Manual insert successful</div>";
        echo "<div>Test data: " . json_encode($testData) . "</div>";
    } else {
        echo "<div style='color: red;'>❌ Manual insert failed</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Manual insert error: " . $e->getMessage() . "</div>";
}

// Test Admin->create() method
echo "<h3>6. Testing Admin->create() method...</h3>";
try {
    $admin = new Admin();
    $testData2 = [
        'username' => 'methodtest_' . time(),
        'email' => 'method' . time() . '@test.com',
        'password' => 'testpass456',
        'full_name' => 'Method Test User'
    ];
    
    echo "<div>Test data: " . json_encode($testData2) . "</div>";
    
    $result = $admin->create($testData2);
    
    if ($result) {
        echo "<div style='color: green;'>✅ Admin->create() method successful</div>";
    } else {
        echo "<div style='color: red;'>❌ Admin->create() method failed</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Admin->create() method error: " . $e->getMessage() . "</div>";
}

echo "<br><br><a href='admin-register.php'>Go to Admin Registration</a>";
?>
