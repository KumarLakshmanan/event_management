<?php
session_start();
require_once '../config/config.php';
require_once 'api.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check action parameter
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        handleCreate();
        break;
        
    case 'confirm':
        handleConfirm();
        break;
        
    case 'delete':
        handleDelete();
        break;
        
    default:
        // Invalid action
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Handle booking creation
 */
function handleCreate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $packageId = intval($_POST['package_id'] ?? 0);
    $eventDate = sanitizeInput($_POST['event_date'] ?? '');
    $eventPlace = sanitizeInput($_POST['event_place'] ?? '');
    
    // Validate input
    if ($packageId <= 0 || empty($eventDate) || empty($eventPlace)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    
    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }
    
    // Ensure event date is in the future
    $eventDateTime = strtotime($eventDate);
    $today = strtotime(date('Y-m-d'));
    if ($eventDateTime < $today) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Event date must be in the future']);
        exit;
    }
    
    // Get packages data to verify package exists
    $packages = getMockData('packages.json');
    $packageExists = false;
    foreach ($packages as $package) {
        if ($package['id'] === $packageId) {
            $packageExists = true;
            break;
        }
    }
    
    if (!$packageExists) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Selected package does not exist']);
        exit;
    }
    
    // Get bookings data
    $bookings = getMockData('bookings.json');
    
    // Generate booking ID
    $id = count($bookings) > 0 ? max(array_column($bookings, 'id')) + 1 : 1;
    
    // Create new booking
    $newBooking = [
        'id' => $id,
        'user_id' => intval($_SESSION['user_id']),
        'package_id' => $packageId,
        'event_place' => $eventPlace,
        'event_date' => $eventDate,
        'discount' => null,
        'confirmed_by' => null,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add booking to data
    $bookings[] = $newBooking;
    
    // Save data
    saveMockData('bookings.json', $bookings);
    
    // Send API request to external API
    apiPost('bookings', $newBooking);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'booking' => $newBooking]);
    exit;
}

/**
 * Handle booking confirmation
 */
function handleConfirm() {
    // Check if user has manager or admin role
    if (!hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $id = intval($_POST['id'] ?? 0);
    $discount = isset($_POST['discount']) && $_POST['discount'] !== '' ? floatval($_POST['discount']) : null;
    
    // Validate input
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid booking ID']);
        exit;
    }
    
    // Check if discount is allowed for this user
    if ($discount !== null && !canGiveDiscount()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'You are not authorized to provide discounts']);
        exit;
    }
    
    // Get bookings data
    $bookings = getMockData('bookings.json');
    
    // Find booking to confirm
    $bookingIndex = -1;
    foreach ($bookings as $index => $booking) {
        if ($booking['id'] === $id) {
            $bookingIndex = $index;
            break;
        }
    }
    
    if ($bookingIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Check if booking is already confirmed
    if ($bookings[$bookingIndex]['status'] === 'confirmed') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking is already confirmed']);
        exit;
    }
    
    // Update booking
    $bookings[$bookingIndex]['status'] = 'confirmed';
    $bookings[$bookingIndex]['confirmed_by'] = intval($_SESSION['user_id']);
    if ($discount !== null) {
        $bookings[$bookingIndex]['discount'] = $discount;
    }
    
    // Save data
    saveMockData('bookings.json', $bookings);
    
    // Send API request to external API
    apiPut('bookings/' . $id, $bookings[$bookingIndex]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'booking' => $bookings[$bookingIndex]]);
    exit;
}

/**
 * Handle booking deletion
 */
function handleDelete() {
    // Only admins can delete bookings
    if (!hasRole('admin')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }
    
    // Get booking ID
    $id = intval($_REQUEST['id'] ?? 0);
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid booking ID']);
        exit;
    }
    
    // Get bookings data
    $bookings = getMockData('bookings.json');
    
    // Find booking to delete
    $bookingIndex = -1;
    foreach ($bookings as $index => $booking) {
        if ($booking['id'] === $id) {
            $bookingIndex = $index;
            break;
        }
    }
    
    if ($bookingIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Remove booking from data
    array_splice($bookings, $bookingIndex, 1);
    
    // Save data
    saveMockData('bookings.json', $bookings);
    
    // Send API request to external API
    apiDelete('bookings/' . $id);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>
