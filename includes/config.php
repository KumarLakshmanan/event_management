<?php
// Database configuration
define('DB_PATH', __DIR__ . '/../database.sqlite');

// Application settings
define('APP_NAME', 'Event Planning Platform');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('UPLOADS_DIR', __DIR__ . '/../uploads');

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
