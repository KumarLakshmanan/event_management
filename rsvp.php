<?php
/**
 * Guest Management
 * 
 * Manage guests for a booking, including RSVP status
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// PHPMailer library
require_once 'vendor/phpmailer/PHPMailer/src/Exception.php';
require_once 'vendor/phpmailer/PHPMailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/PHPMailer/src/SMTP.php';

// Get database connection
$db = getDBConnection();

// Handle RSVP updates from email links
if (isset($_GET['token']) && isset($_GET['response'])) {
    $token = $_GET['token'];
    $response = $_GET['response'] == 'yes' ? 'attending' : 'declined';
    
    // Find guest by token
    $stmt = $db->prepare("SELECT id, booking_id, name FROM attendees WHERE rsvp_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($guest) {
        // Update guest RSVP status
        $stmt = $db->prepare("UPDATE attendees SET rsvp_status = :status WHERE id = :id");
        $stmt->bindParam(':status', $response);
        $stmt->bindParam(':id', $guest['id']);
        
        if ($stmt->execute()) {
            // Add notification
            $message = $guest['name'] . ' has ' . ($response == 'attending' ? 'accepted' : 'declined') . ' the invitation';
            $type = 'guest_' . ($response == 'attending' ? 'accepted' : 'rejected');
            addNotification($type, $message, $guest['id']);
            
            setAlert('success', 'Thank you for your response. Your RSVP status has been updated to: ' . ucfirst($response));
        } else {
            setAlert('danger', 'Failed to update RSVP status. Please try again or contact the event organizer.');
        }
    } else {
        setAlert('danger', 'Invalid token. Please contact the event organizer for assistance.');
    }
    
    // Redirect to the homepage
    header('Location: index.php');
    exit;
}
