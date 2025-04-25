<?php
require_once 'config.php';

/**
 * Display an alert message
 * 
 * @param string $message The message to display
 * @param string $type The type of alert (success, danger, warning, info)
 * @return string HTML for the alert
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Get flash messages and clear them from session
 * 
 * @return string HTML for flash messages
 */
function getFlashMessages() {
    if (!isset($_SESSION['flash_messages'])) {
        return '';
    }
    
    $output = '';
    foreach ($_SESSION['flash_messages'] as $message) {
        $output .= showAlert($message['message'], $message['type']);
    }
    
    // Clear flash messages
    unset($_SESSION['flash_messages']);
    
    return $output;
}

/**
 * Set a flash message to be displayed on the next page load
 * 
 * @param string $message The message to display
 * @param string $type The type of alert (success, danger, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Validate and sanitize input
 * 
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format price with currency symbol
 * 
 * @param float $price The price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return '£' . number_format($price, 2);
}

/**
 * Format date in a human-readable format
 * 
 * @param string $date Date string
 * @param string $format Format string (default: 'F j, Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Upload an image file
 * 
 * @param array $file File from $_FILES
 * @param string $destination Destination directory
 * @return string|false Path to uploaded file or false on failure
 */
function uploadImage($file, $destination = UPLOADS_DIR) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateRandomString() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Move the uploaded file to the destination
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Get the role name for display
 * 
 * @param string $role Role identifier
 * @return string Human-readable role name
 */
function getRoleName($role) {
    switch ($role) {
        case ROLE_ADMIN:
            return 'Administrator';
        case ROLE_MANAGER:
            return 'Manager';
        case ROLE_CLIENT:
            return 'Client';
        default:
            return ucfirst($role);
    }
}

/**
 * Get the status name for display
 * 
 * @param string $status Status identifier
 * @return string Human-readable status name
 */
function getStatusName($status) {
    switch ($status) {
        case STATUS_PENDING:
            return 'Pending';
        case STATUS_CONFIRMED:
            return 'Confirmed';
        case STATUS_CANCELLED:
            return 'Cancelled';
        case STATUS_COMPLETED:
            return 'Completed';
        default:
            return ucfirst($status);
    }
}

/**
 * Get the RSVP status name for display
 * 
 * @param string $status RSVP status identifier
 * @return string Human-readable RSVP status name
 */
function getRsvpStatusName($status) {
    switch ($status) {
        case RSVP_PENDING:
            return 'Pending';
        case RSVP_ACCEPTED:
            return 'Accepted';
        case RSVP_DECLINED:
            return 'Declined';
        default:
            return ucfirst($status);
    }
}

/**
 * Get the status badge HTML
 * 
 * @param string $status Status identifier
 * @return string HTML for the status badge
 */
function getStatusBadge($status) {
    $badgeClass = '';
    
    switch ($status) {
        case STATUS_PENDING:
            $badgeClass = 'bg-warning';
            break;
        case STATUS_CONFIRMED:
            $badgeClass = 'bg-primary';
            break;
        case STATUS_CANCELLED:
            $badgeClass = 'bg-danger';
            break;
        case STATUS_COMPLETED:
            $badgeClass = 'bg-success';
            break;
        case RSVP_PENDING:
            $badgeClass = 'bg-warning';
            break;
        case RSVP_ACCEPTED:
            $badgeClass = 'bg-success';
            break;
        case RSVP_DECLINED:
            $badgeClass = 'bg-danger';
            break;
        default:
            $badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
}

/**
 * Get pagination HTML
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @return string HTML for pagination
 */
function getPagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $output = '<nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $output .= '<li class="page-item">
                    <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>';
    } else {
        $output .= '<li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $startPage + 4);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $output .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $output .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $output .= '<li class="page-item">
                    <a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>';
    } else {
        $output .= '<li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>';
    }
    
    $output .= '</ul></nav>';
    
    return $output;
}
?>
