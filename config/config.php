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

// Function to insert a record
function insertRecord($table, $data) {
    if (USE_DATABASE) {
        $db = Database::getInstance();

        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $params = array_values($data);

        $result = $db->queryOne($sql, $params);
        return $result ? $result['id'] : false;
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
                return false;
        }

        $records = getMockData($file);
        $id = count($records) > 0 ? max(array_column($records, 'id')) + 1 : 1;
        $data['id'] = $id;
        $records[] = $data;
        saveMockData($file, $records);
        return $id;
    }
}

// Function to update a record
function updateRecord($table, $id, $data) {
    if (USE_DATABASE) {
        $db = Database::getInstance();

        $setClauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $params[] = $value;
        }

        $setClause = implode(', ', $setClauses);
        $params[] = $id;

        $sql = "UPDATE $table SET $setClause WHERE id = ?";

        return $db->execute($sql, $params);
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
                return false;
        }

        $records = getMockData($file);
        $updated = false;

        foreach ($records as $index => $record) {
            if ($record['id'] == $id) {
                foreach ($data as $key => $value) {
                    $records[$index][$key] = $value;
                }
                $updated = true;
                break;
            }
        }

        if ($updated) {
            saveMockData($file, $records);
            return true;
        }

        return false;
    }
}

// Function to delete a record
function deleteRecord($table, $id) {
    if (USE_DATABASE) {
        $db = Database::getInstance();
        return $db->execute("DELETE FROM $table WHERE id = ?", [$id]);
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
                return false;
        }

        $records = getMockData($file);
        $recordIndex = -1;

        foreach ($records as $index => $record) {
            if ($record['id'] == $id) {
                $recordIndex = $index;
                break;
            }
        }

        if ($recordIndex !== -1) {
            array_splice($records, $recordIndex, 1);
            saveMockData($file, $records);
            return true;
        }

        return false;
    }
}

// For backward compatibility
function getMockData($file) {
    $filePath = MOCK_DIR . $file;
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true);
    }
    return [];
}

// For backward compatibility
function saveMockData($file, $data) {
    $filePath = MOCK_DIR . $file;
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $jsonData);
}

// Function to check user role
function hasRole($role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }

    if ($role === 'admin') {
        return $_SESSION['user_role'] === 'admin';
    } else if ($role === 'manager') {
        return $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager';
    } else if ($role === 'client') {
        return $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager' || $_SESSION['user_role'] === 'client';
    }

    return false;
}

// Function to check if user can give discount
function canGiveDiscount() {
    return isset($_SESSION['can_give_discount']) && $_SESSION['can_give_discount'] === true;
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
