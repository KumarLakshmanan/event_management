<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit;
}

// Redirect to dashboard
header("Location: pages/dashboard.php");
exit;
?>
