<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

// Require login for all actions
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$notificationController = new NotificationController();

// Get the current user
$user = getCurrentUser();

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle notifications actions (markAsRead, markAllAsRead)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle mark as read action
    if (isset($_POST['mark_as_read']) && isset($_POST['notification_id'])) {
        $result = $notificationController->markAsRead($_POST['notification_id']);
        if ($result) {
            setFlashMessage('Notification marked as read.', 'success');
        } else {
            setFlashMessage('Failed to mark notification as read.', 'danger');
        }
        header('Location: ' . APP_URL . '/dashboard/notifications.php');
        exit;
    }
    
    // Handle mark all as read action
    if (isset($_POST['mark_all_as_read'])) {
        $result = $notificationController->markAllAsRead($user['id']);
        if ($result) {
            setFlashMessage('All notifications marked as read.', 'success');
        } else {
            setFlashMessage('Failed to mark all notifications as read.', 'danger');
        }
        header('Location: ' . APP_URL . '/dashboard/notifications.php');
        exit;
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'mark_as_read' && isset($_POST['notification_id'])) {
            $result = $notificationController->markAsRead($_POST['notification_id']);
            echo json_encode(['success' => $result]);
            exit;
        }
        
        if ($_POST['action'] === 'mark_all_as_read') {
            $result = $notificationController->markAllAsRead($user['id']);
            echo json_encode(['success' => $result]);
            exit;
        }
    }
    
    // Return error for invalid requests
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get page and limit from URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Get unread only filter
$unreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';

// Get user notifications
$result = $notificationController->getUserNotifications($user['id'], $page, $perPage, $unreadOnly);
$notifications = $result['notifications'];
$pagination = $result['pagination'];

// Get unread count
$unreadCount = $notificationController->countUnread($user['id']);

// Set page title and sidebar flag for template
$title = 'Notifications';
$showSidebar = true;

// Include header
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Notifications</h1>
        
        <div>
            <?php if ($unreadCount > 0): ?>
                <form method="POST" action="" class="d-inline">
                    <input type="hidden" name="mark_all_as_read" value="1">
                    <button type="submit" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($unreadOnly): ?>
                <a href="<?php echo APP_URL; ?>/dashboard/notifications.php" class="btn btn-outline-primary">
                    <i class="fas fa-list me-1"></i>Show All
                </a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/dashboard/notifications.php?unread=1" class="btn btn-outline-primary">
                    <i class="fas fa-bell me-1"></i>Show Unread
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">
                        <?php echo $unreadOnly ? 'Unread Notifications' : 'All Notifications'; ?>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?> unread</span>
                        <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="text-center p-4">
                    <p class="mb-0">No notifications found.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action d-flex align-items-center p-3 <?php echo $notification['is_read'] ? '' : 'list-group-item-light'; ?>">
                            <div class="flex-shrink-0 me-3">
                                <div class="notification-icon bg-<?php echo $notificationController->getTypeColor($notification['type']); ?>">
                                    <i class="<?php echo $notificationController->getTypeIcon($notification['type']); ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($notification['message']); ?></h6>
                                    <small class="text-muted"><?php echo timeAgo($notification['created_at']); ?></small>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <div class="mt-2">
                                        <form method="POST" action="">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" name="mark_as_read" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-check me-1"></i>Mark as Read
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($pagination['total'] > 1): ?>
            <div class="card-footer">
                <?php echo getPagination($pagination['current'], $pagination['total'], $unreadOnly ? '/dashboard/notifications.php?unread=1&page=' : '/dashboard/notifications.php?page='); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>