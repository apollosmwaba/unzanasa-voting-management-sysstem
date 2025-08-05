<?php
require_once __DIR__ . '/init.php';

echo "<h2>🧪 Testing Admin Registration System</h2>";

try {
    $db = new Database();
    
    echo "<h3>Step 1: Check Current Admins</h3>";
    $db->query('SELECT * FROM admins');
    $currentAdmins = $db->resultSet();
    echo "<p>Current admin count: " . count($currentAdmins) . "</p>";
    
    if (!empty($currentAdmins)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Created</th></tr>";
        foreach ($currentAdmins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '') ?: $admin['full_name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($admin['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Step 2: Test Registration Process</h3>";
    
    // Test data
    $testAdmin = [
        'username' => 'testadmin_' . time(),
        'email' => 'testadmin' . time() . '@unzanasa.com',
        'password' => 'testpass123',
        'first_name' => 'Test',
        'last_name' => 'Administrator'
    ];
    
    echo "<p>Testing with:</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($testAdmin['username']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($testAdmin['email']) . "</li>";
    echo "<li><strong>Password:</strong> " . htmlspecialchars($testAdmin['password']) . "</li>";
    echo "<li><strong>Name:</strong> " . htmlspecialchars($testAdmin['first_name'] . ' ' . $testAdmin['last_name']) . "</li>";
    echo "</ul>";
    
    // Check if username/email already exists
    $db->query('SELECT COUNT(*) as count FROM admins WHERE username = :username OR email = :email');
    $db->bind(':username', $testAdmin['username']);
    $db->bind(':email', $testAdmin['email']);
    $result = $db->single();
    
    if ($result['count'] > 0) {
        echo "<p>❌ Username or email already exists</p>";
    } else {
        echo "<p>✅ Username and email are available</p>";
        
        // Create the admin
        $hashedPassword = password_hash($testAdmin['password'], PASSWORD_DEFAULT);
        
        $db->query('INSERT INTO admins (username, email, password, first_name, last_name, created_at) 
                   VALUES (:username, :email, :password, :first_name, :last_name, NOW())');
        $db->bind(':username', $testAdmin['username']);
        $db->bind(':email', $testAdmin['email']);
        $db->bind(':password', $hashedPassword);
        $db->bind(':first_name', $testAdmin['first_name']);
        $db->bind(':last_name', $testAdmin['last_name']);
        
        if ($db->execute()) {
            echo "<p>✅ Test admin created successfully!</p>";
            
            // Test authentication
            echo "<h3>Step 3: Test Authentication</h3>";
            $admin = new Admin();
            $authResult = $admin->authenticate($testAdmin['username'], $testAdmin['password']);
            
            if ($authResult) {
                echo "<p>✅ Authentication successful!</p>";
                echo "<p>Authenticated user data:</p>";
                echo "<pre>";
                print_r(array_filter($authResult, function($key) {
                    return $key !== 'password';
                }, ARRAY_FILTER_USE_KEY));
                echo "</pre>";
            } else {
                echo "<p>❌ Authentication failed</p>";
            }
            
        } else {
            echo "<p>❌ Failed to create test admin</p>";
        }
    }
    
    echo "<h3>Step 4: Final Admin Count</h3>";
    $db->query('SELECT COUNT(*) as count FROM admins');
    $result = $db->single();
    echo "<p>Total admins now: " . $result['count'] . "</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>✅ Admin Registration System Test Complete!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li><a href='admin-register.php'>Test the registration form</a></li>";
    echo "<li><a href='admin-login.php'>Test login with new admin</a></li>";
    echo "<li><a href='manage-admins.php'>View admin management page</a></li>";
    echo "<li><a href='admin-dashboard.php'>Check updated dashboard</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
