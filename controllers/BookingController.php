<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Package.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/User.php';

class BookingController {
    private $bookingModel;
    private $packageModel;
    private $serviceModel;
    private $guestModel;
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->bookingModel = new Booking();
        $this->packageModel = new Package();
        $this->serviceModel = new Service();
        $this->guestModel = new Guest();
        $this->userModel = new User();
    }
    
    /**
     * Get all bookings with pagination
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings and pagination info
     */
    public function getAllBookings($page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get bookings
        $bookings = $this->bookingModel->getAll($perPage, $offset);
        
        // Get total count
        $total = $this->bookingModel->countAll();
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'bookings' => $bookings,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get bookings for a specific user with pagination
     * 
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings and pagination info
     */
    public function getUserBookings($userId, $page = 1, $perPage = 10) {
        // Validate input
        if (!$userId || !is_numeric($userId)) {
            return [
                'bookings' => [],
                'pagination' => [
                    'current' => $page,
                    'total' => 0,
                    'perPage' => $perPage,
                    'totalItems' => 0
                ]
            ];
        }
        
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get bookings
        $bookings = $this->bookingModel->getByUserId($userId, $perPage, $offset);
        
        // Get total count
        $total = $this->bookingModel->countByUserId($userId);
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'bookings' => $bookings,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get bookings by status with pagination
     * 
     * @param string $status Booking status
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Bookings and pagination info
     */
    public function getBookingsByStatus($status, $page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get bookings by status
        $bookings = $this->bookingModel->getAll($perPage, $offset, 'id', 'DESC');
        
        // Filter by status
        $bookings = array_filter($bookings, function($booking) use ($status) {
            return $booking['status'] === $status;
        });
        
        // Get total count
        $total = $this->bookingModel->countByStatus($status);
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'bookings' => array_values($bookings), // Re-index array after filtering
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get booking details by ID
     * 
     * @param int $id Booking ID
     * @return array|false Booking details or false if not found
     */
    public function getBookingDetails($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        $booking = $this->bookingModel->getWithPackageDetails($id);
        
        if (!$booking) {
            return false;
        }
        
        // Get client details
        $user = $this->userModel->getById($booking['user_id']);
        if ($user) {
            $booking['client_name'] = $user['name'];
            $booking['client_email'] = $user['email'];
        }
        
        return $booking;
    }
    
    /**
     * Create a new booking
     * 
     * @param array $data Booking data
     * @return int|false Booking ID or false on failure
     */
    public function createBooking($data) {
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['event_date']) || !isset($data['event_location'])) {
            return false;
        }
        
        // Check if creating a custom package (selecting individual services)
        if (empty($data['package_id']) && !empty($data['services'])) {
            // Create a custom booking directly with services
            $totalPrice = 0;
            $selectedServices = [];
            
            // Calculate total price and collect service details
            foreach ($data['services'] as $serviceId) {
                $service = $this->serviceModel->getById($serviceId);
                if ($service) {
                    $totalPrice += $service['price'];
                    $selectedServices[] = $service;
                }
            }
            
            // Create custom package booking
            $bookingId = $this->bookingModel->create(
                $data['user_id'],
                0, // No package, custom selection
                $data['event_date'],
                $data['event_location'],
                $totalPrice,
                STATUS_PENDING
            );
            
            if ($bookingId) {
                // Add selected services to the booking
                foreach ($selectedServices as $service) {
                    $this->bookingModel->addService($bookingId, $service['id']);
                }
                
                return $bookingId;
            }
            
            return false;
        }
        
        // Standard booking with a package
        if (empty($data['package_id']) || !is_numeric($data['package_id'])) {
            return false;
        }
        
        // Get package details
        $package = $this->packageModel->getById($data['package_id']);
        if (!$package) {
            return false;
        }
        
        // Create the booking
        $bookingId = $this->bookingModel->create(
            $data['user_id'],
            $data['package_id'],
            $data['event_date'],
            $data['event_location'],
            $package['price'],
            STATUS_PENDING
        );
        
        return $bookingId;
    }
    
    /**
     * Update booking status
     * 
     * @param array $data Update data
     * @return bool Success or failure
     */
    public function updateStatus($data) {
        // Validate required fields
        if (!isset($data['booking_id']) || !isset($data['status'])) {
            return false;
        }
        
        // Check if status is valid
        $validStatuses = [STATUS_PENDING, STATUS_CONFIRMED, STATUS_CANCELLED, STATUS_COMPLETED];
        if (!in_array($data['status'], $validStatuses)) {
            return false;
        }
        
        return $this->bookingModel->updateStatus($data['booking_id'], $data['status']);
    }
    
    /**
     * Apply discount to booking
     * 
     * @param array $data Discount data
     * @return bool Success or failure
     */
    public function applyDiscount($data) {
        // Validate required fields
        if (!isset($data['booking_id']) || !isset($data['discount_amount']) || !is_numeric($data['discount_amount'])) {
            return false;
        }
        
        // Ensure discount is not negative
        if ($data['discount_amount'] < 0) {
            return false;
        }
        
        return $this->bookingModel->applyDiscount($data['booking_id'], $data['discount_amount']);
    }
    
    /**
     * Cancel a booking
     * 
     * @param int $id Booking ID
     * @return bool Success or failure
     */
    public function cancelBooking($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->bookingModel->updateStatus($id, STATUS_CANCELLED);
    }
    
    /**
     * Get package details
     * 
     * @param int $id Package ID
     * @return array|false Package details or false if not found
     */
    public function getPackageDetails($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->packageModel->getById($id);
    }
    
    /**
     * Get all packages
     * 
     * @return array All packages
     */
    public function getAllPackages() {
        return $this->packageModel->getAll(100, 0, 'name', 'ASC');
    }
    
    /**
     * Get all services
     * 
     * @return array All services
     */
    public function getAllServices() {
        return $this->serviceModel->getAll(100, 0, 'name', 'ASC');
    }
    
    /**
     * Get services included in a booking
     * 
     * @param int $bookingId Booking ID
     * @return array Services
     */
    public function getBookingServices($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [];
        }
        
        return $this->bookingModel->getServices($bookingId);
    }
    
    /**
     * Get guests for a booking
     * 
     * @param int $bookingId Booking ID
     * @return array Guests
     */
    public function getBookingGuests($bookingId) {
        // Validate input
        if (!$bookingId || !is_numeric($bookingId)) {
            return [];
        }
        
        return $this->guestModel->getByBookingId($bookingId);
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
}
?>
