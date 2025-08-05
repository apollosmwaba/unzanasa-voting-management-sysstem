<?php
// Simple admin creation test
require_once __DIR__ . '/init.php';

echo "<h2>Simple Admin Creation Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Processing Form Submission...</h3>";
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    echo "<div>Received data:</div>";
    echo "<ul>";
    echo "<li>Username: " . htmlspecialchars($username) . "</li>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>Password: " . (empty($password) ? 'EMPTY' : '[PROVIDED]') . "</li>";
    echo "<li>Full Name: " . htmlspecialchars($full_name) . "</li>";
    echo "</ul>";
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        echo "<div style='color: red;'>❌ Missing required fields</div>";
    } else {
        try {
            echo "<h4>Step 1: Creating Admin instance...</h4>";
            $admin = new Admin();
            echo "<div style='color: green;'>✅ Admin instance created</div>";
            
            echo "<h4>Step 2: Preparing data...</h4>";
            $adminData = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'full_name' => $full_name
            ];
            echo "<div style='color: green;'>✅ Data prepared</div>";
            
            echo "<h4>Step 3: Calling create method...</h4>";
            $result = $admin->create($adminData);
            
            if ($result) {
                echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #f0fff0;'>";
                echo "✅ SUCCESS: Admin created successfully!";
                echo "</div>";
                
                // Verify creation
                echo "<h4>Step 4: Verifying creation...</h4>";
                $db = new Database();
                $db->query("SELECT * FROM admins WHERE username = :username");
                $db->bind(':username', $username);
                $createdAdmin = $db->single();
                
                if ($createdAdmin) {
                    echo "<div style='color: green;'>✅ Admin found in database:</div>";
                    echo "<ul>";
                    echo "<li>ID: " . $createdAdmin['id'] . "</li>";
                    echo "<li>Username: " . $createdAdmin['username'] . "</li>";
                    echo "<li>Email: " . $createdAdmin['email'] . "</li>";
                    echo "<li>Full Name: " . $createdAdmin['full_name'] . "</li>";
                    echo "<li>Created: " . $createdAdmin['created_at'] . "</li>";
                    echo "</ul>";
                } else {
                    echo "<div style='color: red;'>❌ Admin not found in database after creation!</div>";
                }
                
            } else {
                echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
                echo "❌ FAILED: Admin creation returned false";
                echo "</div>";
                
                // Check for existing admin
                echo "<h4>Checking for existing admin...</h4>";
                $db = new Database();
                $db->query("SELECT * FROM admins WHERE username = :username OR email = :email");
                $db->bind(':username', $username);
                $db->bind(':email', $email);
                $existing = $db->single();
                
                if ($existing) {
                    echo "<div style='color: orange;'>⚠️ Admin with this username or email already exists:</div>";
                    echo "<ul>";
                    echo "<li>ID: " . $existing['id'] . "</li>";
                    echo "<li>Username: " . $existing['username'] . "</li>";
                    echo "<li>Email: " . $existing['email'] . "</li>";
                    echo "</ul>";
                } else {
                    echo "<div style='color: red;'>❌ No existing admin found - creation failed for unknown reason</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fff0f0;'>";
            echo "❌ EXCEPTION: " . $e->getMessage();
            echo "<br>File: " . $e->getFile() . " Line: " . $e->getLine();
            echo "</div>";
        }
    }
    
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Admin Test</title>
</head>
<body>
    <h2>Create Admin Test Form</h2>
    <form method="POST">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input type="email" name="email" required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td>Full Name:</td>
                <td><input type="text" name="full_name" required></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Create Admin">
                </td>
            </tr>
        </table>
    </form>
    
    <hr>
    <h3>Current Admins in Database:</h3>
    <?php
    try {
        $db = new Database();
        $db->query("SELECT * FROM admins ORDER BY created_at DESC");
        $admins = $db->resultSet();
        
        if ($admins) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . $admin['id'] . "</td>";
                echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['full_name']) . "</td>";
                echo "<td>" . $admin['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='color: orange;'>No admins found in database</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error fetching admins: " . $e->getMessage() . "</div>";
    }
    ?>
    
    <br><br>
    <a href="admin-register.php">Go to Official Admin Registration</a>
</body>
</html>
