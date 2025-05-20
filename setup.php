<?php
// Initial setup script to create the database and tables

// Include database configuration
require_once 'includes/config.php';

try {
    // Create a new PDO instance
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $db = new PDO($dsn, DB_USER, DB_PASS);
    
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255)  NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT "client",
        can_apply_discount TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )');
    
    // Create services table
    $db->exec('CREATE TABLE IF NOT EXISTS services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )');
    
    // Create packages table
    $db->exec('CREATE TABLE IF NOT EXISTS packages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255),
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )');
    
    // Create package_services junction table
    $db->exec('CREATE TABLE IF NOT EXISTS package_services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        package_id INT,
        service_id INT,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )');
    
    // Create bookings table
    $db->exec('CREATE TABLE IF NOT EXISTS bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        package_id INT,
        event_date DATE NOT NULL,
        event_location VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT "pending",
        total_price DECIMAL(10,2) NOT NULL,
        discount_applied DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
    )');
    
    // Create booking_services for custom packages
    $db->exec('CREATE TABLE IF NOT EXISTS booking_services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT,
        service_id INT,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )');
    
    // Create guests table
    $db->exec('CREATE TABLE IF NOT EXISTS guests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(50),
        rsvp_status VARCHAR(50) DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )');
    
    // Create notifications table
    $db->exec('CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT DEFAULT 0,
        related_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    
    // Insert default users (password: password123)
    $adminPassword = password_hash('Admin123', PASSWORD_DEFAULT);
    $managerPassword = password_hash('Manager123', PASSWORD_DEFAULT);
    $clientPassword = password_hash('Client123', PASSWORD_DEFAULT);

    // Admin user
    $stmt = $db->prepare('INSERT IGNORE INTO users (name, email, password, role, can_apply_discount) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['Admin User', 'admin@example.com', $adminPassword, 'administrator', 1]);
    
    // Manager user with discount permission
    $stmt->execute(['Manager User', 'manager@example.com', $managerPassword, 'manager', 1]);
    
    // Manager without discount permission
    $stmt->execute(['Manager No Discount', 'manager2@example.com', $managerPassword, 'manager', 0]);
    
    // Client user
    $stmt->execute(['Client User', 'client@example.com', $clientPassword, 'client', 0]);
    
    // Insert sample packages
    $packageStmt = $db->prepare('INSERT IGNORE INTO packages (name, description, price, image_path) VALUES (?, ?, ?, ?)');
    $packageStmt->execute(['Basic Wedding', 'Essential wedding services including photographer, basic decoration, and music.', 999.99, 'default_package.jpg']);
    $packageStmt->execute(['Premium Wedding', 'Premium wedding package with professional photography, videography, gourmet catering, and elegant decorations.', 2499.99, 'default_package.jpg']);
    $packageStmt->execute(['Birthday Party', 'Fun-filled birthday party package with decorations, entertainment, and catering.', 399.99, 'default_package.jpg']);
    $packageStmt->execute(['Corporate Event', 'Professional corporate event solution with A/V equipment, catering, and venue decoration.', 1499.99, 'default_package.jpg']);
    
    // Insert sample services
    $serviceStmt = $db->prepare('INSERT IGNORE INTO services (name, description, price) VALUES (?, ?, ?)');
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
