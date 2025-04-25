<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

// Require login for all actions
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$notificationController = new NotificationController();

// Set response header
header('Content-Type: application/json');

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID
    $userId = $_SESSION['user']['id'];
    
    // Handle different actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_as_read':
                if (isset($_POST['notification_id'])) {
                    $result = $notificationController->markAsRead($_POST['notification_id']);
                    echo json_encode(['success' => $result]);
                    exit;
                }
                break;
                
            case 'mark_all_as_read':
                $result = $notificationController->markAllAsRead($userId);
                echo json_encode(['success' => $result]);
                exit;
                
            case 'count_unread':
                $count = $notificationController->countUnread($userId);
                echo json_encode(['success' => true, 'count' => $count]);
                exit;
                
            case 'get_recent':
                $result = $notificationController->getUserNotifications($userId, 1, 5, true);
                echo json_encode([
                    'success' => true, 
                    'notifications' => $result['notifications'],
                    'count' => count($result['notifications'])
                ]);
                exit;
        }
    }
}

// If we get here, the request was invalid
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
?>