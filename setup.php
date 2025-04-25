<?php
// Initial setup script to create the database and tables

// Include database configuration
require_once 'includes/config.php';

try {
    // Create a new PDO instance
    $db = new PDO("sqlite:" . DB_PATH);
    
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Create users table
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "client",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create services table
    $db->exec('CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create packages table
    $db->exec('CREATE TABLE IF NOT EXISTS packages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        image_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create package_services junction table
    $db->exec('CREATE TABLE IF NOT EXISTS package_services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        package_id INTEGER,
        service_id INTEGER,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        UNIQUE(package_id, service_id)
    )');
    
    // Create bookings table
    $db->exec('CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        package_id INTEGER,
        event_date TEXT NOT NULL,
        event_location TEXT NOT NULL,
        status TEXT DEFAULT "pending",
        total_price REAL NOT NULL,
        discount_applied REAL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
    )');
    
    // Create booking_services for custom packages
    $db->exec('CREATE TABLE IF NOT EXISTS booking_services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_id INTEGER,
        service_id INTEGER,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )');
    
    // Create guests table
    $db->exec('CREATE TABLE IF NOT EXISTS guests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_id INTEGER,
        name TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        rsvp_status TEXT DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )');
    
    // Insert an admin user (password: admin123)
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT OR IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Admin User', 'admin@example.com', $hashedPassword, 'administrator']);
    
    echo "<h2>Database setup completed successfully!</h2>";
    echo "<p>An admin user has been created with:</p>";
    echo "<p>Email: admin@example.com</p>";
    echo "<p>Password: admin123</p>";
    echo "<p><a href='index.php'>Go to homepage</a></p>";
    
} catch(PDOException $e) {
    die("Database setup error: " . $e->getMessage());
}
?>
