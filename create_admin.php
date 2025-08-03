<?php
// Create admin user
require_once __DIR__ . '/init.php';

$db = new Database();

// Check if admin already exists
$db->query('SELECT id FROM admins WHERE username = :username');
$db->bind(':username', 'admin');

if ($db->rowCount() > 0) {
    die('Admin user already exists');
}

// Create admin user
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@unzanasa.com';
$fullName = 'System Administrator';

$db->query('INSERT INTO admins (username, password, email, full_name) VALUES (:username, :password, :email, :full_name)');
$db->bind(':username', $username);
$db->bind(':password', $password);
$db->bind(':email', $email);
$db->bind(':full_name', $fullName);

if ($db->execute()) {
    echo 'Admin user created successfully!<br>';
    echo 'Username: admin<br>';
    echo 'Password: admin123<br>';
    echo '<a href="admin-login.php">Go to Login</a>';
} else {
    echo 'Error creating admin user';
}
