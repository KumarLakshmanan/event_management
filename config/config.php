<?php
// Application configuration
define('APP_NAME', 'Event Management System');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('MOCK_DIR', __DIR__ . '/../mock/');
define('API_URL', 'https://api.example.com'); // Replace with your actual API URL

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Function to read mock data
function getMockData($file) {
    $filePath = MOCK_DIR . $file;
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true);
    }
    return [];
}

// Function to write mock data
function saveMockData($file, $data) {
    $filePath = MOCK_DIR . $file;
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $jsonData);
}

// Function to generate a unique ID
function generateId() {
    return uniqid();
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
