<?php

/**
 * API Handler - Provides functions to interact with external APIs
 * 
 * This file contains helper functions for making API requests to external services.
 * For demo purposes, we'll simulate API responses.
 */

/**
 * Make a GET request to an external API
 *
 * @param string $endpoint The API endpoint
 * @param array $params Query parameters
 * @return array Response data
 */
function apiGet($endpoint, $params = [])
{
    // Build URL with query parameters
    $db = Database::getInstance();

    // Get services for lookup



    // In a real application, you would make an actual HTTP request
    // For demo purposes, we'll simulate a response

    // Simulate API delay
    usleep(rand(100000, 500000)); // 100-500ms delay

    // Return simulated response based on endpoint
    switch ($endpoint) {
        case 'packages':
            $packages = $db->query("SELECT * FROM packages");
            return [
                'status' => 200,
                'data' => $packages
            ];

        case 'services':
            $services = $db->query("SELECT * FROM services");
            return [
                'status' => 200,
                'data' => $services
            ];

        case 'bookings':
            $bookings = $db->query("SELECT * FROM bookings");
            return [
                'status' => 200,
                'data' =>   $bookings
            ];

        case 'guests':
            $guests = $db->query("SELECT * FROM guests");
            return [
                'status' => 200,
                'data' =>   $guests
            ];

        case 'users':
            $users = $db->query("SELECT * FROM users");
            return [
                'status' => 200,
                'data' =>   $users
            ];

        default:
            return [
                'status' => 404,
                'error' => 'Endpoint not found'
            ];
    }
}

/**
 * Send an email invitation to a guest
 *
 * @param array $guest Guest data
 * @param array $booking Booking data
 * @return bool Success status
 */
function sendInvitation($guest, $booking)
{
    // In a real application, you would send an actual email
    // For demo purposes, we'll simulate sending an email

    // create an email body
    $emailBody = "Dear {$guest['name']},\n\n";
    $emailBody .= "You are invited to the event at {$booking['event_place']} on {$booking['event_date']}.\n";
    $emailBody .= "Please RSVP by clicking the link below:\n";
    $emailBody .= "http://example.com/rsvp?booking_id={$booking['id']}&guest_id={$guest['id']}\n\n";
    $emailBody .= "Best regards,\n";
    $emailBody .= "Event Management Team";
    
    // send the email
    $to = $guest['email'];
    $subject = "Invitation to Event at {$booking['event_place']}";
    $headers = "From: test@gmail.com\r\n";
    $headers .= "Reply-To: {$guest['email']}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "X-Priority: 3\r\n"; // Normal priority
    $headers .= "X-MSMail-Priority: Normal\r\n"; // Normal priority
    $headers .= "X-Mailer: PHP/" . phpversion();

    mail($to, $subject, $emailBody, $headers);
    
    // Return success
    return true;
}
