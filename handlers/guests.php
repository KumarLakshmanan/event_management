<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respondWithError('Unauthorized', 401);
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
    case 'send_invite':
        handleSendInvite();
        break;
    default:
        respondWithError('Invalid action specified');
        break;
}

/**
 * Handle guest creation
 */
function handleCreateGuest() {
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $bookingId = filter_var($_POST['booking_id'] ?? 0, FILTER_VALIDATE_INT);
    $isClient = isset($_POST['client']) && $_POST['client'] == 1;
    
    if (empty($name) || empty($email) || !$bookingId) {
        respondWithError('All fields are required');
        return;
    }
    
    // Verify booking exists and user has permission to add guests
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get booking
        $booking = $db->querySingle(
            "SELECT * FROM bookings WHERE id = ?", 
            [$bookingId]
        );
        
        if (!$booking) {
            respondWithError('Booking not found');
            return;
        }
        
        // Check permission based on user role
        if ($_SESSION['user_role'] === 'client') {
            // Clients can only add guests to their own bookings
            if ($booking['user_id'] != $_SESSION['user_id']) {
                respondWithError('Permission denied', 403);
                return;
            }
        } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
            // Only admins and managers can add guests to any booking
            respondWithError('Permission denied', 403);
            return;
        }
        
        // Insert guest
        $guestData = [
            'booking_id' => $bookingId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'rsvp_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $guestId = insertRecord('guests', $guestData);
        
        if ($guestId) {
            respondWithSuccess('Guest added successfully', ['id' => $guestId]);
        } else {
            respondWithError('Failed to add guest');
        }
    } else {
        // Fallback to mock data
        $bookings = getMockData('bookings.json');
        $guests = getMockData('guests.json');
        
        // Find booking
        $bookingFound = false;
        foreach ($bookings as $booking) {
            if ($booking['id'] == $bookingId) {
                $bookingFound = true;
                
                // Check permission based on user role
                if ($_SESSION['user_role'] === 'client') {
                    // Clients can only add guests to their own bookings
                    if ($booking['user_id'] != $_SESSION['user_id']) {
                        respondWithError('Permission denied', 403);
                        return;
                    }
                } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
                    // Only admins and managers can add guests to any booking
                    respondWithError('Permission denied', 403);
                    return;
                }
                
                break;
            }
        }
        
        if (!$bookingFound) {
            respondWithError('Booking not found');
            return;
        }
        
        // Generate guest ID
        $id = count($guests) > 0 ? max(array_column($guests, 'id')) + 1 : 1;
        
        // Create new guest
        $newGuest = [
            'id' => $id,
            'booking_id' => $bookingId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'rsvp_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add guest to data
        $guests[] = $newGuest;
        
        // Save data
        saveMockData('guests.json', $guests);
        
        respondWithSuccess('Guest added successfully', ['id' => $id]);
    }
}

/**
 * Handle guest update
 */
