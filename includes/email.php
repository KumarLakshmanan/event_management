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


function sendBookingConfirmationEmail($booking, $user, $package)
{
    $subject = 'Your Booking is Confirmed - ' . APP_NAME;

    $message = '
    <html>
    <body style="margin:0; padding:0; background-color:#f5f7fa; font-family:Segoe UI, sans-serif;">
        <div style="max-width:600px; margin:30px auto; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="background-color:#004aad; color:#ffffff; padding:25px 30px; text-align:center;">
                <h2 style="margin:0; font-size:24px;">🎉 Booking Confirmed</h2>
            </div>
            <div style="padding:30px;">
                <p style="font-size:16px; margin:0 0 15px;">Hello <strong>' . $user['name'] . '</strong>,</p>
                <p style="font-size:15px; color:#555;">Your booking has been confirmed. Here’s what you need to know:</p>
                <table style="width:100%; margin-top:20px; border-collapse:collapse;">
                    <tbody>';

    $details = [
        'Booking ID' => '#' . $booking['id'],
        'Package' => $package['name'],
        'Event Date' => date('F d, Y', strtotime($booking['event_date'])),
        'Event Location' => $booking['event_place'],
        'Status' => ucfirst($booking['status']),
        'Total Price' => '£' . number_format($booking['total_price'] ?? $package['price'], 2)
    ];

    foreach ($details as $label => $value) {
        $message .= '
                        <tr>
                            <td style="padding:10px 8px; border-bottom:1px solid #eee; font-weight:600; color:#333;">' . $label . '</td>
                            <td style="padding:10px 8px; border-bottom:1px solid #eee; text-align:right; color:#444;">' . $value . '</td>
                        </tr>';
    }

    $message .= '
                    </tbody>
                </table>
                <p style="font-size:14px; margin-top:25px; color:#666;">Thank you for choosing <strong>' . APP_NAME . '</strong>. If you have any questions, feel free to reach out to our support team.</p>
            </div>
            <div style="text-align:center; font-size:12px; padding:15px; background:#f0f0f0; color:#777;">
                <p>This is an automated message. Please do not reply.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($user['email'], $user['name'], $subject, $message);
}

function sendGuestInvitationEmail($guest, $booking, $user)
{
    $to_email = $guest['email'];
    $to_name = $guest['name'];
    $subject = 'You Are Invited to an Event - ' . APP_NAME;

    $message = '
    <html>
    <body style="margin:0; padding:0; background-color:#f6f8fc; font-family:Segoe UI, sans-serif;">
        <div style="max-width:600px; margin:30px auto; background-color:#ffffff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:hidden;">
            <div style="background-color:#8e24aa; color:#fff; text-align:center; padding:30px 20px;">
                <h2 style="margin:0; font-size:22px;">🎊 Youre Invited!</h2>
            </div>
            <div style="padding:25px;">
                <p style="font-size:16px;">Hi <strong>' . $guest['name'] . '</strong>,</p>
                <p style="font-size:15px; color:#444;">You are invited to a special event hosted by <strong>' . $user['name'] . '</strong>! Here are the details:</p>
                <table style="width:100%; margin-top:20px; border-collapse:collapse;">
                    <tr>
                        <td style="padding:10px; font-weight:600; color:#333;">📅 Date</td>
                        <td style="padding:10px; text-align:right; color:#555;">' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px; font-weight:600; color:#333;">📍 Location</td>
                        <td style="padding:10px; text-align:right; color:#555;">' . $booking['event_place'] . '</td>
                    </tr>
                </table>
                <div style="text-align:center; margin:30px 0;">
                    <a href="' . WEBSITE_ADDRESS . 'rsvp.php?status=1&booking_id=' . $booking['id'] . '&guest_id=' . $guest['id'] . '" style="background-color:#43a047; color:#fff; padding:12px 25px; text-decoration:none; border-radius:6px; margin:0 5px;">Accept</a>
                    <a href="' . WEBSITE_ADDRESS . 'rsvp.php?status=2&booking_id=' . $booking['id'] . '&guest_id=' . $guest['id'] . '" style="background-color:#e53935; color:#fff; padding:12px 25px; text-decoration:none; border-radius:6px; margin:0 5px;">Decline</a>
                </div>
                <p style="text-align:center; font-size:14px; color:#666;">We hope to see you there!</p>
            </div>
            <div style="text-align:center; font-size:12px; padding:15px; background:#f0f0f0; color:#777;">
                <p>This invitation was sent automatically by our system.</p>
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($to_email, $to_name, $subject, $message);
}
