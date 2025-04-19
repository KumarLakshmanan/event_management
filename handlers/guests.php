<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Get the requested action
$action = sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateGuest();
        break;
    case 'update':
        handleUpdateGuest();
        break;
    case 'delete':
        handleDeleteGuest();
        break;
    case 'invite':
        handleInviteGuest();
        break;
    default:
        setFlashMessage('Invalid action specified', 'danger');
        header("Location: ../pages/my-guests.php");
        exit;
}

/**
 * Handle guest creation
 */
function handleCreateGuest()
{
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $bookingId = filter_var($_POST['booking_id'] ?? 0, FILTER_VALIDATE_INT);

    // Validate required fields
    if (empty($name) || !$bookingId) {
        respondWithError('Name and booking are required');
        return;
    }

    // Get user ID
    $userId = $_SESSION['user_id'];

    // Verify ownership of booking
    $db = Database::getInstance();

    // Check if booking exists and belongs to user
    $booking = $db->querySingle("SELECT * FROM bookings WHERE id = ? AND user_id = ?", [$bookingId, $userId]);
    if (!$booking) {
        respondWithError('Invalid booking or not authorized');
        return;
    }

    // Create guest
    $guestData = [
        'booking_id' => $bookingId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'rsvp_status' => 'pending'
    ];

    $guestId = insertRecord('guests', $guestData);

    if ($guestId) {
        // Send invitation email if email provided
        if (!empty($email)) {
            $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);
            sendGuestInvitationEmail([
                'id' => $guestId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ], $booking, $user);

            // Update last invited time
            $db->execute("UPDATE guests SET last_invited_at = ? WHERE id = ?", [date('Y-m-d H:i:s'), $guestId]);

            // Add notification
            addNotification(
                'guest_invited',
                "You've invited {$name} to your event.",
                $userId,
                "../pages/my-guests.php"
            );
        }

        respondWithSuccess('Guest added successfully', ['id' => $guestId]);
    } else {
        respondWithError('Failed to add guest');
    }
}

/**
 * Handle guest update
 */
function handleUpdateGuest()
{
    // Validate input
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');

    // Validate required fields
    if (!$id || empty($name)) {
        respondWithError('ID and name are required');
        return;
    }

    // Get user ID
    $userId = $_SESSION['user_id'];

    // Update guest in database or mock data
    $db = Database::getInstance();

    // Check if guest exists and belongs to user's booking
    $guestQuery = "
            SELECT g.* FROM guests g
            JOIN bookings b ON g.booking_id = b.id
            WHERE g.id = ? AND b.user_id = ?
        ";
    $guest = $db->querySingle($guestQuery, [$id, $userId]);

    if (!$guest) {
        respondWithError('Guest not found or not authorized');
        return;
    }

    // Update guest
    $guestData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone
    ];

    $result = updateRecord('guests', $id, $guestData);

    if ($result) {
        respondWithSuccess('Guest updated successfully');
    } else {
        respondWithError('Failed to update guest');
    }
}

/**
 * Handle guest deletion
 */
function handleDeleteGuest()
{
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id) {
        setFlashMessage('Invalid guest ID', 'danger');
        header("Location: ../pages/my-guests.php");
        exit;
    }

    // Get user ID
    $userId = $_SESSION['user_id'];

    // Delete guest from database or mock data
    $db = Database::getInstance();

    // Check if guest exists and belongs to user's booking
    $guestQuery = "
            SELECT g.* FROM guests g
            JOIN bookings b ON g.booking_id = b.id
            WHERE g.id = ? AND (b.user_id = ? OR ? IN (SELECT id FROM users WHERE role IN ('admin', 'manager')))
        ";
    $guest = $db->querySingle($guestQuery, [$id, $userId, $userId]);

    if (!$guest) {
        setFlashMessage('Guest not found or not authorized', 'danger');
        header("Location: ../pages/my-guests.php");
        exit;
    }

    // Delete guest
    $result = $db->execute("DELETE FROM guests WHERE id = ?", [$id]);

    if ($result) {
        setFlashMessage('Guest deleted successfully', 'success');
    } else {
        setFlashMessage('Failed to delete guest', 'danger');
    }

    header("Location: ../pages/my-guests.php");
    exit;
}

/**
 * Handle guest invitation (resend)
 */
function handleInviteGuest()
{
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id) {
        setFlashMessage('Invalid guest ID', 'danger');
        header("Location: ../pages/my-guests.php");
        exit;
    }

    // Get user ID
    $userId = $_SESSION['user_id'];

    // Process invitation in database or mock data
    $db = Database::getInstance();

    // Check if guest exists and belongs to user's booking
    $guestQuery = "
            SELECT g.* FROM guests g
            JOIN bookings b ON g.booking_id = b.id
            WHERE g.id = ? AND b.user_id = ?
        ";
    $guest = $db->querySingle($guestQuery, [$id, $userId]);

    if (!$guest) {
        setFlashMessage('Guest not found or not authorized', 'danger');
        header("Location: ../pages/my-guests.php");
        exit;
    }

    // Check if guest has email
    if (empty($guest['email'])) {
        setFlashMessage('Guest does not have an email address', 'warning');
        header("Location: ../pages/my-guests.php");
        exit;
    }

    // Get booking details
    $booking = $db->querySingle("SELECT * FROM bookings WHERE id = ?", [$guest['booking_id']]);

    // Get user details
    $user = $db->querySingle("SELECT * FROM users WHERE id = ?", [$userId]);

    // Send invitation email
    $emailSent = sendGuestInvitationEmail($guest, $booking, $user);

    if ($emailSent) {
        // Update last invited time
        $db->execute("UPDATE guests SET last_invited_at = ? WHERE id = ?", [date('Y-m-d H:i:s'), $id]);

        // Add notification
        addNotification(
            'guest_invited',
            "You've sent an invitation to {$guest['name']}.",
            $userId,
            "../pages/my-guests.php"
        );

        setFlashMessage('Invitation sent successfully', 'success');
    } else {
        setFlashMessage('Failed to send invitation', 'danger');
    }

    header("Location: ../pages/my-guests.php");
    exit;
}

/**
 * Respond with success JSON
 */
function respondWithSuccess($message, $data = [])
{
    $response = [
        'success' => true,
        'message' => $message
    ];

    if (!empty($data)) {
        $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Respond with error JSON
 */
function respondWithError($message, $code = 400)
{
    http_response_code($code);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
