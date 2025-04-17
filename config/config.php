<?php
// Application configuration
define('APP_NAME', 'Event Management System');
define('APP_URL', 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:5000'));
define('MOCK_DIR', __DIR__ . '/../mock/'); // Keep for backward compatibility
define('API_URL', 'https://api.example.com'); // Replace with your actual API URL

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Database settings
define('USE_DATABASE', true); // Set to true to use database, false to use mock data

// Include database connection class
require_once __DIR__ . '/database.php';

// Function to read data from database or mock files
function getData($table, $conditions = null, $orderBy = null) {
    if (USE_DATABASE) {
        $db = Database::getInstance();

        $sql = "SELECT * FROM $table";
        $params = [];

        if ($conditions) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = ?";
                $params[] = $value;
            }

            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        return $db->query($sql, $params);
    } else {
        // Fallback to mock data for backward compatibility
        $file = '';
        switch ($table) {
            case 'users':
                $file = 'users.json';
                break;
            case 'services':
                $file = 'services.json';
                break;
            case 'packages':
                $file = 'packages.json';
                break;
            case 'bookings':
                $file = 'bookings.json';
                break;
            case 'guests':
                $file = 'guests.json';
                break;
            default:
                return [];
        }

        return getMockData($file);
    }
}

// Function to get a single record by ID
function getRecordById($table, $id) {
    if (USE_DATABASE) {
        $db = Database::getInstance();
        return $db->queryOne("SELECT * FROM $table WHERE id = ?", [$id]);
    } else {
        // Fallback to mock data for backward compatibility
        $records = getData($table);
        foreach ($records as $record) {
            if ($record['id'] == $id) {
                return $record;
            }
        }
        return null;
    }
}
?>
