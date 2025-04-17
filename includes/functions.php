<?php
/**
 * Helper functions for database operations and common tasks
 */

/**
 * Insert a record into a database table
 * 
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs
 * @return int|false The inserted ID on success, false on failure
 */
function insertRecord($table, $data) {
    $db = Database::getInstance();
    
    // Build SQL query
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    // Execute query
    $result = $db->execute($sql, array_values($data));
    
    if ($result) {
        // Get the last insert ID
        $lastId = $db->lastInsertId();
        return $lastId;
    }
    
    return false;
}

/**
 * Update a record in a database table
 * 
 * @param string $table The table name
 * @param int $id The ID of the record to update
 * @param array $data Associative array of column => value pairs
 * @return bool True on success, false on failure
 */
function updateRecord($table, $id, $data) {
    $db = Database::getInstance();
    
    // Build SQL query
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "$column = ?";
    }
    $setClause = implode(', ', $setParts);
    
    $sql = "UPDATE $table SET $setClause WHERE id = ?";
    
    // Add ID to values array
    $values = array_values($data);
    $values[] = $id;
    
    // Execute query
    return $db->execute($sql, $values);
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Get mock data from a JSON file
 * 
 * @param string $filename The JSON file name
 * @return array The data from the JSON file
 */
function getMockData($filename) {
    $filePath = '../mock/' . $filename;
    
    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        return json_decode($json, true) ?? [];
    }
    
    return [];
}

/**
 * Save mock data to a JSON file
 * 
 * @param string $filename The JSON file name
 * @param array $data The data to save
 * @return bool True on success, false on failure
 */
function saveMockData($filename, $data) {
    $filePath = '../mock/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json) !== false;
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the random string
 * @return string The random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Hash a password
 * 
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password The password to verify
 * @param string $hash The hash to verify against
 * @return bool True if the password matches the hash, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Send an email
 * 
 * @param string $to The recipient email address
 * @param string $subject The email subject
 * @param string $message The email message
 * @param array $headers Additional email headers
 * @return bool True on success, false on failure
 */
function sendEmail($to, $subject, $message, $headers = []) {
    // In a real application, you would use a proper email library like PHPMailer
    // For the demo, we'll just simulate success
    return true;
}

/**
 * Format a date
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Check if a user has a specific permission
 * 
 * @param string $permission The permission to check
 * @return bool True if the user has the permission, false otherwise
 */
function hasPermission($permission) {
    // In a real application, you would check the user's permissions
    // For the demo, we'll use a simple role-based check
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    switch ($permission) {
        case 'manage_users':
            return $_SESSION['user_role'] === 'admin';
        case 'manage_bookings':
            return $_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager';
        case 'view_dashboard':
            return true; // All logged-in users can view dashboard
        default:
            return false;
    }
}
?>