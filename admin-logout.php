<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Check if admin is logged in
if (Auth::check()) {
    // Get the admin class and call logout
    $admin = new Admin();
    $admin->logout();
    
    // Set success message
    Utils::flashMessage('You have been successfully logged out.', 'success');
}

// Redirect to login page
Utils::redirect('admin-login.php');
