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
        can_apply_discount INTEGER DEFAULT 0,
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
        user_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    // ALTER TABLE packages ADD COLUMN user_id INTEGER;
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
    
    // Create notifications table
    $db->exec('CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        type TEXT NOT NULL,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        related_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    
    // Insert default users (password: password123)
    $adminPassword = password_hash('Admin123', PASSWORD_DEFAULT);
    $managerPassword = password_hash('Manager123', PASSWORD_DEFAULT);
    $clientPassword = password_hash('Client123', PASSWORD_DEFAULT);

    // Admin user
    $stmt = $db->prepare('INSERT OR IGNORE INTO users (name, email, password, role, can_apply_discount) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['Admin User', 'admin@example.com', $adminPassword, 'administrator', 1]);
    
    // Manager user with discount permission
    $stmt->execute(['Manager User', 'manager@example.com', $managerPassword, 'manager', 1]);
    
    // Manager without discount permission
    $stmt->execute(['Manager No Discount', 'manager2@example.com', $managerPassword, 'manager', 0]);
    
    // Client user
    $stmt->execute(['Client User', 'client@example.com', $clientPassword, 'client', 0]);
    
    // Insert sample packages
    $packageStmt = $db->prepare('INSERT OR IGNORE INTO packages (name, description, price, image_path) VALUES (?, ?, ?, ?)');
    $packageStmt->execute(['Basic Wedding', 'Essential wedding services including photographer, basic decoration, and music.', 999.99, 'default_package.jpg']);
    $packageStmt->execute(['Premium Wedding', 'Premium wedding package with professional photography, videography, gourmet catering, and elegant decorations.', 2499.99, 'default_package.jpg']);
    $packageStmt->execute(['Birthday Party', 'Fun-filled birthday party package with decorations, entertainment, and catering.', 399.99, 'default_package.jpg']);
    $packageStmt->execute(['Corporate Event', 'Professional corporate event solution with A/V equipment, catering, and venue decoration.', 1499.99, 'default_package.jpg']);
    
    // Insert sample services
    $serviceStmt = $db->prepare('INSERT OR IGNORE INTO services (name, description, price) VALUES (?, ?, ?)');
    $serviceStmt->execute(['Photography', 'Professional event photography service (4 hours)', 349.99]);
    $serviceStmt->execute(['Videography', 'HD video recording and editing of your event', 449.99]);
    $serviceStmt->execute(['Catering', 'Gourmet food service for up to 50 guests', 799.99]);
    $serviceStmt->execute(['Decoration', 'Elegant venue decoration including flowers and lighting', 299.99]);
    $serviceStmt->execute(['DJ Services', 'Professional DJ with sound system for 5 hours', 349.99]);
    $serviceStmt->execute(['Live Band', 'Professional live music band for your event', 899.99]);
    $serviceStmt->execute(['Venue Rental', 'Exclusive venue rental for your event', 999.99]);
    
    echo "<h2>Database setup completed successfully!</h2>";
    echo "<p>The following users have been created (all with password: password123):</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@example.com</li>";
    echo "<li><strong>Manager with discount:</strong> manager@example.com</li>";
    echo "<li><strong>Manager without discount:</strong> manager2@example.com</li>";
    echo "<li><strong>Client:</strong> client@example.com</li>";
    echo "</ul>";
    echo "<p>Sample packages and services have also been created.</p>";
    echo "<p><a href='index.php' class='btn btn-primary'>Go to homepage</a></p>";
    
} catch(PDOException $e) {
    die("Database setup error: " . $e->getMessage());
}
?>
