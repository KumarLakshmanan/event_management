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

// Database settings
define('DB_PATH', BASE_PATH . 'database.sqlite');

// Application settings
define('APP_NAME', 'Event Planning Platform');
define('APP_VERSION', '1.0.0');

// Session configuration
session_start();

// Database connection function
function getDBConnection() {
    try {
        // Create SQLite database if it doesn't exist
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize database schema if needed
        initializeDatabase($db);
        
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Initialize database schema
function initializeDatabase($db) {
    // Check if tables exist
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='members'");
    if (!$result->fetch()) {
        // Create tables
        $queries = [
            // Members (users) table
            "CREATE TABLE members (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                phone VARCHAR(20),
                password TEXT NOT NULL,
                address TEXT,
                role VARCHAR(20) NOT NULL DEFAULT 'client',
                can_give_discount BOOLEAN DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Products (services) table
            "CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL
            )",
            
            // Bundles (packages) table
            "CREATE TABLE bundles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(150) NOT NULL,
                image_url TEXT,
                description TEXT,
                price DECIMAL(10,2),
                customized BOOLEAN DEFAULT 0,
                created_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES members(id)
            )",
            
            // BundleProducts (package_services) table
            "CREATE TABLE bundle_products (
                bundle_id INTEGER,
                product_id INTEGER,
                PRIMARY KEY (bundle_id, product_id),
                FOREIGN KEY (bundle_id) REFERENCES bundles(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )",
            
            // Reservations (bookings) table
            "CREATE TABLE reservations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                bundle_id INTEGER,
                event_place TEXT NOT NULL,
                event_date TIMESTAMP NOT NULL,
                discount DECIMAL(10,2),
                confirmed_by INTEGER,
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES members(id) ON DELETE CASCADE,
                FOREIGN KEY (bundle_id) REFERENCES bundles(id),
                FOREIGN KEY (confirmed_by) REFERENCES members(id)
            )",
            
            // Attendees (guests) table
            "CREATE TABLE attendees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER,
                name VARCHAR(100),
                email VARCHAR(150),
                phone VARCHAR(20),
                rsvp_status VARCHAR(10) DEFAULT 'pending',
                FOREIGN KEY (booking_id) REFERENCES reservations(id) ON DELETE CASCADE
            )",
            
            // AttendanceRecords (event_attendance) table
            "CREATE TABLE attendance_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                guest_id INTEGER,
                attended BOOLEAN DEFAULT 0,
                checked_in_at TIMESTAMP,
                remarks TEXT,
                FOREIGN KEY (guest_id) REFERENCES attendees(id) ON DELETE CASCADE
            )"
        ];
        
        // Execute all table creation queries
        foreach ($queries as $query) {
            $db->exec($query);
        }
        
        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO members (name, email, password, role) 
                  VALUES ('Admin User', 'admin@example.com', '$adminPassword', 'administrator')");
    }
}
?>