function handleUpdateGuest() {
    // Validate input
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $rsvpStatus = sanitizeInput($_POST['rsvp_status'] ?? 'pending');
    $bookingId = filter_var($_POST['booking_id'] ?? 0, FILTER_VALIDATE_INT);
    $isClient = isset($_POST['client']) && $_POST['client'] == 1;
    
    if (!$id || empty($name) || empty($email) || !$bookingId) {
        respondWithError('All fields are required');
        return;
    }
    
    // Update guest in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get guest and booking to verify permission
        $guest = $db->querySingle(
            "SELECT * FROM guests WHERE id = ?", 
            [$id]
        );
        
        if (!$guest) {
            respondWithError('Guest not found');
            return;
        }
        
        // Verify booking
        $booking = $db->querySingle(
            "SELECT * FROM bookings WHERE id = ?", 
            [$guest['booking_id']]
        );
        
        if (!$booking) {
            respondWithError('Booking not found');
            return;
        }
        
        // Check permission based on user role
        if ($_SESSION['user_role'] === 'client') {
            // Clients can only update guests for their own bookings
            if ($booking['user_id'] != $_SESSION['user_id']) {
                respondWithError('Permission denied', 403);
                return;
            }
        } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
            // Only admins and managers can update guests for any booking
            respondWithError('Permission denied', 403);
            return;
        }
        
        // Update guest
        $guestData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'rsvp_status' => $rsvpStatus
        ];
        
        $result = updateRecord('guests', $id, $guestData);
        
        if ($result) {
            respondWithSuccess('Guest updated successfully');
        } else {
            respondWithError('Failed to update guest');
        }
    } else {
        // Fallback to mock data
        $bookings = getMockData('bookings.json');
        $guests = getMockData('guests.json');
        $updated = false;
        
        // Find guest
        foreach ($guests as $index => $guest) {
            if ($guest['id'] == $id) {
                // Get booking to verify permission
                $bookingFound = false;
                $booking = null;
                
                foreach ($bookings as $b) {
                    if ($b['id'] == $guest['booking_id']) {
                        $booking = $b;
                        $bookingFound = true;
                        break;
                    }
                }
                
                if (!$bookingFound) {
                    respondWithError('Booking not found');
                    return;
                }
                
                // Check permission based on user role
                if ($_SESSION['user_role'] === 'client') {
                    // Clients can only update guests for their own bookings
                    if ($booking['user_id'] != $_SESSION['user_id']) {
                        respondWithError('Permission denied', 403);
                        return;
                    }
                } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
                    // Only admins and managers can update guests for any booking
                    respondWithError('Permission denied', 403);
                    return;
                }
                
                // Update guest
                $guests[$index]['name'] = $name;
                $guests[$index]['email'] = $email;
                $guests[$index]['phone'] = $phone;
                $guests[$index]['rsvp_status'] = $rsvpStatus;
                
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            saveMockData('guests.json', $guests);
            respondWithSuccess('Guest updated successfully');
        } else {
            respondWithError('Guest not found');
        }
    }
}

/**
 * Handle guest deletion
 */
function handleDeleteGuest() {
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    $bookingId = filter_var($_GET['booking_id'] ?? 0, FILTER_VALIDATE_INT);
    $isClient = isset($_GET['client']) && $_GET['client'] == 1;
    
    if (!$id || !$bookingId) {
        respondWithError('Invalid guest ID or booking ID');
        return;
    }
    
    // Delete guest from database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get guest to verify booking
        $guest = $db->querySingle(
            "SELECT * FROM guests WHERE id = ?", 
            [$id]
        );
        
        if (!$guest) {
            respondWithError('Guest not found');
            return;
        }
        
        // Verify booking
        $booking = $db->querySingle(
            "SELECT * FROM bookings WHERE id = ?", 
            [$guest['booking_id']]
        );
        
        if (!$booking) {
            respondWithError('Booking not found');
            return;
        }
        
        // Check permission based on user role
        if ($_SESSION['user_role'] === 'client') {
            // Clients can only delete guests for their own bookings
            if ($booking['user_id'] != $_SESSION['user_id']) {
                respondWithError('Permission denied', 403);
                return;
            }
        } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
            // Only admins and managers can delete guests for any booking
            respondWithError('Permission denied', 403);
            return;
        }
        
        // Delete guest
        $result = $db->execute("DELETE FROM guests WHERE id = ?", [$id]);
        
        if ($result) {
            // Redirect back to appropriate page
            $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
            header("Location: $redirectUrl");
            exit;
        } else {
            // Redirect with error
            $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
            header("Location: $redirectUrl&error=" . urlencode('Failed to delete guest'));
            exit;
        }
    } else {
        // Fallback to mock data
        $bookings = getMockData('bookings.json');
        $guests = getMockData('guests.json');
        $deleted = false;
        $guestExists = false;
        
        // Find guest
        foreach ($guests as $index => $guest) {
            if ($guest['id'] == $id) {
                $guestExists = true;
                
                // Get booking to verify permission
                $bookingFound = false;
                $booking = null;
                
                foreach ($bookings as $b) {
                    if ($b['id'] == $guest['booking_id']) {
                        $booking = $b;
                        $bookingFound = true;
                        break;
                    }
                }
                
                if (!$bookingFound) {
                    // Redirect with error
                    $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
                    header("Location: $redirectUrl&error=" . urlencode('Booking not found'));
                    exit;
                }
                
                // Check permission based on user role
                if ($_SESSION['user_role'] === 'client') {
                    // Clients can only delete guests for their own bookings
                    if ($booking['user_id'] != $_SESSION['user_id']) {
                        // Redirect with error
                        $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
                        header("Location: $redirectUrl&error=" . urlencode('Permission denied'));
                        exit;
                    }
                } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
                    // Only admins and managers can delete guests for any booking
                    // Redirect with error
                    $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
                    header("Location: $redirectUrl&error=" . urlencode('Permission denied'));
                    exit;
                }
                
                // Remove guest
                array_splice($guests, $index, 1);
                $deleted = true;
                break;
            }
        }
        
        if ($deleted) {
            saveMockData('guests.json', $guests);
            
            // Redirect back to appropriate page
            $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
            header("Location: $redirectUrl");
            exit;
        } else if ($guestExists) {
            // Redirect with error
            $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
            header("Location: $redirectUrl&error=" . urlencode('Failed to delete guest'));
            exit;
        } else {
            // Redirect with error
            $redirectUrl = $isClient ? "../pages/my-guests.php?booking=$bookingId" : "../pages/guests.php?booking=$bookingId";
            header("Location: $redirectUrl&error=" . urlencode('Guest not found'));
            exit;
        }
    }
}

