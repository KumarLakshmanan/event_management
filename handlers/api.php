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
function apiGet($endpoint, $params = []) {
    // Build URL with query parameters
    $url = API_URL . '/' . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    // In a real application, you would make an actual HTTP request
    // For demo purposes, we'll simulate a response
    
    // Simulate API delay
    usleep(rand(100000, 500000)); // 100-500ms delay
    
    // Return simulated response based on endpoint
    switch ($endpoint) {
        case 'packages':
            return [
                'status' => 200,
                'data' => getMockData('packages.json')
            ];
            
        case 'services':
            return [
                'status' => 200,
                'data' => getMockData('services.json')
            ];
            
        case 'bookings':
            return [
                'status' => 200,
                'data' => getMockData('bookings.json')
            ];
            
        case 'guests':
            return [
                'status' => 200,
                'data' => getMockData('guests.json')
            ];
            
        case 'users':
            return [
                'status' => 200,
                'data' => getMockData('users.json')
            ];
            
        default:
            return [
                'status' => 404,
                'error' => 'Endpoint not found'
            ];
    }
}

/**
 * Make a POST request to an external API
 *
 * @param string $endpoint The API endpoint
 * @param array $data Request data
 * @return array Response data
 */
function apiPost($endpoint, $data = []) {
    // In a real application, you would make an actual HTTP request
    // For demo purposes, we'll simulate a response
    
    // Simulate API delay
    usleep(rand(100000, 500000)); // 100-500ms delay
    
    // Return simulated response
    return [
        'status' => 200,
        'message' => 'Data saved successfully',
        'data' => $data
    ];
}

/**
 * Make a PUT request to an external API
 *
 * @param string $endpoint The API endpoint
 * @param array $data Request data
 * @return array Response data
 */
function apiPut($endpoint, $data = []) {
    // In a real application, you would make an actual HTTP request
    // For demo purposes, we'll simulate a response
    
    // Simulate API delay
    usleep(rand(100000, 500000)); // 100-500ms delay
    
    // Return simulated response
    return [
        'status' => 200,
        'message' => 'Data updated successfully',
        'data' => $data
    ];
}

/**
 * Make a DELETE request to an external API
 *
 * @param string $endpoint The API endpoint
 * @param array $params Query parameters
 * @return array Response data
 */
function apiDelete($endpoint, $params = []) {
    // Build URL with query parameters
    $url = API_URL . '/' . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    // In a real application, you would make an actual HTTP request
    // For demo purposes, we'll simulate a response
    
    // Simulate API delay
    usleep(rand(100000, 500000)); // 100-500ms delay
    
    // Return simulated response
    return [
        'status' => 200,
        'message' => 'Data deleted successfully'
    ];
}

/**
 * Send an email invitation to a guest
 *
 * @param array $guest Guest data
 * @param array $booking Booking data
 * @return bool Success status
 */
function sendInvitation($guest, $booking) {
    // In a real application, you would send an actual email
    // For demo purposes, we'll simulate sending an email
    
    // Simulate delay
    usleep(rand(500000, 1000000)); // 500-1000ms delay
    
    // Return success
    return true;
}
?>
