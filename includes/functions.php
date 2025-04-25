<?php
/**
 * General utility functions for Event Planning Platform
 */

/**
 * Gets the currently logged in user ID
 * 
 * @return int|null Current user ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Creates a notification in the system
 * 
 * @param int $userId User ID associated with the notification
 * @param string $type Notification type
 * @param string $message Notification message
 * @param string $link Optional link for the notification
 * @return bool True on success, false on failure
 */
function createNotification($userId, $type, $message, $link = null) {
    try {
        $db = getDBConnection();
        
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, link, created_at, is_read) 
                             VALUES (:user_id, :type, :message, :link, CURRENT_TIMESTAMP, 0)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

// setAlert function moved to beginning of file

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email address
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format currency amount
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatCurrency($amount) {
    return '£' . number_format($amount, 2);
}

/**
 * Format date in a readable format
 * 
 * @param string $dateString Date string
 * @param string $format Format string (default: 'M d, Y h:i A')
 * @return string Formatted date
 */
function formatDate($dateString, $format = 'M d, Y h:i A') {
    $date = new DateTime($dateString);
    return $date->format($format);
}

/**
 * Get dashboard URL based on user role
 * 
 * @return string Dashboard URL
 */
function getDashboardUrl() {
    if (!isset($_SESSION['user_role'])) {
        return '/dashboard/index.php';
    }
    
    switch ($_SESSION['user_role']) {
        case 'administrator':
            return '/dashboard/admin.php';
        case 'manager':
            return '/dashboard/manager.php';
        case 'client':
        default:
            return '/dashboard/client.php';
    }
}

/**
 * Generate a random string
 * 
 * @param int $length Length of string to generate
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charLength - 1)];
    }
    
    return $randomString;
}

/**
 * Upload an image file
 * 
 * @param array $file $_FILES array element
 * @param string $destination Destination folder (default: 'assets/img')
 * @return string|bool File path on success, false on failure
 */
function uploadImage($file, $destination = 'assets/img') {
    // Check if file is a valid upload
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Create destination directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateRandomString() . '.' . $extension;
    $targetPath = $destination . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

/**
 * Get status badge HTML
 * 
 * @param string $status Status value
 * @return string HTML for status badge
 */
function getStatusBadge($status) {
    $badgeClass = '';
    
    switch (strtolower($status)) {
        case 'pending':
            $badgeClass = 'bg-warning';
            break;
        case 'confirmed':
            $badgeClass = 'bg-success';
            break;
        case 'cancelled':
            $badgeClass = 'bg-danger';
            break;
        case 'completed':
            $badgeClass = 'bg-info';
            break;
        default:
            $badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
}

/**
 * Get RSVP status badge HTML
 * 
 * @param string $status RSVP status value
 * @return string HTML for RSVP status badge
 */
function getRsvpBadge($status) {
    $badgeClass = '';
    
    switch (strtolower($status)) {
        case 'pending':
            $badgeClass = 'bg-warning';
            break;
        case 'attending':
            $badgeClass = 'bg-success';
            break;
        case 'declined':
            $badgeClass = 'bg-danger';
            break;
        default:
            $badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
}

/**
 * Get available roles for user assignment
 * 
 * @return array Associative array of roles
 */
function getAvailableRoles() {
    return [
        'client' => 'Client',
        'manager' => 'Manager',
        'administrator' => 'Administrator'
    ];
}

/**
 * Display alert message
 * 
 * @param string $message Message to display
 * @param string $type Alert type (success, danger, warning, info)
 * @return string HTML for alert
 */
function displayAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Set an alert message in session
 * 
 * @param string $type Alert type (success, danger, warning, info)
 * @param string $message Message to display
 */
function setAlert($type, $message) {
    $_SESSION['alert_type'] = $type;
    $_SESSION['alert_message'] = $message;
}

/**
 * Get alert message from session and clear it
 * 
 * @return array|null Alert data or null if no alert
 */
function getAlert() {
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
        $alert = [
            'type' => $_SESSION['alert_type'],
            'message' => $_SESSION['alert_message']
        ];
        
        // Clear alert from session
        unset($_SESSION['alert_type']);
        unset($_SESSION['alert_message']);
        
        return $alert;
    }
    
    return null;
}

// Note: getCurrentUser() is already defined in auth.php

/**
 * Add a notification for a user
 * 
 * @param string $type Notification type
 * @param string $message Notification message
 * @param int $relatedId Related item ID (optional)
 * @param int $userId User ID (default: current user)
 * @return bool Success status
 */
function addNotification($type, $message, $relatedId = null, $userId = null) {
    $db = getDBConnection();
    
    // If no user ID specified, add notification for all admins and managers
    if ($userId === null) {
        if (isLoggedIn()) {
            // Add for current user
            $userId = $_SESSION['user_id'];
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, related_id) 
                                VALUES (:user_id, :type, :message, :related_id)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':related_id', $relatedId);
            $stmt->execute();
        }
        
        // Add for all admins and managers (except current user)
        $stmt = $db->prepare("SELECT id FROM members WHERE role IN ('administrator', 'manager') AND id != :current_user");
        $currentUser = isLoggedIn() ? $_SESSION['user_id'] : 0;
        $stmt->bindParam(':current_user', $currentUser);
        $stmt->execute();
        
        $adminUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($adminUsers as $adminId) {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, related_id) 
                                VALUES (:user_id, :type, :message, :related_id)");
            $stmt->bindParam(':user_id', $adminId);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':related_id', $relatedId);
            $stmt->execute();
        }
        
        return true;
    } else {
        // Add notification for specific user
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, related_id) 
                            VALUES (:user_id, :type, :message, :related_id)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':related_id', $relatedId);
        
        return $stmt->execute();
    }
}

/**
 * Get unread notifications count for current user
 * 
 * @return int Number of unread notifications
 */
function getUnreadNotificationsCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    return (int)$stmt->fetchColumn();
}

/**
 * Format time ago
 * 
 * @param string $datetime Datetime string
 * @return string Formatted time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>