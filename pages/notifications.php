<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user's notifications

    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'];
    
    // For admin/manager, show all notifications
    // For client, show only their own notifications
    if ($userRole === 'admin' || $userRole === 'manager') {
        $notifications = $db->query("
            SELECT * FROM notifications WHERE user_id IS NULL
            ORDER BY created_at DESC 
            LIMIT 50
        ");
    } else {
        $notifications = $db->query("
            SELECT * FROM notifications 
            WHERE user_id = ?
            ORDER BY created_at DESC 
            LIMIT 50
        ", [$userId]);
    }
    
    // Mark all as read
    $db->execute("
        UPDATE notifications 
        SET is_read = true 
        WHERE user_id = ? OR (user_id IS NULL AND ? IN ('admin', 'manager'))
    ", [$userId, $userRole]);

?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
    <?php if (!empty($notifications)): ?>
    <a href="../handlers/notifications.php?action=clear_all" class="btn btn-sm btn-danger shadow-sm">
        <i class="fas fa-trash fa-sm text-white-50"></i> Clear All
    </a>
    <?php endif; ?>
</div>

<!-- Notifications -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
        <div class="alert alert-info">
            <p>You don't have any notifications.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notification): ?>
                    <tr class="<?php echo $notification['is_read'] ? '' : 'table-info'; ?>">
                        <td>
                            <?php
                            $icon = 'fa-bell';
                            $badgeClass = 'badge-info';
                            
                            switch ($notification['type']) {
                                case 'booking_created':
                                    $icon = 'fa-calendar-plus';
                                    $badgeClass = 'badge-success';
                                    break;
                                case 'booking_confirmed':
                                    $icon = 'fa-check-circle';
                                    $badgeClass = 'badge-success';
                                    break;
                                case 'booking_cancelled':
                                    $icon = 'fa-times-circle';
                                    $badgeClass = 'badge-danger';
                                    break;
                                case 'booking_completed':
                                    $icon = 'fa-calendar-check';
                                    $badgeClass = 'badge-primary';
                                    break;
                                case 'login':
                                    $icon = 'fa-sign-in-alt';
                                    $badgeClass = 'badge-info';
                                    break;
                                case 'register':
                                    $icon = 'fa-user-plus';
                                    $badgeClass = 'badge-info';
                                    break;
                                case 'guest_rsvp_accepted':
                                    $icon = 'fa-user-check';
                                    $badgeClass = 'badge-success';
                                    break;
                                case 'guest_rsvp_declined':
                                    $icon = 'fa-user-times';
                                    $badgeClass = 'badge-danger';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <i class="fas <?php echo $icon; ?> mr-1"></i>
                                <?php echo ucwords(str_replace('_', ' ', $notification['type'])); ?>
                            </span>
                        </td>
                        <td><?php echo $notification['message']; ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></td>
                        <td>
                            <?php if (isset($notification['link']) && $notification['link']): ?>
                            <a href="<?php echo $notification['link']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php endif; ?>
                            <a href="../handlers/notifications.php?action=delete&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-danger btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mark notifications as read when viewing the page
    $.ajax({
        url: '../handlers/notifications.php?action=mark_read',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Update notification count in header
                updateNotificationCount(response.unreadCount);
            }
        }
    });

    // Function to update notification count in header
    function updateNotificationCount(count) {
        const badgeCounter = $('.badge-counter');
        if (count > 0) {
            badgeCounter.text(count > 9 ? '9+' : count).show();
        } else {
            badgeCounter.hide();
        }
    }

    // Handle delete button clicks
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this notification?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>