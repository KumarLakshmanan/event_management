<?php
/**
 * Logout page for Event Planning Platform
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';

// Log out the user
logoutUser();

// Redirect to home page
header("Location: /index.php");
exit;
?>