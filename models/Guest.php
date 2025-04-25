<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Guest {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new guest
     * 
     * @param int $bookingId Booking ID
     * @param string $name Guest name
     * @param string $email Guest email
     * @param string $phone Guest phone
     * @param string $rsvpStatus RSVP status
     * @return int|false Guest ID or false on failure
     */
    public function create($bookingId, $name, $email, $phone, $rsvpStatus = RSVP_PENDING) {
        // Validate inputs
        if (!$bookingId || !is_numeric($bookingId) || empty($name)) {
            return false;
        }
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Prepare data for insert
        $data = [
            'booking_id' => $bookingId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'rsvp_status' => $rsvpStatus
        ];
        
        // Insert guest
        return $this->db->insert('guests', $data);
    }
    
    /**
     * Get guest by ID
     * 
     * @param int $id Guest ID
     * @return array|false Guest data or false if not found
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM guests WHERE id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Update guest
     * 
     * @param int $id Guest ID
     * @param array $data Guest data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Ensure id is valid
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Remove protected fields
        $allowedFields = ['name', 'email', 'phone', 'rsvp_status'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Add updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('guests', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Update guest RSVP status
     * 
     * @param int $id Guest ID
     * @param string $status New RSVP status
     * @return bool Success or failure
     */
    public function updateRsvpStatus($id, $status) {
        // Validate inputs
        if (!$id || !is_numeric($id) || empty($status)) {
            return false;
        }
        
        // Check if status is valid
        $validStatuses = [RSVP_PENDING, RSVP_ACCEPTED, RSVP_DECLINED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return $this->update($id, ['rsvp_status' => $status]);
    }
    
    /**
     * Delete guest
     * 
     * @param int $id Guest ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->db->delete('guests', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Get all guests for a booking
     * 
     * @param int $bookingId Booking ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Guests
     */
    public function getByBookingId($bookingId, $limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'ASC') {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [];
        }
        
        // Validate order by field
        $allowedOrderByFields = ['id', 'name', 'rsvp_status', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        return $this->db->fetchAll(
            "SELECT * FROM guests
             WHERE booking_id = :booking_id
             ORDER BY $orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':booking_id' => $bookingId,
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Count total guests for a booking
     * 
     * @param int $bookingId Booking ID
     * @return int Number of guests
     */
    public function countByBookingId($bookingId) {
        return $this->db->count('guests', 'booking_id = :booking_id', [':booking_id' => $bookingId]);
    }
    
    /**
     * Count guests by RSVP status for a booking
     * 
     * @param int $bookingId Booking ID
     * @param string $status RSVP status
     * @return int Number of guests
     */
    public function countByRsvpStatus($bookingId, $status) {
        return $this->db->count(
            'guests', 
            'booking_id = :booking_id AND rsvp_status = :status',
            [
                ':booking_id' => $bookingId,
                ':status' => $status
            ]
        );
    }
    
    /**
     * Get RSVP statistics for a booking
     * 
     * @param int $bookingId Booking ID
     * @return array RSVP statistics
     */
    public function getRsvpStats($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [
                'total' => 0,
                'accepted' => 0,
                'declined' => 0,
                'pending' => 0
            ];
        }
        
        return [
            'total' => $this->countByBookingId($bookingId),
            'accepted' => $this->countByRsvpStatus($bookingId, RSVP_ACCEPTED),
            'declined' => $this->countByRsvpStatus($bookingId, RSVP_DECLINED),
            'pending' => $this->countByRsvpStatus($bookingId, RSVP_PENDING)
        ];
    }
    
    /**
     * Send invitation email to guest
     * 
     * @param int $guestId Guest ID
     * @return bool Success or failure
     */
    public function sendInvitation($guestId) {
        // Validate input
        if (!$guestId || !is_numeric($guestId)) {
            return false;
        }
        
        // Get guest information
        $guest = $this->getById($guestId);
        if (!$guest || empty($guest['email'])) {
            return false;
        }
        
        // In a real implementation, this would send an email
        // For now, just mark as sent by updating the guest
        return $this->update($guestId, [
            'rsvp_status' => RSVP_PENDING,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Send invitations to all guests of a booking
     * 
     * @param int $bookingId Booking ID
     * @return int Number of invitations sent
     */
    public function sendAllInvitations($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return 0;
        }
        
        // Get all guests with email addresses
        $guests = $this->db->fetchAll(
            "SELECT id FROM guests 
             WHERE booking_id = :booking_id AND email IS NOT NULL AND email != ''",
            [':booking_id' => $bookingId]
        );
        
        $sent = 0;
        foreach ($guests as $guest) {
            if ($this->sendInvitation($guest['id'])) {
                $sent++;
            }
        }
        
        return $sent;
    }
}
?>
