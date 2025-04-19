<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

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

    case 'cancel':
        handleCancel();
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
function handleCreate()
{
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

    $userId = $_SESSION['user_id'];

    $db = Database::getInstance();

    // Check if package exists
    $package = $db->querySingle("SELECT * FROM packages WHERE id = ?", [$packageId]);

    if (!$package) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Selected package does not exist']);
        exit;
    }

    // Create booking
    $bookingData = [
        'user_id' => $userId,
        'package_id' => $packageId,
        'event_place' => $eventPlace,
        'event_date' => $eventDate,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $bookingId = insertRecord('bookings', $bookingData);

    if (!$bookingId) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to create booking']);
        exit;
    }

    // Get full booking data
    $newBooking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$bookingId]);

    // Add notification
    addNotification(
        'booking_created',
        "You've created a new booking for {$package['name']} on " . date('F d, Y', strtotime($eventDate)),
        $userId,
        "../pages/bookings.php?id={$bookingId}"
    );

    // Notify admins and managers
    if ($userId) {
        $user = $db->querySingle("SELECT name FROM users WHERE id = ?", [$userId]);
        $userName = $user['name'] ?? 'A user';

        addNotification(
            'booking_created',
            "{$userName} has created a new booking for {$package['name']} on " . date('F d, Y', strtotime($eventDate)),
            null, // System notification (for admins/managers)
            "../pages/bookings.php?id={$bookingId}"
        );
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'booking' => $newBooking]);
    exit;
}

/**
 * Handle booking confirmation
 */
function handleConfirm()
{
    // Check if user has manager or admin role
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
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
    if ($discount !== null && !hasPermission('give_discount')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'You are not authorized to provide discounts']);
        exit;
    }

    // Get booking data
    $db = Database::getInstance();

    // Get booking
    $booking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$id]);

    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }

    // Check if booking is already confirmed
    if ($booking['status'] === 'confirmed') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking is already confirmed']);
        exit;
    }

    // Update booking
    $userData = [
        'status' => 'confirmed',
        'confirmed_by' => intval($_SESSION['user_id'])
    ];

    // Add discount if provided
    if ($discount !== null) {
        $userData['discount'] = $discount;
    }

    $result = updateRecord('bookings', $id, $userData);

    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to confirm booking']);
        exit;
    }

    // Get updated booking
    $updatedBooking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$id]);

    // Get the user
    $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$updatedBooking['user_id']]);

    // Get package details
    $package = $db->querySingle("SELECT * FROM packages WHERE id = ?", [$updatedBooking['package_id']]);

    // Send confirmation email
    sendBookingConfirmationEmail($updatedBooking, $user, $package);

    // Add notification
    $discountMsg = $discount ? sprintf(' with a $%.2f discount', $discount) : '';
    addNotification(
        'booking_confirmed',
        "Your booking for {$package['name']} on " . date('F d, Y', strtotime($updatedBooking['event_date'])) . " has been confirmed{$discountMsg}.",
        $updatedBooking['user_id'],
        "../pages/bookings.php?id={$id}"
    );

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'booking' => $updatedBooking]);
    exit;
}

/**
 * Handle booking cancellation
 */
function handleCancel()
{
    // Check permission - users can cancel their own bookings
    // Managers and admins can cancel any booking

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }

    // Get input data
    $id = intval($_POST['id'] ?? 0);

    // Validate input
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid booking ID']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'];

    // Get booking data
    $db = Database::getInstance();

    // Get booking
    $booking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$id]);

    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }

    // Check permission - users can only cancel their own bookings
    if ($userRole === 'client' && $booking['user_id'] != $userId) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }

    // Check if booking is already cancelled
    if ($booking['status'] === 'cancelled') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking is already cancelled']);
        exit;
    }

    // Update booking status
    $result = $db->execute("UPDATE bookings SET status = 'cancelled' WHERE id = ?", [$id]);

    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to cancel booking']);
        exit;
    }

    // Get updated booking
    $updatedBooking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$id]);

    // Get package details
    $package = $db->querySingle("SELECT * FROM packages WHERE id = ?", [$updatedBooking['package_id']]);

    // Add notification
    addNotification(
        'booking_cancelled',
        "Booking for {$package['name']} on " . date('F d, Y', strtotime($updatedBooking['event_date'])) . " has been cancelled.",
        $updatedBooking['user_id'],
        "../pages/bookings.php?id={$id}"
    );

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'booking' => $updatedBooking]);
    exit;
}

/**
 * Handle booking deletion
 */
function handleDelete()
{
    // Only admins can delete bookings
    if ($_SESSION['user_role'] !== 'admin') {
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

    $db = Database::getInstance();

    // Check if booking exists
    $booking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$id]);

    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Booking not found']);
        exit;
    }

    // Delete booking
    $result = $db->execute("DELETE FROM bookings WHERE id = ?", [$id]);

    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to delete booking']);
        exit;
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
