<?php
/**
 * Database Tables Creation Script
 * Creates all necessary tables for the application
 */
require_once 'config.php';
require_once 'database.php';

// Get database connection
$db = Database::getInstance();
$connection = $db->getConnection();

// Define SQL to create tables
$tables = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        password_hash VARCHAR(255) NOT NULL,
        address TEXT,
        role VARCHAR(20) NOT NULL,
        can_give_discount BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Services table
    "CREATE TABLE IF NOT EXISTS services (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL
    )",

    // Packages table
    "CREATE TABLE IF NOT EXISTS packages (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image_url VARCHAR(255),
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        customized BOOLEAN DEFAULT FALSE,
        created_by INTEGER REFERENCES users(id),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Package-Service relationship table
    "CREATE TABLE IF NOT EXISTS package_services (
        id SERIAL PRIMARY KEY,
        package_id INTEGER REFERENCES packages(id) ON DELETE CASCADE,
        service_id INTEGER REFERENCES services(id) ON DELETE CASCADE,
        UNIQUE(package_id, service_id)
    )",

    // Bookings table
    "CREATE TABLE IF NOT EXISTS bookings (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        package_id INTEGER REFERENCES packages(id) ON DELETE SET NULL,
        event_place VARCHAR(255),
        event_date DATE,
        discount DECIMAL(10, 2),
        confirmed_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Guests table
    "CREATE TABLE IF NOT EXISTS guests (
        id SERIAL PRIMARY KEY,
        booking_id INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        rsvp_status VARCHAR(20) DEFAULT 'pending',
        last_invited_at TIMESTAMP
    )",
    
    // Notifications table
    "CREATE TABLE IF NOT EXISTS notifications (
        id SERIAL PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        link VARCHAR(255),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

// Begin transaction
$connection->beginTransaction();

// Create tables
foreach ($tables as $sql) {
    $connection->exec($sql);
    echo "Table created successfully.\n";
}

?>