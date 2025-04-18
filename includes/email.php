<?php
/**
 * Email Helper Functions
 */

/**
 * Send an email notification
 * 
 * @param string $to Email address of the recipient
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @param string $from Email address of the sender (optional)
 * @return bool True if email sent successfully, false otherwise
 */
function sendEmail($to, $subject, $message, $from = '') {
    // Set default sender if not provided
    if (empty($from)) {
        $from = 'noreply@' . $_SERVER['HTTP_HOST'];
    }
    
    // Set headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from,
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Send email
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send a booking confirmation email
 * 
 * @param array $booking Booking data
 * @param array $user User data
 * @param array $package Package data
 * @return bool True if email sent successfully, false otherwise
 */
function sendBookingConfirmationEmail($booking, $user, $package) {
    $to = $user['email'];
    $subject = 'Booking Confirmation - ' . APP_NAME;
    
    // Build message
    $message = '
    <html>
    <head>
        <title>Booking Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4e73df; color: white; padding: 10px 20px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #666; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Booking Confirmation</h1>
            </div>
            <div class="content">
                <p>Dear ' . $user['name'] . ',</p>
                <p>Your booking has been confirmed. Here are the details:</p>
                
                <table>
                    <tr>
                        <th>Booking ID</th>
                        <td>#' . $booking['id'] . '</td>
                    </tr>
                    <tr>
                        <th>Package</th>
                        <td>' . $package['name'] . '</td>
                    </tr>
                    <tr>
                        <th>Event Date</th>
                        <td>' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                    </tr>
                    <tr>
                        <th>Event Place</th>
                        <td>' . $booking['event_place'] . '</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>' . ucfirst($booking['status']) . '</td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td>$' . number_format($booking['total_price'] ?? $package['price'], 2) . '</td>
                    </tr>
                </table>
                
                <p>Thank you for choosing our services. If you have any questions, please contact us.</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return sendEmail($to, $subject, $message);
}

/**
 * Send a guest invitation email
 * 
 * @param array $guest Guest data
 * @param array $booking Booking data
 * @param array $user User data
 * @return bool True if email sent successfully, false otherwise
 */
function sendGuestInvitationEmail($guest, $booking, $user) {
    $to = $guest['email'];
    $subject = 'Event Invitation - ' . APP_NAME;
    
    // Build message
    $message = '
    <html>
    <head>
        <title>Event Invitation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4e73df; color: white; padding: 10px 20px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #666; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>You\'re Invited!</h1>
            </div>
            <div class="content">
                <p>Dear ' . $guest['name'] . ',</p>
                <p>' . $user['name'] . ' has invited you to an event. Here are the details:</p>
                
                <table>
                    <tr>
                        <th>Event Date</th>
                        <td>' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                    </tr>
                    <tr>
                        <th>Event Place</th>
                        <td>' . $booking['event_place'] . '</td>
                    </tr>
                </table>
                
                <p>Please let us know if you can attend:</p>
                
                <p style="text-align: center; margin: 30px 0;">
                    <a href="#" class="button" style="background-color: #28a745;">Yes, I\'ll Attend</a>
                    <a href="#" class="button" style="background-color: #dc3545; margin-left: 10px;">No, I Can\'t Attend</a>
                </p>
                
                <p>We hope to see you there!</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return sendEmail($to, $subject, $message);
}