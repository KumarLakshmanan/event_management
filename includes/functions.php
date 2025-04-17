<?php
/**
 * Helper functions for database operations and common tasks
 */

// Constants for notification types
define('NOTIFICATION_INFO', 'info');
define('NOTIFICATION_SUCCESS', 'success');
define('NOTIFICATION_WARNING', 'warning');
define('NOTIFICATION_DANGER', 'danger');

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
        case 'give_discount':
            // Only admins and managers with the can_give_discount flag can give discounts
            if ($_SESSION['user_role'] === 'admin') {
                return true;
            }
            if ($_SESSION['user_role'] === 'manager' && isset($_SESSION['can_give_discount']) && $_SESSION['can_give_discount']) {
                return true;
            }
            return false;
        default:
            return false;
    }
}

/**
 * Set flash message in session
 * 
 * @param string $message The message to display
 * @param string $type The message type (success, info, warning, danger)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get count of unread notifications for current user
 * 
 * @return int Number of unread notifications
 */
function getUnreadNotificationsCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? '';
    
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // For admin/manager, count all unread notifications
        // For client, count only their own unread notifications
        if ($userRole === 'admin' || $userRole === 'manager') {
            $result = $db->querySingle("
                SELECT COUNT(*) as count FROM notifications 
                WHERE is_read = false
            ");
        } else {
            $result = $db->querySingle("
                SELECT COUNT(*) as count FROM notifications 
                WHERE is_read = false AND (user_id = ? OR user_id IS NULL)
            ", [$userId]);
        }
        
        return $result['count'] ?? 0;
    } else {
        // Fallback to mock data
        $notifications = getMockData('notifications.json');
        $count = 0;
        
        // For admin/manager, count all unread notifications
        // For client, count only their own unread notifications
        foreach ($notifications as $notification) {
            if ($notification['is_read'] === false) {
                if ($userRole === 'admin' || $userRole === 'manager' || 
                    $notification['user_id'] == $userId || $notification['user_id'] === null) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}

/**
 * Calculate total price from package and services
 * 
 * @param array $package Package data
 * @param array $selectedServices Array of selected service IDs
 * @return float Total price
 */
function calculatePackagePrice($package, $selectedServices = []) {
    // If no services selected or not a customized package, return the package price
    if (empty($selectedServices) || !isset($package['customized']) || !$package['customized']) {
        return floatval($package['price']);
    }
    
    // Get services data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get services for the selected IDs
        $placeholders = implode(', ', array_fill(0, count($selectedServices), '?'));
        $services = $db->query("
            SELECT * FROM services 
            WHERE id IN ($placeholders)
        ", $selectedServices);
    } else {
        // Fallback to mock data
        $allServices = getMockData('services.json');
        $services = array_filter($allServices, function($service) use ($selectedServices) {
            return in_array($service['id'], $selectedServices);
        });
    }
    
    // Calculate total price
    $totalPrice = 0;
    foreach ($services as $service) {
        $totalPrice += floatval($service['price']);
    }
    
    return $totalPrice;
}
?>