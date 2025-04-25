<?php
/**
 * Notifications Page
 * 
 * View and manage notifications for the user
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get database connection
$db = getDBConnection();

// Process actions
if (isset($_GET['action']) && $_GET['action'] === 'mark_read') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        // Mark single notification as read
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
    } else {
        // Mark all notifications as read
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
    }
    
    setAlert('success', 'Notifications marked as read');
    header('Location: notifications.php');
    exit;
}

// Get all notifications for current user
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$pageTitle = 'Notifications';

// Include header
require_once '../templates/header.php';
?>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
            <?php if (!empty($notifications)): ?>
                <a href="notifications.php?action=mark_read" class="btn btn-outline-primary">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <div class="col-12">
            <?php if (empty($notifications)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> You have no notifications.
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'list-group-item-primary'; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <?php 
                                    $type = $notification['type'];
                                    $icon = '';
                                    
                                    switch ($type) {
                                        case 'new_booking':
                                            $icon = '<i class="fas fa-calendar-plus text-primary"></i>';
                                            break;
                                        case 'booking_confirmed':
                                            $icon = '<i class="fas fa-calendar-check text-success"></i>';
                                            break;
                                        case 'booking_cancelled':
                                            $icon = '<i class="fas fa-calendar-times text-danger"></i>';
                                            break;
                                        case 'booking_completed':
                                            $icon = '<i class="fas fa-calendar-check text-info"></i>';
                                            break;
                                        case 'login':
                                            $icon = '<i class="fas fa-sign-in-alt text-info"></i>';
                                            break;
                                        case 'register':
                                            $icon = '<i class="fas fa-user-plus text-success"></i>';
                                            break;
                                        case 'guest_accepted':
                                            $icon = '<i class="fas fa-user-check text-success"></i>';
                                            break;
                                        case 'guest_rejected':
                                            $icon = '<i class="fas fa-user-times text-danger"></i>';
                                            break;
                                        default:
                                            $icon = '<i class="fas fa-bell text-primary"></i>';
                                    }
                                    
                                    echo $icon . ' ' . ucfirst(str_replace('_', ' ', $type));
                                    ?>
                                </h5>
                                <small><?php echo timeAgo($notification['created_at']); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if ($notification['related_id']): ?>
                                    <?php
                                    $link = '#';
                                    $text = 'View';
                                    
                                    switch ($notification['type']) {
                                        case 'new_booking':
                                        case 'booking_confirmed':
                                        case 'booking_cancelled':
                                        case 'booking_completed':
                                            $link = 'bookings.php?action=edit&id=' . $notification['related_id'];
                                            $text = 'View Booking';
                                            break;
                                        case 'register':
                                            $link = 'users.php?action=edit&id=' . $notification['related_id'];
                                            $text = 'View User';
                                            break;
                                        case 'guest_accepted':
                                        case 'guest_rejected':
                                            $link = 'guests.php?action=edit&id=' . $notification['related_id'];
                                            $text = 'View Guest';
                                            break;
                                    }
                                    ?>
                                    <a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> <?php echo $text; ?>
                                    </a>
                                <?php else: ?>
                                    <span>&nbsp;</span>
                                <?php endif; ?>
                                
                                <?php if (!$notification['is_read']): ?>
                                    <a href="notifications.php?action=mark_read&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>