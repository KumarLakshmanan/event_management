<?php
/**
 * Dashboard index page for Event Planning Platform
 * 
 * Redirects to appropriate dashboard based on user role
 */

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Require user to be logged in
requireLogin();

// Redirect to appropriate dashboard based on role
switch ($_SESSION['user_role']) {
    case 'administrator':
        include_once 'admin.php';
        break;
    case 'manager':
        include_once 'manager.php';
        break;
    case 'client':
    default:
        include_once 'client.php';
        break;
}
?>