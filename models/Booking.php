<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Booking {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new booking
     * 
     * @param int $userId User ID
     * @param int $packageId Package ID
     * @param string $eventDate Event date
     * @param string $eventLocation Event location
     * @param float $totalPrice Total price
     * @param string $status Booking status
     * @return int|false Booking ID or false on failure
     */
    public function create($userId, $packageId, $eventDate, $eventLocation, $totalPrice, $status = STATUS_PENDING) {
        // Validate inputs
        if (!$userId || !is_numeric($userId) || !$packageId || !is_numeric($packageId) || 
            empty($eventDate) || empty($eventLocation) || !is_numeric($totalPrice) || $totalPrice < 0) {
            return false;
        }
        
        // Prepare data for insert
        $data = [
            'user_id' => $userId,
            'package_id' => $packageId,
            'event_date' => $eventDate,
            'event_location' => $eventLocation,
            'total_price' => $totalPrice,
            'status' => $status
        ];
        
        // Insert booking
        return $this->db->insert('bookings', $data);
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array|false Booking data or false if not found
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM bookings WHERE id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Get booking with package details
     * 
     * @param int $id Booking ID
     * @return array|false Booking data with package info or false if not found
     */
    public function getWithPackageDetails($id) {
        return $this->db->fetchOne(
            "SELECT b.*, p.name as package_name, p.description as package_description, p.image_path
             FROM bookings b
             LEFT JOIN packages p ON b.package_id = p.id
             WHERE b.id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Update booking
     * 
     * @param int $id Booking ID
     * @param array $data Booking data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Ensure id is valid
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Remove protected fields
        $allowedFields = ['package_id', 'event_date', 'event_location', 'status', 'total_price', 'discount_applied'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Add updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('bookings', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Update booking status
     * 
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool Success or failure
     */
    public function updateStatus($id, $status) {
        // Validate inputs
        if (!$id || !is_numeric($id) || empty($status)) {
            return false;
        }
        
        // Check if status is valid
        $validStatuses = [STATUS_PENDING, STATUS_CONFIRMED, STATUS_CANCELLED, STATUS_COMPLETED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Apply discount to booking
     * 
     * @param int $id Booking ID
     * @param float $discountAmount Discount amount
     * @return bool Success or failure
     */
    public function applyDiscount($id, $discountAmount) {
        // Validate inputs
        if (!$id || !is_numeric($id) || !is_numeric($discountAmount) || $discountAmount < 0) {
            return false;
        }
        
        // Get booking
        $booking = $this->getById($id);
        if (!$booking) {
            return false;
        }
        
        // Calculate new total price
        $totalPrice = $booking['total_price'];
        
        // Ensure discount is not greater than total price
        if ($discountAmount > $totalPrice) {
            $discountAmount = $totalPrice;
        }
        
        // Update booking with discount
        return $this->update($id, [
            'discount_applied' => $discountAmount
        ]);
    }
    
    /**
     * Delete booking
     * 
     * @param int $id Booking ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->db->delete('bookings', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Get all bookings
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Bookings
     */
    public function getAll($limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'DESC') {
        // Validate order by field
        $allowedOrderByFields = ['id', 'event_date', 'status', 'total_price', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        return $this->db->fetchAll(
            "SELECT b.*, 
                    p.name as package_name,
                    u.name as client_name,
                    u.email as client_email
             FROM bookings b
             LEFT JOIN packages p ON b.package_id = p.id
             LEFT JOIN users u ON b.user_id = u.id
             ORDER BY b.$orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Get bookings by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Bookings
     */
    public function getByUserId($userId, $limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'DESC') {
        // Validate inputs
        if (!$userId || !is_numeric($userId)) {
            return [];
        }
        
        // Validate order by field
        $allowedOrderByFields = ['id', 'event_date', 'status', 'total_price', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        return $this->db->fetchAll(
            "SELECT b.*, p.name as package_name
             FROM bookings b
             LEFT JOIN packages p ON b.package_id = p.id
             WHERE b.user_id = :user_id
             ORDER BY b.$orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':user_id' => $userId,
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Count total bookings
     * 
     * @return int Number of bookings
     */
    public function countAll() {
        return $this->db->count('bookings');
    }
    
    /**
     * Count bookings by user ID
     * 
     * @param int $userId User ID
     * @return int Number of bookings
     */
    public function countByUserId($userId) {
        return $this->db->count('bookings', 'user_id = :user_id', [':user_id' => $userId]);
    }
    
    /**
     * Count bookings by status
     * 
     * @param string $status Status
     * @return int Number of bookings
     */
    public function countByStatus($status) {
        return $this->db->count('bookings', 'status = :status', [':status' => $status]);
    }
    
    /**
     * Add a service to a booking (for custom packages)
     * 
     * @param int $bookingId Booking ID
     * @param int $serviceId Service ID
     * @return bool Success or failure
     */
    public function addService($bookingId, $serviceId) {
        // Validate inputs
        if (!$bookingId || !is_numeric($bookingId) || !$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        // Check if service is already added to booking
        $bookingService = $this->db->fetchOne(
            "SELECT id FROM booking_services WHERE booking_id = :booking_id AND service_id = :service_id",
            [
                ':booking_id' => $bookingId,
                ':service_id' => $serviceId
            ]
        );
        
        if ($bookingService) {
            return true; // Service already added, consider this a success
        }
        
        // Add service to booking
        $data = [
            'booking_id' => $bookingId,
            'service_id' => $serviceId
        ];
        
        return $this->db->insert('booking_services', $data) ? true : false;
    }
    
    /**
     * Remove a service from a booking
     * 
     * @param int $bookingId Booking ID
     * @param int $serviceId Service ID
     * @return bool Success or failure
     */
    public function removeService($bookingId, $serviceId) {
        // Validate inputs
        if (!$bookingId || !is_numeric($bookingId) || !$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        return $this->db->delete(
            'booking_services', 
            'booking_id = :booking_id AND service_id = :service_id',
            [
                ':booking_id' => $bookingId,
                ':service_id' => $serviceId
            ]
        );
    }
    
    /**
     * Get services in a booking
     * 
     * @param int $bookingId Booking ID
     * @return array Services in the booking
     */
    public function getServices($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [];
        }
        
        return $this->db->fetchAll(
            "SELECT s.* 
             FROM services s
             JOIN booking_services bs ON s.id = bs.service_id
             WHERE bs.booking_id = :booking_id",
            [':booking_id' => $bookingId]
        );
    }
    
    /**
     * Get bookings by date range
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Bookings in date range
     */
    public function getByDateRange($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT b.*, 
                    p.name as package_name,
                    u.name as client_name,
                    u.email as client_email
             FROM bookings b
             LEFT JOIN packages p ON b.package_id = p.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.event_date BETWEEN :start_date AND :end_date
             ORDER BY b.event_date ASC",
            [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]
        );
    }
    
    /**
     * Calculate booking statistics
     * 
     * @return array Statistics about bookings
     */
    public function getStatistics() {
        $stats = [
            'total' => $this->countAll(),
            'pending' => $this->countByStatus(STATUS_PENDING),
            'confirmed' => $this->countByStatus(STATUS_CONFIRMED),
            'cancelled' => $this->countByStatus(STATUS_CANCELLED),
            'completed' => $this->countByStatus(STATUS_COMPLETED)
        ];
        
        // Get total revenue
        $totalRevenue = $this->db->fetchOne(
            "SELECT SUM(total_price - discount_applied) as total_revenue FROM bookings WHERE status != :cancelled_status",
            [':cancelled_status' => STATUS_CANCELLED]
        );
        
        $stats['total_revenue'] = $totalRevenue ? $totalRevenue['total_revenue'] : 0;
        
        // Get upcoming bookings count
        $upcomingCount = $this->db->count(
            'bookings',
            'event_date >= :today AND status = :confirmed_status',
            [
                ':today' => date('Y-m-d'),
                ':confirmed_status' => STATUS_CONFIRMED
            ]
        );
        
        $stats['upcoming'] = $upcomingCount;
        
        return $stats;
    }
}
?>
