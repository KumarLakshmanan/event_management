<?php
/**
 * Configuration file for Event Planning Platform
 * 
 * Contains database connection settings and application constants
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', BASE_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('CONTROLLERS_PATH', BASE_PATH . 'controllers' . DIRECTORY_SEPARATOR);
define('TEMPLATES_PATH', BASE_PATH . 'templates' . DIRECTORY_SEPARATOR);

define('DB_PATH', BASE_PATH . 'database.sqlite');

// Application settings
define('APP_NAME', 'Event Planning Platform');
define('APP_VERSION', '1.0.0');

// Session configuration
session_start();

// Database connection function
function getDBConnection() {
    try {
        // Create or open SQLite database
        $db = new PDO('sqlite:' . DB_PATH);
        
        // Set error mode and enable foreign keys
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON');
        
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>