<?php
/**
 * General utility functions for Event Planning Platform
 */

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
    return '$' . number_format($amount, 2);
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
?>