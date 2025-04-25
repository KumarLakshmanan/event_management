<?php
/**
 * Logout page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';

// Log out the user
logoutUser();

// Redirect to home page
header("Location: /index.php");
exit;
?>