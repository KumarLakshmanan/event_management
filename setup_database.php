<?php
/**
 * Database Setup Script for Event Planning Platform
 * 
 * This script creates the SQLite database schema and seeds initial data
 * Execute this script to initialize a new database or reset an existing one
 */

// Include configuration
require_once 'includes/config.php';

// Process only if form is submitted or parameters provided
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : null);
$reset = $action === 'reset';
$confirm = $action === 'confirm';
$seed = $action === 'seed';

// Check if database file exists
$databaseExists = file_exists(DB_PATH);

// Start collecting output
ob_start();
$messages = [];
$errors = [];
$tables_created = [];
$tables_exist = false;

// Don't run if no action and database exists
if ($databaseExists && !$action) {
    $confirm = false;
} else if (!$databaseExists) {
    // If database doesn't exist, create it
    $confirm = true;
}

// Remove existing database if reset requested
if ($databaseExists && $reset) {
    if (unlink(DB_PATH)) {
        $messages[] = "Existing database removed successfully.";
        $confirm = true; // Proceed with database creation
    } else {
        $errors[] = "Failed to remove existing database. Please check file permissions.";
    }
}

// Create/update database if confirmed
if ($confirm || $seed) {
    try {
        // Create new PDO connection
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON');
        
        if (!$databaseExists || $reset) {
            $messages[] = "Database file created successfully.";
        }
        
        // Create tables
        $tables = [
            // Members (users) table
            "CREATE TABLE IF NOT EXISTS members (
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
            "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL
            )",
            
            // Bundles (packages) table
            "CREATE TABLE IF NOT EXISTS bundles (
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
            "CREATE TABLE IF NOT EXISTS bundle_products (
                bundle_id INTEGER,
                product_id INTEGER,
                PRIMARY KEY (bundle_id, product_id),
                FOREIGN KEY (bundle_id) REFERENCES bundles(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )",
            
            // Reservations (bookings) table
            "CREATE TABLE IF NOT EXISTS reservations (
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
            "CREATE TABLE IF NOT EXISTS attendees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER,
                name VARCHAR(100),
                email VARCHAR(150),
                phone VARCHAR(20),
                rsvp_status VARCHAR(10) DEFAULT 'pending',
                rsvp_token VARCHAR(64),
                FOREIGN KEY (booking_id) REFERENCES reservations(id) ON DELETE CASCADE
            )",
            
            // AttendanceRecords (event_attendance) table
            "CREATE TABLE IF NOT EXISTS attendance_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                guest_id INTEGER,
                attended BOOLEAN DEFAULT 0,
                checked_in_at TIMESTAMP,
                remarks TEXT,
                FOREIGN KEY (guest_id) REFERENCES attendees(id) ON DELETE CASCADE
            )",
            
            // Notifications table
            "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT 0,
                related_id INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES members(id) ON DELETE CASCADE
            )"
        ];
        
        // Execute all table creation queries
        foreach ($tables as $query) {
            $db->exec($query);
            $table_name = substr($query, strpos($query, "CREATE TABLE IF NOT EXISTS") + 27, strpos($query, " (") - strpos($query, "CREATE TABLE IF NOT EXISTS") - 27);
            $tables_created[] = $table_name;
        }
        
        // Check if tables already had data
        $stmt = $db->query("SELECT count(*) FROM sqlite_master WHERE type='table'");
        $tables_count = $stmt->fetchColumn();
        
        if ($tables_count > 0) {
            $tables_exist = true;
        }
        
        // Check if admin user exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE email = :email AND role = 'administrator'");
        $stmt->bindValue(':email', 'admin@example.com');
        $stmt->execute();
        $adminExists = (int)$stmt->fetchColumn();
        
        if (!$adminExists) {
            // Create default admin user
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO members (name, email, password, role) 
                      VALUES ('Admin User', 'admin@example.com', :password, 'administrator')");
            $stmt->bindParam(':password', $adminPassword);
            $stmt->execute();
            $messages[] = "Default admin user created (Email: admin@example.com, Password: admin123)";
        } else {
            $messages[] = "Admin user already exists";
        }
        
        // Seed sample data if requested
        if ($seed) {
            // First check if products table is empty
            $stmt = $db->query("SELECT COUNT(*) FROM products");
            $productsCount = (int)$stmt->fetchColumn();
            
            if ($productsCount == 0) {
                // Sample services
                $services = [
                    ['Catering Service', 'Professional catering for events', 1500.00],
                    ['Photography', 'Event photography with digital copies', 800.00],
                    ['Venue Decoration', 'Complete decoration package', 1200.00],
                    ['DJ & Sound System', 'Professional DJ with equipment', 600.00],
                    ['Invitation Cards', 'Custom designed invitations', 300.00],
                    ['Videography', 'Professional video recording and editing', 1000.00],
                    ['Floral Arrangements', 'Custom floral designs', 500.00],
                    ['Transportation', 'Luxury vehicles for the event', 700.00]
                ];
                
                $serviceInsert = $db->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
                
                foreach ($services as $service) {
                    $serviceInsert->execute($service);
                }
                
                $messages[] = "Sample services added";
            } else {
                $messages[] = "Services table already contains data, skipping service seeding";
            }
            
            // Check if bundles table is empty
            $stmt = $db->query("SELECT COUNT(*) FROM bundles");
            $bundlesCount = (int)$stmt->fetchColumn();
            
            if ($bundlesCount == 0) {
                // Sample packages
                $packages = [
                    ['Wedding Basic', 3000.00, 'Essential wedding services for your special day.', 1, 0],
                    ['Corporate Event', 2500.00, 'Complete corporate event package for professional gatherings.', 1, 0],
                    ['Birthday Deluxe', 1800.00, 'All-inclusive birthday celebration package.', 1, 0]
                ];
                
                $packageInsert = $db->prepare("INSERT INTO bundles (name, price, description, created_by, customized) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($packages as $package) {
                    $packageInsert->execute($package);
                    $packageId = $db->lastInsertId();
                    
                    // Get random services for each package
                    $stmt = $db->query("SELECT id FROM products ORDER BY RANDOM() LIMIT 3");
                    $serviceIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $bundleProductInsert = $db->prepare("INSERT INTO bundle_products (bundle_id, product_id) VALUES (?, ?)");
                    
                    foreach ($serviceIds as $serviceId) {
                        $bundleProductInsert->execute([$packageId, $serviceId]);
                    }
                }
                
                $messages[] = "Sample packages added";
            } else {
                $messages[] = "Packages table already contains data, skipping package seeding";
            }
            
            // Check if there are any client or manager users
            $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE role = :role1 OR role = :role2");
            $stmt->bindValue(':role1', 'client');
            $stmt->bindValue(':role2', 'manager');
            $stmt->execute();
            $usersCount = (int)$stmt->fetchColumn();
            
            if ($usersCount == 0) {
                // Sample client users
                $users = [
                    ['John Doe', 'john@example.com', 'client123', 'client', '555-123-4567'],
                    ['Jane Smith', 'jane@example.com', 'client123', 'client', '555-987-6543'],
                    ['Robert Manager', 'robert@example.com', 'manager123', 'manager', '555-456-7890']
                ];
                
                $userInsert = $db->prepare("INSERT INTO members (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($users as $user) {
                    $user[2] = password_hash($user[2], PASSWORD_DEFAULT);
                    $userInsert->execute($user);
                }
                
                $messages[] = "Sample users added";
            } else {
                $messages[] = "Users already exist, skipping user seeding";
            }
        }
        
    } catch (PDOException $e) {
        $errors[] = "Database setup failed: " . $e->getMessage();
    }
}

