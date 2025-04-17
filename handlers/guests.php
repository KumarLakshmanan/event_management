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
        
    case 'update':
        handleUpdate();
        break;
        
    case 'delete':
        handleDelete();
        break;
        
    case 'send_invite':
        handleSendInvite();
        break;
        
    case 'update_rsvp':
        handleUpdateRsvp();
        break;
        
    default:
        // Invalid action
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Handle guest creation
 */
function handleCreate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    // Validate input
    if ($bookingId <= 0 || empty($name) || empty($email)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Name, email, and booking ID are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Verify booking exists and is accessible by the user
    $bookings = getMockData('bookings.json');
    $booking = null;
    foreach ($bookings as $b) {
        if ($b['id'] === $bookingId) {
            $booking = $b;
            break;
        }
    }
    
    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Check if user has access to this booking
    if ($booking['user_id'] !== intval($_SESSION['user_id']) && !hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied to this booking']);
        exit;
    }
    
    // Check if booking is confirmed before adding guests
    if ($booking['status'] !== 'confirmed' && !hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Cannot add guests to unconfirmed bookings']);
        exit;
    }
    
    // Get guests data
    $guests = getMockData('guests.json');
    
    // Generate guest ID
    $id = count($guests) > 0 ? max(array_column($guests, 'id')) + 1 : 1;
    
    // Create new guest
    $newGuest = [
        'id' => $id,
        'booking_id' => $bookingId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'rsvp_status' => 'pending'
    ];
    
    // Add guest to data
    $guests[] = $newGuest;
    
    // Save data
    saveMockData('guests.json', $guests);
    
    // Send API request to external API
    apiPost('guests', $newGuest);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'guest' => $newGuest]);
    exit;
}

/**
 * Handle guest update
 */
function handleUpdate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $id = intval($_POST['id'] ?? 0);
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $rsvpStatus = sanitizeInput($_POST['rsvp_status'] ?? '');
    
    // Validate input
    if ($id <= 0 || $bookingId <= 0 || empty($name) || empty($email)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID, booking ID, name, and email are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Verify booking exists and is accessible by the user
    $bookings = getMockData('bookings.json');
    $booking = null;
    foreach ($bookings as $b) {
        if ($b['id'] === $bookingId) {
            $booking = $b;
            break;
        }
    }
    
    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Check if user has access to this booking
    if ($booking['user_id'] !== intval($_SESSION['user_id']) && !hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied to this booking']);
        exit;
    }
    
    // Get guests data
    $guests = getMockData('guests.json');
    
    // Find guest to update
    $guestIndex = -1;
    foreach ($guests as $index => $guest) {
        if ($guest['id'] === $id) {
            $guestIndex = $index;
            break;
        }
    }
    
    if ($guestIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Guest not found']);
        exit;
    }
    
    // Validate RSVP status
    $validRsvpStatuses = ['pending', 'yes', 'no'];
    if (!empty($rsvpStatus) && !in_array($rsvpStatus, $validRsvpStatuses)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid RSVP status']);
        exit;
    }
    
    // Update guest
    $guests[$guestIndex]['name'] = $name;
    $guests[$guestIndex]['email'] = $email;
    $guests[$guestIndex]['phone'] = $phone;
    if (!empty($rsvpStatus)) {
        $guests[$guestIndex]['rsvp_status'] = $rsvpStatus;
    }
    
    // Save data
    saveMockData('guests.json', $guests);
    
    // Send API request to external API
    apiPut('guests/' . $id, $guests[$guestIndex]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'guest' => $guests[$guestIndex]]);
    exit;
}

/**
 * Handle guest deletion
 */
function handleDelete() {
    // Get input data
    $id = intval($_REQUEST['id'] ?? 0);
    $bookingId = intval($_REQUEST['booking_id'] ?? 0);
    
    if ($id <= 0 || $bookingId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid guest ID or booking ID']);
        exit;
    }
    
    // Verify booking exists and is accessible by the user
    $bookings = getMockData('bookings.json');
    $booking = null;
    foreach ($bookings as $b) {
        if ($b['id'] === $bookingId) {
            $booking = $b;
            break;
        }
    }
    
    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Check if user has access to this booking
    if ($booking['user_id'] !== intval($_SESSION['user_id']) && !hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied to this booking']);
        exit;
    }
    
    // Get guests data
    $guests = getMockData('guests.json');
    
    // Find guest to delete
    $guestIndex = -1;
    foreach ($guests as $index => $guest) {
        if ($guest['id'] === $id && $guest['booking_id'] === $bookingId) {
            $guestIndex = $index;
            break;
        }
    }
    
    if ($guestIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Guest not found']);
        exit;
    }
    
    // Remove guest from data
    array_splice($guests, $guestIndex, 1);
    
    // Save data
    saveMockData('guests.json', $guests);
    
    // Send API request to external API
    apiDelete('guests/' . $id);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

/**
 * Handle sending invitation to a guest
 */
function handleSendInvite() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $id = intval($_POST['id'] ?? 0);
    $bookingId = intval($_POST['booking_id'] ?? 0);
    
    if ($id <= 0 || $bookingId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid guest ID or booking ID']);
        exit;
    }
    
    // Verify booking exists and is accessible by the user
    $bookings = getMockData('bookings.json');
    $booking = null;
    foreach ($bookings as $b) {
        if ($b['id'] === $bookingId) {
            $booking = $b;
            break;
        }
    }
    
    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }
    
    // Check if user has access to this booking
    if ($booking['user_id'] !== intval($_SESSION['user_id']) && !hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied to this booking']);
        exit;
    }
    
    // Get guests data
    $guests = getMockData('guests.json');
    
    // Find guest to send invitation
    $guest = null;
    foreach ($guests as $g) {
        if ($g['id'] === $id && $g['booking_id'] === $bookingId) {
            $guest = $g;
            break;
        }
    }
    
    if (!$guest) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Guest not found']);
        exit;
    }
    
    // In a real application, you would send an actual email invitation
    // For demo purposes, we'll simulate sending an email
    $result = sendInvitation($guest, $booking);
    
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to send invitation']);
        exit;
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Invitation sent successfully']);
    exit;
}

/**
 * Handle updating RSVP status (from invitation link)
 */
function handleUpdateRsvp() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $guestId = intval($_POST['guest_id'] ?? 0);
    $rsvpStatus = sanitizeInput($_POST['rsvp_status'] ?? '');
    
    // Validate input
    if ($guestId <= 0 || empty($rsvpStatus)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Guest ID and RSVP status are required']);
        exit;
    }
    
    // Validate RSVP status
    $validRsvpStatuses = ['yes', 'no'];
    if (!in_array($rsvpStatus, $validRsvpStatuses)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid RSVP status']);
        exit;
    }
    
    // Get guests data
    $guests = getMockData('guests.json');
    
    // Find guest to update
    $guestIndex = -1;
    foreach ($guests as $index => $guest) {
        if ($guest['id'] === $guestId) {
            $guestIndex = $index;
            break;
        }
    }
    
    if ($guestIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Guest not found']);
        exit;
    }
    
    // Update RSVP status
    $guests[$guestIndex]['rsvp_status'] = $rsvpStatus;
    
    // Save data
    saveMockData('guests.json', $guests);
    
    // Send API request to external API
    apiPut('guests/' . $guestId, $guests[$guestIndex]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'guest' => $guests[$guestIndex]]);
    exit;
}
?>
