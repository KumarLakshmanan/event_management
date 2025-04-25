<?php
// Include PHPMailer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative
 * @return bool Success or failure
 */
function sendEmail($to, $subject, $body, $altBody = '') {
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Configure PHPMailer
        // For demonstration purposes, we'll use a send from the server directly
        // In production, you would configure SMTP settings
        
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your_username'; // Replace with your SMTP username
        $mail->Password = 'your_password'; // Replace with your SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        // Set sender and recipient
        $mail->setFrom('events@eventplanner.com', APP_NAME);
        $mail->addAddress($to);
        
        // Set email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send a guest RSVP invitation email
 * 
 * @param array $guest Guest data
 * @param array $booking Booking data
 * @return bool Success or failure
 */
function sendGuestInvitation($guest, $booking) {
    // Generate unique token for the guest's RSVP
    $token = md5($guest['id'] . $guest['created_at'] . $booking['id']);
    
    // Build accept and decline URLs
    $acceptUrl = APP_URL . '/rsvp.php?token=' . $token . '&action=accept';
    $declineUrl = APP_URL . '/rsvp.php?token=' . $token . '&action=decline';
    
    // Email subject
    $subject = 'You\'re Invited: ' . htmlspecialchars($booking['event_location']) . ' on ' . formatDate($booking['event_date']);
    
    // Email body
    $body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center;">
            <h2>You\'re Invited!</h2>
        </div>
        <div style="padding: 20px;">
            <p>Hello ' . htmlspecialchars($guest['name']) . ',</p>
            <p>You have been invited to an event at <strong>' . htmlspecialchars($booking['event_location']) . '</strong> on <strong>' . formatDate($booking['event_date']) . '</strong>.</p>
            <p>Please let us know if you can attend:</p>
            
            <div style="margin: 30px 0; text-align: center;">
                <a href="' . $acceptUrl . '" style="display: inline-block; background-color: #10B981; color: white; padding: 10px 20px; margin: 0 10px; text-decoration: none; border-radius: 5px;">Yes, I\'ll Attend</a>
                <a href="' . $declineUrl . '" style="display: inline-block; background-color: #EF4444; color: white; padding: 10px 20px; margin: 0 10px; text-decoration: none; border-radius: 5px;">No, I Can\'t Attend</a>
            </div>
            
            <p>Thank you!</p>
            <p><em>This is an automated message from ' . APP_NAME . '.</em></p>
        </div>
    </div>';
    
    // Plain text alternative
    $altBody = 'Hello ' . $guest['name'] . ',
    
You have been invited to an event at ' . $booking['event_location'] . ' on ' . formatDate($booking['event_date']) . '.

To accept: ' . $acceptUrl . '
To decline: ' . $declineUrl . '

Thank you!
This is an automated message from ' . APP_NAME;
    
    // Send the email
    return sendEmail($guest['email'], $subject, $body, $altBody);
}
?>