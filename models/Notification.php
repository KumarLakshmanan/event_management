<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Notification {
    private $db;
    
    // Notification types
    const TYPE_BOOKING_CREATED = 'booking_created';
    const TYPE_BOOKING_CONFIRMED = 'booking_confirmed';
    const TYPE_BOOKING_CANCELLED = 'booking_cancelled';
    const TYPE_BOOKING_COMPLETED = 'booking_completed';
    const TYPE_USER_LOGIN = 'user_login';
    const TYPE_USER_REGISTERED = 'user_registered';
    const TYPE_GUEST_ACCEPTED = 'guest_accepted';
    const TYPE_GUEST_DECLINED = 'guest_declined';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param int $relatedId Related entity ID (booking, guest, etc.)
     * @return int|false Notification ID or false on failure
     */
    public function create($userId, $type, $message, $relatedId = null) {
        // Prepare data for insert
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'related_id' => $relatedId,
            'is_read' => 0
        ];
        
        // Insert notification
        return $this->db->insert('notifications', $data);
    }
    
    /**
     * Get notifications for a user
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool $unreadOnly Only get unread notifications
     * @return array Notifications
     */
    public function getByUserId($userId, $limit = 20, $offset = 0, $unreadOnly = false) {
        // Build query
        $query = "SELECT * FROM notifications WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $id Notification ID
     * @return bool Success or failure
     */
    public function markAsRead($id) {
        return $this->db->update('notifications', ['is_read' => 1], 'id = :id', [':id' => $id]);
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success or failure
     */
    public function markAllAsRead($userId) {
        return $this->db->update('notifications', ['is_read' => 1], 'user_id = :user_id', [':user_id' => $userId]);
    }
    
    /**
     * Delete notification
     * 
     * @param int $id Notification ID
     * @return bool Success or failure
     */
    public function delete($id) {
        return $this->db->delete('notifications', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Count unread notifications for a user
     * 
     * @param int $userId User ID
     * @return int Count
     */
    public function countUnread($userId) {
        return $this->db->count('notifications', 'user_id = :user_id AND is_read = 0', [':user_id' => $userId]);
    }
}
?>