/**
 * Handle sending invite to guest
 */
function handleSendInvite() {
    // Validate input
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $bookingId = filter_var($_POST['booking_id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!$id || !$bookingId) {
        respondWithError('Invalid guest ID or booking ID');
        return;
    }
    
    // Get guest from database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Get guest
        $guest = $db->querySingle(
            "SELECT * FROM guests WHERE id = ?", 
            [$id]
        );
        
        if (!$guest) {
            respondWithError('Guest not found');
            return;
        }
        
        // Verify booking
        $booking = $db->querySingle(
            "SELECT b.*, p.name as package_name 
             FROM bookings b 
             LEFT JOIN packages p ON b.package_id = p.id
             WHERE b.id = ?", 
            [$guest['booking_id']]
        );
        
        if (!$booking) {
            respondWithError('Booking not found');
            return;
        }
        
        // Check permission based on user role
        if ($_SESSION['user_role'] === 'client') {
            // Clients can only send invites for their own bookings
            if ($booking['user_id'] != $_SESSION['user_id']) {
                respondWithError('Permission denied', 403);
                return;
            }
        } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
            // Only admins and managers can send invites for any booking
            respondWithError('Permission denied', 403);
            return;
        }
        
        // In a real application, you would send an email here
        // For the demo, we'll just simulate success
        
        // Update guest's last invited timestamp
        $db->execute(
            "UPDATE guests SET last_invited_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        );
        
        respondWithSuccess('Invitation sent successfully');
    } else {
        // Fallback to mock data
        $bookings = getMockData('bookings.json');
        $guests = getMockData('guests.json');
        $packages = getMockData('packages.json');
        $guestFound = false;
        
        // Find guest
        foreach ($guests as $index => $guest) {
            if ($guest['id'] == $id) {
                $guestFound = true;
                
                // Get booking
                $bookingFound = false;
                $booking = null;
                
                foreach ($bookings as $b) {
                    if ($b['id'] == $guest['booking_id']) {
                        $booking = $b;
                        $bookingFound = true;
                        break;
                    }
                }
                
                if (!$bookingFound) {
                    respondWithError('Booking not found');
                    return;
                }
                
                // Check permission based on user role
                if ($_SESSION['user_role'] === 'client') {
                    // Clients can only send invites for their own bookings
                    if ($booking['user_id'] != $_SESSION['user_id']) {
                        respondWithError('Permission denied', 403);
                        return;
                    }
                } else if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
                    // Only admins and managers can send invites for any booking
                    respondWithError('Permission denied', 403);
                    return;
                }
                
                // Get package name
                $packageName = 'Unknown Package';
                foreach ($packages as $p) {
                    if ($p['id'] == $booking['package_id']) {
                        $packageName = $p['name'];
                        break;
                    }
                }
                
                // In a real application, you would send an email here
                // For the demo, we'll just simulate success
                
                // Update guest's last invited timestamp
                $guests[$index]['last_invited_at'] = date('Y-m-d H:i:s');
                
                // Save updated guests data
                saveMockData('guests.json', $guests);
                
                respondWithSuccess('Invitation sent successfully');
                return;
            }
        }
        
        if (!$guestFound) {
            respondWithError('Guest not found');
        }
    }
}

/**
 * Respond with success JSON
 */
function respondWithSuccess($message, $data = []) {
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
function respondWithError($message, $code = 400) {
    http_response_code($code);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
?>