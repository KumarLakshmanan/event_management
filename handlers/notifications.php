<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Get the requested action
$action = sanitizeInput($_GET['action'] ?? '');

switch ($action) {
    case 'delete':
        handleDelete();
        break;
    case 'clear_all':
        handleClearAll();
        break;
    case 'mark_read':
        handleMarkRead();
        break;
    default:
        header("Location: ../pages/notifications.php");
        exit;
}

/**
 * Handle notification deletion
 */
function handleDelete()
{
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id) {
        setFlashMessage('Invalid notification ID', 'danger');
        header("Location: ../pages/notifications.php");
        exit;
    }

    // Get user info
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'];

    // Delete notification from database
    $db = Database::getInstance();

    // Get notification to verify permission
    $notification = $db->querySingle(
        "SELECT * FROM notifications WHERE id = ?",
        [$id]
    );

    if (!$notification) {
        setFlashMessage('Notification not found', 'danger');
        header("Location: ../pages/notifications.php");
        exit;
    }

    // Verify permission - users can only delete their own notifications
    // Admins/managers can delete any notification
    if ($userRole !== 'admin' && $userRole !== 'manager' && $notification['user_id'] != $userId) {
        setFlashMessage('Permission denied', 'danger');
        header("Location: ../pages/notifications.php");
        exit;
    }

    // Delete notification
    $result = $db->execute("DELETE FROM notifications WHERE id = ?", [$id]);

    if ($result) {
        setFlashMessage('Notification deleted successfully', 'success');
    } else {
        setFlashMessage('Failed to delete notification', 'danger');
    }

    header("Location: ../pages/notifications.php");
    exit;
}

/**
 * Handle clearing all notifications
 */
function handleClearAll()
{
    // Get user info
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'];

    // Clear all notifications from database
    $db = Database::getInstance();

    // For admin/manager, can clear all notifications
    // For client, can only clear own notifications
    if ($userRole === 'admin' || $userRole === 'manager') {
        $result = $db->execute("DELETE FROM notifications");
    } else {
        $result = $db->execute("DELETE FROM notifications WHERE user_id = ?", [$userId]);
    }

    if ($result) {
        setFlashMessage('All notifications cleared successfully', 'success');
    } else {
        setFlashMessage('Failed to clear notifications', 'danger');
    }

    header("Location: ../pages/notifications.php");
    exit;
}

/**
 * Handle marking notifications as read
 */
function handleMarkRead()
{
    // Get user info
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'];

    // Update notifications in database
    $db = Database::getInstance();

    // For admin/manager, mark all notifications as read
    // For client, mark only their notifications as read
    if ($userRole === 'admin' || $userRole === 'manager') {
        $result = $db->execute("
            UPDATE notifications 
            SET is_read = true 
            WHERE user_id IS NULL
        ");
    } else {
        $result = $db->execute("
            UPDATE notifications 
            SET is_read = true 
            WHERE user_id = ?
        ", [$userId]);
    }

    // Get updated unread count
    $unreadCount = getUnreadNotificationsCount();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'unreadCount' => $unreadCount
    ]);
    exit;
}
