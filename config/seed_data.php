<?php
/**
 * Seed Data Script
 * Populates the database with initial data
 */
require_once 'config.php';
require_once 'database.php';

// Get database connection
$db = Database::getInstance();

// Check if data already exists
$users = $db->query("SELECT COUNT(*) as count FROM users");
if ($users[0]['count'] > 0) {
    echo "Data already exists. Skipping seed.\n";
    exit;
}

// Begin transaction
$db->beginTransaction();

try {
    // Create admin user and get ID
    $adminId = null;
    $adminResult = $db->execute(
        "INSERT INTO users (name, email, phone, password_hash, address, role, can_give_discount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ",
        [
            'Admin User',
            'admin@example.com',
            '123-456-7890',
            password_hash('Admin123', PASSWORD_DEFAULT),
            '123 Admin St, Admin City',
            'admin', 
            'true', // Using string 'true' instead of boolean true
            date('Y-m-d H:i:s')
        ]
    );

    if (is_array($adminResult) && isset($adminResult['id'])) {
        $adminId = $adminResult['id'];
    }

    // Create manager user and get ID
    $managerId = null;
    $managerResult = $db->execute(
        "INSERT INTO users (name, email, phone, password_hash, address, role, can_give_discount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ",
        [
            'Manager User',
            'manager@example.com',
            '234-567-8901',
            password_hash('Manager123', PASSWORD_DEFAULT),
            '456 Manager Ave, Manager Town',
            'manager', 
            'true', // Using string 'true' instead of boolean true
            date('Y-m-d H:i:s')
        ]
    );

    if (is_array($managerResult) && isset($managerResult['id'])) {
        $managerId = $managerResult['id'];
    }

    // Create client user and get ID
    $clientId = null;
    $db->execute(
        "INSERT INTO users (name, email, phone, password_hash, address, role, can_give_discount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ",
        [
            'Client User',
            'client@example.com',
            '345-678-9012',
            password_hash('Client123', PASSWORD_DEFAULT),
            '789 Client Blvd, Client Village',
            'client', 
            'false', // Using string 'false' instead of boolean false
            date('Y-m-d H:i:s')
        ]
    );
    $clientId = $db->lastInsertId();

    // Add services
    $services = [
        ['Photography', 'Professional photography service for your event', 500.00],
        ['Catering', 'Delicious food and beverages for your guests', 1000.00],
        ['DJ Service', 'Music and entertainment for your event', 300.00],
        ['Venue Decoration', 'Beautiful decorations for your event space', 800.00],
        ['Transportation', 'Luxury transportation for the event', 400.00],
        ['Videography', 'Professional video recording of your event', 600.00]
    ];

    foreach ($services as $service) {
        $db->execute(
            "INSERT INTO services (name, description, price) VALUES (?, ?, ?)",
            [$service[0], $service[1], $service[2]]
        );
    }

    // Add packages - using admin ID for created_by
    $packages = [
        ['Basic Wedding Package', '../assets/uploads/680a4f81c0db9_C8D3fLVB.jpeg', 'A simple package for small weddings', 2000.00, 'false', $adminId],
        ['Premium Wedding Package', '../assets/uploads/680a4f8a7c474_GOLD+WEDDING+PACKAGE.jpeg', 'Our most popular wedding package with all essential services', 4000.00, 'false', $adminId],
        ['Destination Wedding Package', '../assets/uploads/680a4f91133d2_Destination-Wedding-e1736780129298.jpg', 'The ultimate wedding experience with premium services', 6000.00, 'false', $adminId],
        ['Corporate Event Package', '../assets/uploads/680a4f9863a83_What-Is-Included-in-A-Typical-Corporate-Event-Package.jpg', 'Perfect for business meetings and corporate events', 3000.00, 'false', $managerId],
        ['Birthday Celebration Package', '../assets/uploads/680a4f9dd5f83_balloon_decor.jpg', 'Make your birthday special with our celebration package', 1500.00, 'false', $managerId]
    ];

    foreach ($packages as $package) {
        $db->execute(
            "INSERT INTO packages (name, image_url, description, price, customized, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$package[0], $package[1], $package[2], $package[3], $package[4], $package[5], date('Y-m-d H:i:s')]
        );
    }

    // Package services relationships
    $packageServices = [
        // Basic Wedding Package (1): Photography, Venue Decoration
        [1, 1], [1, 4],

        // Premium Wedding Package (2): Photography, Catering, DJ, Venue Decoration
        [2, 1], [2, 2], [2, 3], [2, 4],

        // Deluxe Wedding Package (3): All services
        [3, 1], [3, 2], [3, 3], [3, 4], [3, 5], [3, 6],

        // Corporate Event Package (4): Catering, Venue Decoration, Photography
        [4, 2], [4, 4], [4, 1],

        // Birthday Package (5): DJ, Catering, Venue Decoration
        [5, 3], [5, 2], [5, 4]
    ];

    foreach ($packageServices as $ps) {
        $db->execute(
            "INSERT INTO package_services (package_id, service_id) VALUES (?, ?)",
            [$ps[0], $ps[1]]
        );
    }

    // Add bookings using client ID
    $bookings = [
        // Client booking (pending)
        [$clientId, 1, 'Wedding Venue, New York', '2025-06-15', null, null, 'pending'],

        // Client booking (confirmed)
        [$clientId, 2, 'Grand Hall, Los Angeles', '2025-07-22', 200.00, $managerId, 'confirmed'],

        // Another client booking (pending)
        [$clientId, 4, 'Corporate Center, Chicago', '2025-05-10', null, null, 'pending'],

        // Another client booking (confirmed)
        [$clientId, 3, 'Luxury Resort, Miami', '2025-08-05', 500.00, $adminId, 'confirmed'],

        // Another client booking (confirmed)
        [$clientId, 5, 'Community Center, Dallas', '2025-04-30', null, $managerId, 'confirmed']
    ];

    $bookingIds = [];

    foreach ($bookings as $index => $booking) {
        $bookingResult = $db->execute(
            "INSERT INTO bookings (user_id, package_id, event_place, event_date, discount, confirmed_by, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ",
            [$booking[0], $booking[1], $booking[2], $booking[3], $booking[4], $booking[5], $booking[6], date('Y-m-d H:i:s')]
        );

        if ($bookingResult) {
            $bookingId = $db->queryOne("SELECT LAST_INSERT_ID() as id");
            $bookingIds[$index + 1] = $bookingId['id']; // +1 to match the 1-based indexing in the guests array
        }
    }

    // Only add guests if we have booking IDs
    if (!empty($bookingIds)) {
        // Add guests using the booking IDs we got from the database
        $guestData = [
            // Template format: [booking_index, name, email, phone, rsvp_status]
            // For booking 1
            [1, 'John Smith', 'john.smith@example.com', '123-456-7890', 'yes'],
            [1, 'Emily Johnson', 'emily.johnson@example.com', '234-567-8901', 'yes'],
            [1, 'Michael Williams', 'michael.williams@example.com', '345-678-9012', 'no'],
            [1, 'Jessica Brown', 'jessica.brown@example.com', '456-789-0123', 'pending'],

            // For booking 2
            [2, 'David Miller', 'david.miller@example.com', '567-890-1234', 'pending'],

            // For booking 3
            [3, 'Sarah Davis', 'sarah.davis@example.com', '678-901-2345', 'yes'],
            [3, 'James Wilson', 'james.wilson@example.com', '789-012-3456', 'yes'],
            [3, 'Lisa Taylor', 'lisa.taylor@example.com', '890-123-4567', 'no'],

            // For booking 4
            [4, 'Robert Anderson', 'robert.anderson@example.com', '901-234-5678', 'pending'],

            // For booking 5
            [5, 'Jennifer Thomas', 'jennifer.thomas@example.com', '012-345-6789', 'yes']
        ];

        foreach ($guestData as $guest) {
            $bookingIndex = $guest[0];
            if (isset($bookingIds[$bookingIndex])) {
                $db->execute(
                    "INSERT INTO guests (booking_id, name, email, phone, rsvp_status) VALUES (?, ?, ?, ?, ?)",
                    [$bookingIds[$bookingIndex], $guest[1], $guest[2], $guest[3], $guest[4]]
                );
            }
        }
    }

    // Commit transaction
    $db->commit();
    echo "Database seeded successfully!\n";

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    echo "Error seeding database: " . $e->getMessage() . "\n";
}
?>