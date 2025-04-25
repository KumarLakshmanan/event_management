<?php


date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@ini_set("display_startup_errors", "1");
@ini_set('display_errors', 'On');
@ini_set('error_reporting', 1);
@ini_set('error_reporting', E_ALL);
ini_set('log_errors', true);
ini_set('error_log', './php-error.log');
// Database configuration
define('DB_PATH', __DIR__ . '/../database.sqlite');

// Application settings
define('APP_NAME', 'Event Planning Platform');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/event_v3/');
define('UPLOADS_DIR', __DIR__ . '/../uploads');

// $mailUsername = 'kumar.lakshmanan.projects@gmail.com';
// $mailPassword   = 'vgwjdkoiirxcvhds';

define('MAIL_USERNAME', 'kumar.lakshmanan.projects@gmail.com');
define('MAIL_PASSWORD', 'yhkrxirfwzvurbhx');

// User roles
define('ROLE_ADMIN', 'administrator');
define('ROLE_MANAGER', 'manager');
define('ROLE_CLIENT', 'client');

// Booking statuses
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_COMPLETED', 'completed');

// RSVP statuses
define('RSVP_PENDING', 'pending');
define('RSVP_ACCEPTED', 'accepted');
define('RSVP_DECLINED', 'declined');

// Notification types
define('NOTIFICATION_BOOKING', 'booking');
define('NOTIFICATION_USER', 'user');
define('NOTIFICATION_RSVP', 'rsvp');

// Make sure the uploads directory exists
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

// Set default timezone
date_default_timezone_set('UTC');

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting - set to 0 in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to get database connection
function getDB() {
    try {
        $db = new PDO("sqlite:" . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON;');
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>