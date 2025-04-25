<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';

class NotificationController {
    private $notificationModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->notificationModel = new Notification();
        $this->userModel = new User();
    }
    
    /**
     * Get notifications for the current user
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param bool $unreadOnly Only get unread notifications
     * @return array Notifications and pagination info
     */
    public function getUserNotifications($userId, $page = 1, $perPage = 20, $unreadOnly = false) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get notifications
        $notifications = $this->notificationModel->getByUserId($userId, $perPage, $offset, $unreadOnly);
        
        // Count total notifications
        $total = $this->notificationModel->countUnread($userId);
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'notifications' => $notifications,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $id Notification ID
     * @return bool Success or failure
     */
    public function markAsRead($id) {
        return $this->notificationModel->markAsRead($id);
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success or failure
     */
    public function markAllAsRead($userId) {
        return $this->notificationModel->markAllAsRead($userId);
    }
    
    /**
     * Create a notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param int $relatedId Related entity ID (booking, guest, etc.)
     * @return int|false Notification ID or false on failure
     */
    public function createNotification($userId, $type, $message, $relatedId = null) {
        return $this->notificationModel->create($userId, $type, $message, $relatedId);
    }
    
    /**
     * Create a notification with array parameters
     * 
     * @param array $data Notification data
     * @return int|false Notification ID or false on failure
     */
    public function create($data) {
        if (!isset($data['user_id']) || !isset($data['type']) || !isset($data['message'])) {
            return false;
        }
        
        $userId = $data['user_id'];
        $type = $data['type'];
        $message = $data['message'];
        $relatedId = isset($data['related_id']) ? $data['related_id'] : null;
        
        return $this->createNotification($userId, $type, $message, $relatedId);
    }
    
    /**
     * Count unread notifications for a user
     * 
     * @param int $userId User ID
     * @return int Count
     */
    public function countUnread($userId) {
        return $this->notificationModel->countUnread($userId);
    }
    
    /**
     * Get notification type icon
     * 
     * @param string $type Notification type
     * @return string Icon class
     */
    public function getTypeIcon($type) {
        switch ($type) {
            case Notification::TYPE_BOOKING_CREATED:
                return 'fas fa-calendar-plus';
            case Notification::TYPE_BOOKING_CONFIRMED:
                return 'fas fa-check-circle';
            case Notification::TYPE_BOOKING_CANCELLED:
                return 'fas fa-times-circle';
            case Notification::TYPE_BOOKING_COMPLETED:
                return 'fas fa-calendar-check';
            case Notification::TYPE_USER_LOGIN:
                return 'fas fa-sign-in-alt';
            case Notification::TYPE_USER_REGISTERED:
                return 'fas fa-user-plus';
            case Notification::TYPE_GUEST_ACCEPTED:
                return 'fas fa-user-check';
            case Notification::TYPE_GUEST_DECLINED:
                return 'fas fa-user-times';
            default:
                return 'fas fa-bell';
        }
    }
    
    /**
     * Get notification color based on type
     * 
     * @param string $type Notification type
     * @return string CSS color class
     */
    public function getTypeColor($type) {
        switch ($type) {
            case Notification::TYPE_BOOKING_CREATED:
                return 'primary';
            case Notification::TYPE_BOOKING_CONFIRMED:
                return 'success';
            case Notification::TYPE_BOOKING_CANCELLED:
                return 'danger';
            case Notification::TYPE_BOOKING_COMPLETED:
                return 'info';
            case Notification::TYPE_USER_LOGIN:
                return 'secondary';
            case Notification::TYPE_USER_REGISTERED:
                return 'primary';
            case Notification::TYPE_GUEST_ACCEPTED:
                return 'success';
            case Notification::TYPE_GUEST_DECLINED:
                return 'danger';
            default:
                return 'secondary';
        }
    }
}
?>