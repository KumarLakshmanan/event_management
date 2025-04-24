<?php

require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailnew = new PHPMailer(true);

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
function sendEmail($to_email, $to_name, $subject, $message)
{
    global $mailnew;

    $mailnew->isSMTP();
    $mailnew->Host       = 'smtp.gmail.com';
    $mailnew->SMTPAuth   = true;
    $mailnew->Username   = MAIL_USERNAME;
    $mailnew->Password   = MAIL_PASSWORD;
    $mailnew->SMTPSecure = 'tls';
    $mailnew->Port       = 587;

    $mailnew->setFrom(MAIL_USERNAME, APP_NAME);
    $mailnew->addAddress($to_email, $to_name);

    $mailnew->isHTML(true);
    $mailnew->Subject = $subject;
    $mailnew->Body    = $message;
    $mailnew->AltBody = strip_tags($message);

    $mailnew->send();

    return true;
}

/**
 * Send a booking confirmation email
 */
function sendBookingConfirmationEmail($booking, $user, $package)
{
    $subject = 'Booking Confirmation - ' . APP_NAME;

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <div style="background-color: #007bff; color: white; padding: 20px; text-align: center;">
                <h1>Booking Confirmation</h1>
            </div>
            <div style="padding: 20px;">
                <p>Dear ' . $user['name'] . ',</p>
                <p>Your booking has been confirmed. Here are the details:</p>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Booking ID</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">#' . $booking['id'] . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Package</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . $package['name'] . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Date</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Place</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . $booking['event_place'] . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Status</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . ucfirst($booking['status']) . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Total Price</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">$' . number_format($booking['total_price'] ?? $package['price'], 2) . '</td>
                    </tr>
                </table>
                <p>Thank you for choosing our services. If you have any questions, please contact us.</p>
            </div>
            <div style="font-size: 12px; text-align: center; margin-top: 20px; color: #666; padding: 10px; background-color: #f1f1f1;">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    return sendEmail($user['email'], $user['name'], $subject, $message);
}
function sendGuestInvitationEmail($guest, $booking, $user)
{
    $to_email = $guest['email'];
    $to_name = $guest['name'];
    $subject = 'Event Invitation - ' . APP_NAME;

    // Build message
    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <div style="background-color: #007bff; color: white; padding: 20px; text-align: center;">
                <h1>You\'re Invited!</h1>
            </div>
            <div style="padding: 20px;">
                <p>Dear ' . $guest['name'] . ',</p>
                <p>' . $user['name'] . ' has invited you to an event. Here are the details:</p>
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Date</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                    </tr>
                    <tr>
                        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Place</th>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . $booking['event_place'] . '</td>
                    </tr>
                </table>
                
                <p>Please let us know if you can attend:</p>
                
                <p style="text-align: center; margin: 30px 0;">
                    <a href="' . WEBSITE_ADDRESS . 'rsvp.php?status=1&booking_id=' . $booking['id'] . '&guest_id=' . $guest['id'] . '" style="display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; background-color: #28a745; margin: 10px 5px;">Yes, I\'ll Attend</a>
                    <a href="' . WEBSITE_ADDRESS . 'rsvp.php?status=2&booking_id=' . $booking['id'] . '&guest_id=' . $guest['id'] . '" style="display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; background-color: #dc3545; margin: 10px 5px;">No, I Can\'t Attend</a>
                </p>
                
                <p>We hope to see you there!</p>
            </div>
            <div style="font-size: 12px; text-align: center; margin-top: 20px; color: #666; padding: 10px; background-color: #f1f1f1;">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    // Send email
    return sendEmail($to_email, $to_name, $subject, $message);
}
