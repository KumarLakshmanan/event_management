<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logout the user
logout();

// Set flash message
setFlashMessage('You have been successfully logged out.', 'success');

// Redirect to home page
header('Location: ' . APP_URL . 'index.php');
exit;
?>
