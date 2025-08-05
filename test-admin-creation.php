<?php
// Test admin creation functionality
require_once __DIR__ . '/init.php';

echo "<h2>Testing Admin Creation</h2>";

// Create Admin instance
$admin = new Admin();

// Test data
$testData = [
    'username' => 'testadmin',
    'email' => 'test@example.com',
    'password' => 'testpassword123',
    'full_name' => 'Test Administrator'
];

echo "<h3>Test Data:</h3>";
echo "<pre>";
print_r($testData);
echo "</pre>";

echo "<h3>Testing create() method...</h3>";

try {
    $result = $admin->create($testData);
    
    if ($result) {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #f0fff0;'>";
        echo "✅ SUCCESS: Admin created successfully!";
        echo "</div>";
        
        // Try to authenticate the new admin
        echo "<h3>Testing authentication...</h3>";
        $authResult = $admin->authenticate($testData['username'], $testData['password']);
        
        if ($authResult) {
            echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #f0fff0;'>";
            echo "✅ SUCCESS: Authentication works!<br>";
            echo "Admin ID: " . $authResult['id'] . "<br>";
            echo "Username: " . $authResult['username'] . "<br>";
            echo "Email: " . $authResult['email'] . "<br>";
            echo "Full Name: " . $authResult['full_name'];
            echo "</div>";
        } else {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
            echo "❌ ERROR: Authentication failed!";
            echo "</div>";
        }
        
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
        echo "❌ ERROR: Admin creation failed!";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
    echo "❌ EXCEPTION: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>Testing duplicate creation (should fail)...</h3>";

try {
    $result2 = $admin->create($testData);
    
    if ($result2) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
        echo "❌ ERROR: Duplicate admin creation should have failed but didn't!";
        echo "</div>";
    } else {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #f0fff0;'>";
        echo "✅ SUCCESS: Duplicate admin creation properly rejected!";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
    echo "❌ EXCEPTION: " . $e->getMessage();
    echo "</div>";
}

echo "<br><a href='admin-register.php'>Go to Admin Registration Page</a>";
echo "<br><a href='admin-login.php'>Go to Admin Login Page</a>";
?>
