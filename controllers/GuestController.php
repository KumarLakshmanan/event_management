<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/User.php';

class GuestController {
    private $guestModel;
    private $bookingModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->guestModel = new Guest();
        $this->bookingModel = new Booking();
        $this->userModel = new User();
    }
    
    /**
     * Get all guests for a booking
     * 
     * @param int $bookingId Booking ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Guests for the booking
     */
    public function getGuestsByBooking($bookingId, $page = 1, $perPage = 50) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [];
        }
        
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        return $this->guestModel->getByBookingId($bookingId, $perPage, $offset, 'name', 'ASC');
    }
    
    /**
     * Get guest details with booking information
     * 
     * @param int $id Guest ID
     * @return array|false Guest details or false if not found
     */
    public function getGuestWithBooking($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        $guest = $this->guestModel->getById($id);
        
        if (!$guest) {
            return false;
        }
        
        // Get booking details to check permissions
        $booking = $this->bookingModel->getById($guest['booking_id']);
        
        if ($booking) {
            $guest['user_id'] = $booking['user_id'];
        }
        
        return $guest;
    }
    
    /**
     * Add a new guest
     * 
     * @param array $data Guest data
     * @return int|false Guest ID or false on failure
     */
    public function addGuest($data) {
        // Validate required fields
        if (!isset($data['booking_id']) || !isset($data['name']) || empty($data['name'])) {
            return false;
        }
        
        // Validate booking exists
        $booking = $this->bookingModel->getById($data['booking_id']);
        if (!$booking) {
            return false;
        }
        
        // Create the guest
        return $this->guestModel->create(
            $data['booking_id'],
            $data['name'],
            isset($data['email']) ? $data['email'] : '',
            isset($data['phone']) ? $data['phone'] : '',
            isset($data['rsvp_status']) ? $data['rsvp_status'] : RSVP_PENDING
        );
    }
    
    /**
     * Add multiple guests at once
     * 
     * @param int $bookingId Booking ID
     * @param string $guestList Text with guest information (one per line)
     * @return int Number of guests added
     */
    public function bulkAddGuests($bookingId, $guestList) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId) || empty($guestList)) {
            return 0;
        }
        
        // Validate booking exists
        $booking = $this->bookingModel->getById($bookingId);
        if (!$booking) {
            return 0;
        }
        
        // Split guest list by lines
        $lines = explode("\n", str_replace("\r", "", $guestList));
        $addedCount = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // Parse line (name, email, phone - comma separated)
            $parts = array_map('trim', explode(',', $line));
            
            if (!empty($parts[0])) {
                $guestData = [
                    'booking_id' => $bookingId,
                    'name' => $parts[0],
                    'email' => isset($parts[1]) ? $parts[1] : '',
                    'phone' => isset($parts[2]) ? $parts[2] : '',
                    'rsvp_status' => RSVP_PENDING
                ];
                
                $guestId = $this->addGuest($guestData);
                if ($guestId) {
                    $addedCount++;
                }
            }
        }
        
        return $addedCount;
    }
    
    /**
     * Update guest information
     * 
     * @param array $data Guest data
     * @return bool Success or failure
     */
    public function updateGuest($data) {
        // Validate required fields
        if (!isset($data['id']) || !isset($data['name']) || empty($data['name'])) {
            return false;
        }
        
        // Check if guest exists
        $guest = $this->guestModel->getById($data['id']);
        if (!$guest) {
            return false;
        }
        
        // Prepare update data
        $updateData = [
            'name' => $data['name'],
            'email' => isset($data['email']) ? $data['email'] : $guest['email'],
            'phone' => isset($data['phone']) ? $data['phone'] : $guest['phone'],
            'rsvp_status' => isset($data['rsvp_status']) ? $data['rsvp_status'] : $guest['rsvp_status']
        ];
        
        // Update guest
        return $this->guestModel->update($data['id'], $updateData);
    }
    
    /**
     * Update guest RSVP status
     * 
     * @param int $id Guest ID
     * @param string $status New RSVP status
     * @return bool Success or failure
     */
    public function updateRsvpStatus($id, $status) {
        // Validate input
        if (!$id || !is_numeric($id) || empty($status)) {
            return false;
        }
        
        // Check if status is valid
        $validStatuses = [RSVP_PENDING, RSVP_ACCEPTED, RSVP_DECLINED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return $this->guestModel->updateRsvpStatus($id, $status);
    }
    
    /**
     * Delete a guest
     * 
     * @param int $id Guest ID
     * @return bool Success or failure
     */
    public function deleteGuest($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->guestModel->delete($id);
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
        
        return $this->guestModel->getRsvpStats($bookingId);
    }
    
    /**
     * Send invitations to all guests with email addresses
     * 
     * @param int $bookingId Booking ID
     * @return int Number of invitations sent
     */
    public function sendInvitations($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return 0;
        }
        
        return $this->guestModel->sendAllInvitations($bookingId);
    }
}
?>
