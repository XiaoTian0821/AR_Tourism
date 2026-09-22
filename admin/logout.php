<?php
/**
 * AR Tourism Explorer - Admin Logout
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Log out the user
logoutUser();

// Redirect to login page
header('Location: ' . APP_URL . '/admin/login.php?logged_out=1');
exit;
?>