$output = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .table-list {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        .table-name {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .message-list {
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container setup-container">
        <h1 class="mb-4">
            <i class="bi bi-database"></i> 
            Database Setup
        </h1>
        
        <p class="lead"><?php echo APP_NAME; ?> database initialization tool</p>
        
        <hr>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Errors Occurred</h4>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($messages)): ?>
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Setup Progress</h4>
                <ul class="mb-0 message-list">
                    <?php foreach ($messages as $message): ?>
                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($tables_created)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-table"></i> Database Tables
                </div>
                <div class="card-body">
                    <div class="table-list">
                        <?php foreach ($tables_created as $table): ?>
                            <span class="table-name">
                                <i class="bi bi-check2-circle text-success"></i> <?php echo htmlspecialchars($table); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($confirm || $seed): ?>
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">Setup Complete!</h4>
                <p>Your database has been set up successfully.</p>
                <hr>
                <p class="mb-0">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house-door-fill"></i> Go to Homepage
                    </a>
                    <?php if (!$seed): ?>
                        <a href="setup_database.php?action=seed" class="btn btn-outline-primary">
                            <i class="bi bi-database-fill-add"></i> Add Sample Data
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear-fill"></i> Setup Options
                </div>
                <div class="card-body">
                    <?php if ($databaseExists): ?>
                        <p>A database file already exists at <code><?php echo htmlspecialchars(DB_PATH); ?></code>.</p>
                        <p>Choose one of the following options:</p>
                        
                        <form method="post" class="action-buttons">
                            <button type="submit" name="action" value="confirm" class="btn btn-primary">
                                <i class="bi bi-database-check"></i> Update Existing Database
                            </button>
                            <button type="submit" name="action" value="reset" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to reset the database? All existing data will be lost!')">
                                <i class="bi bi-database-x"></i> Reset Database
                            </button>
                            <button type="submit" name="action" value="seed" class="btn btn-success">
                                <i class="bi bi-database-fill-add"></i> Add Sample Data
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </form>
                    <?php else: ?>
                        <p>No database file exists yet. Click the button below to create a new database.</p>
                        <form method="post" class="action-buttons">
                            <button type="submit" name="action" value="confirm" class="btn btn-primary">
                                <i class="bi bi-database-add"></i> Create Database
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>