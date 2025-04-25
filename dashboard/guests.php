<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/Booking.php';

// Require login for all actions
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controllers
$guestController = new GuestController();
$bookingController = new BookingController();

// Get the current user
$user = getCurrentUser();

// Check for guest ID in URL
$guestId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Check for booking ID in URL
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Initialize variables
$errors = [];
$success = false;
$guest = [];
$booking = [];
$guests = [];
$rsvpStats = [];

// If we have a booking ID, get the booking details
if ($bookingId) {
    $booking = $bookingController->getBookingDetails($bookingId);
    
    // Check if booking exists
    if (!$booking) {
        setFlashMessage('Booking not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
    
    // Check if user has permission to view this booking's guests
    if (!hasPermission('manage_packages') && $booking['user_id'] != $user['id']) {
        setFlashMessage('You do not have permission to manage guests for this booking.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
}

// Process guest actions (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle add guest form submission
    if (isset($_POST['add_guest'])) {
        $guestData = [
            'booking_id' => (int)$_POST['booking_id'],
            'name' => sanitizeInput($_POST['name']),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'phone' => sanitizeInput($_POST['phone']),
            'rsvp_status' => RSVP_PENDING
        ];
        
        // Check if user has permission for this booking
        $bookingCheck = $bookingController->getBookingDetails($guestData['booking_id']);
        if (!$bookingCheck || (!hasPermission('manage_packages') && $bookingCheck['user_id'] != $user['id'])) {
            setFlashMessage('You do not have permission to add guests to this booking.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/bookings.php');
            exit;
        }
        
        $newGuestId = $guestController->addGuest($guestData);
        
        if ($newGuestId) {
            setFlashMessage('Guest added successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/guests.php?booking_id=' . $guestData['booking_id']);
            exit;
        } else {
            $errors[] = 'Failed to add guest. Please check your input and try again.';
        }
    }
    
    // Handle bulk add guests form submission
    if (isset($_POST['bulk_add_guests'])) {
        $bookingId = (int)$_POST['booking_id'];
        $guestList = $_POST['guest_list'];
        
        // Check if user has permission for this booking
        $bookingCheck = $bookingController->getBookingDetails($bookingId);
        if (!$bookingCheck || (!hasPermission('manage_packages') && $bookingCheck['user_id'] != $user['id'])) {
            setFlashMessage('You do not have permission to add guests to this booking.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/bookings.php');
            exit;
        }
        
        // Parse guest list (name, email, phone separated by commas, one guest per line)
        $guestsAdded = $guestController->bulkAddGuests($bookingId, $guestList);
        
        if ($guestsAdded > 0) {
            setFlashMessage("$guestsAdded guests added successfully!", 'success');
            header('Location: ' . APP_URL . 'dashboard/guests.php?booking_id=' . $bookingId);
            exit;
        } else {
            $errors[] = 'Failed to add guests. Please check your input format and try again.';
        }
    }
    
    // Handle edit guest form submission
    if (isset($_POST['edit_guest'])) {
        $guestData = [
            'id' => (int)$_POST['guest_id'],
            'name' => sanitizeInput($_POST['name']),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'phone' => sanitizeInput($_POST['phone']),
            'rsvp_status' => sanitizeInput($_POST['rsvp_status'])
        ];
        
        // Check if user has permission for this guest
        $guestCheck = $guestController->getGuestWithBooking($guestData['id']);
        if (!$guestCheck || (!hasPermission('manage_packages') && $guestCheck['user_id'] != $user['id'])) {
            setFlashMessage('You do not have permission to edit this guest.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/bookings.php');
            exit;
        }
        
        $result = $guestController->updateGuest($guestData);
        
        if ($result) {
            setFlashMessage('Guest updated successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/guests.php?booking_id=' . $guestCheck['booking_id']);
            exit;
        } else {
            $errors[] = 'Failed to update guest.';
        }
    }
    
    // Handle delete guest form submission
    if (isset($_POST['delete_guest'])) {
        $guestId = (int)$_POST['guest_id'];
        
        // Check if user has permission for this guest
        $guestCheck = $guestController->getGuestWithBooking($guestId);
        if (!$guestCheck || (!hasPermission('manage_packages') && $guestCheck['user_id'] != $user['id'])) {
            setFlashMessage('You do not have permission to delete this guest.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/bookings.php');
            exit;
        }
        
        $result = $guestController->deleteGuest($guestId);
        
        if ($result) {
            setFlashMessage('Guest deleted successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/guests.php?booking_id=' . $guestCheck['booking_id']);
            exit;
        } else {
            $errors[] = 'Failed to delete guest.';
        }
    }
    
    // Handle send invitations form submission
    if (isset($_POST['send_invitations'])) {
        $bookingId = (int)$_POST['booking_id'];
        
        // Check if user has permission for this booking
        $bookingCheck = $bookingController->getBookingDetails($bookingId);
        if (!$bookingCheck || (!hasPermission('manage_packages') && $bookingCheck['user_id'] != $user['id'])) {
            setFlashMessage('You do not have permission to send invitations for this booking.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/bookings.php');
            exit;
        }
        
        $sentCount = $guestController->sendInvitations($bookingId);
        
        if ($sentCount > 0) {
            setFlashMessage("Invitations sent to $sentCount guests!", 'success');
            header('Location: ' . APP_URL . 'dashboard/guests.php?booking_id=' . $bookingId);
            exit;
        } else {
            $errors[] = 'No invitations sent. Ensure guests have email addresses.';
        }
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_rsvp' && isset($_POST['guest_id']) && isset($_POST['status'])) {
            $guestId = (int)$_POST['guest_id'];
            $status = sanitizeInput($_POST['status']);
            
            // Check if user has permission for this guest
            $guestCheck = $guestController->getGuestWithBooking($guestId);
            if (!$guestCheck || (!hasPermission('manage_packages') && $guestCheck['user_id'] != $user['id'])) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            
            $result = $guestController->updateRsvpStatus($guestId, $status);
            
            echo json_encode(['success' => $result]);
            exit;
        }
    }
    
    // Return error for invalid requests
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle different page actions
if ($action === 'add' && $bookingId) {
    // Add new guest
    $pageTitle = 'Add Guests';
    $template = 'add';
} elseif ($action === 'edit' && $guestId) {
    // Edit existing guest
    $guest = $guestController->getGuestWithBooking($guestId);
    if (!$guest) {
        setFlashMessage('Guest not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
    
    // Check if user has permission to edit this guest
    if (!hasPermission('manage_packages') && $guest['user_id'] != $user['id']) {
        setFlashMessage('You do not have permission to edit this guest.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
    
    $booking = $bookingController->getBookingDetails($guest['booking_id']);
    $pageTitle = 'Edit Guest';
    $template = 'edit';
} elseif ($action === 'delete' && $guestId) {
    // Confirm delete guest
    $guest = $guestController->getGuestWithBooking($guestId);
    if (!$guest) {
        setFlashMessage('Guest not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
    
    // Check if user has permission to delete this guest
    if (!hasPermission('manage_packages') && $guest['user_id'] != $user['id']) {
        setFlashMessage('You do not have permission to delete this guest.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/bookings.php');
        exit;
    }
    
    $booking = $bookingController->getBookingDetails($guest['booking_id']);
    $pageTitle = 'Delete Guest';
    $template = 'delete';
} elseif ($bookingId) {
    // List all guests for a booking
    $booking = $bookingController->getBookingDetails($bookingId);
    
    // Get guests and RSVP stats
    $guests = $guestController->getGuestsByBooking($bookingId);
    $rsvpStats = $guestController->getRsvpStats($bookingId);
    
    $pageTitle = 'Manage Guest List';
    $template = 'list';
} else {
    // If no booking ID provided, redirect to bookings page
    setFlashMessage('Please select a booking to manage guests.', 'info');
    header('Location: ' . APP_URL . 'dashboard/bookings.php');
    exit;
}

// Set up page title and sidebar flag for template
$title = $pageTitle;
$showSidebar = true;

// Include header
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?php echo $pageTitle; ?></h1>
        
        <div>
            <?php if ($template === 'list'): ?>
                <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $bookingId; ?>&action=add" class="btn btn-primary me-2">
                    <i class="fas fa-user-plus me-2"></i>Add Guests
                </a>
            <?php endif; ?>
            
            <a href="<?php echo APP_URL; ?>dashboard/bookings.php?id=<?php echo $booking['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Booking
            </a>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Booking Info Banner -->
    <div class="alert alert-info mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="alert-heading">Event: <?php echo htmlspecialchars($booking['package_name']); ?></h5>
                <p class="mb-0">
                    <strong>Date:</strong> <?php echo formatDate($booking['event_date']); ?> |
                    <strong>Location:</strong> <?php echo htmlspecialchars($booking['event_location']); ?> |
                    <strong>Status:</strong> <?php echo getStatusBadge($booking['status']); ?>
                </p>
            </div>
            <?php if (isset($rsvpStats) && !empty($rsvpStats)): ?>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-primary rounded-pill">Total Guests: <?php echo $rsvpStats['total']; ?></span>
                    <span class="badge bg-success rounded-pill">Accepted: <?php echo $rsvpStats['accepted']; ?></span>
                    <span class="badge bg-danger rounded-pill">Declined: <?php echo $rsvpStats['declined']; ?></span>
                    <span class="badge bg-warning rounded-pill">Pending: <?php echo $rsvpStats['pending']; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($template === 'list'): ?>
        <!-- Guest List View -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Guest List</h5>
                    </div>
                    <?php if (!empty($guests)): ?>
                        <div class="col-auto">
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Send invitations to all guests with email addresses?');">
                                <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                <button type="submit" name="send_invitations" class="btn btn-success btn-sm">
                                    <i class="fas fa-envelope me-1"></i>Send Invitations
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($guests)): ?>
                    <div class="text-center p-4">
                        <p>No guests added yet for this booking.</p>
                        <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $bookingId; ?>&action=add" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Add Guests
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>RSVP Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guests as $guest): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($guest['name']); ?></td>
                                        <td><?php echo !empty($guest['email']) ? htmlspecialchars($guest['email']) : '<span class="text-muted">Not provided</span>'; ?></td>
                                        <td><?php echo !empty($guest['phone']) ? htmlspecialchars($guest['phone']) : '<span class="text-muted">Not provided</span>'; ?></td>
                                        <td>
                                            <!-- <select class="form-select form-select-sm rsvp-status-select" 
                                                    data-guest-id="<?php echo $guest['id']; ?>"
                                                    <?php echo $booking['status'] === STATUS_CANCELLED ? 'disabled' : ''; ?>>
                                                <option value="pending" <?php echo $guest['rsvp_status'] === RSVP_PENDING ? 'selected' : ''; ?>>Pending</option>
                                                <option value="accepted" <?php echo $guest['rsvp_status'] === RSVP_ACCEPTED ? 'selected' : ''; ?>>Accepted</option>
                                                <option value="declined" <?php echo $guest['rsvp_status'] === RSVP_DECLINED ? 'selected' : ''; ?>>Declined</option>
                                            </select> -->
                                            <?php echo getRsvpStatusName($guest['rsvp_status']); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>dashboard/guests.php?action=edit&id=<?php echo $guest['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>dashboard/guests.php?action=delete&id=<?php echo $guest['id']; ?>" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($guests)): ?>
                <div class="card-footer">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Change RSVP status using the dropdown menu. Changes are saved automatically.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
    <?php elseif ($template === 'add'): ?>
        <!-- Add Guest Form -->
        <div class="row">
            <!-- Individual Guest Form -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Add Individual Guest</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Guest Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <div class="form-text">Email is optional, but needed for sending invitations.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="add_guest" class="btn btn-primary">Add Guest</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Add Guests Form -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Bulk Add Guests</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                            
                            <div class="mb-3">
                                <label for="guest_list" class="form-label">Guest List</label>
                                <textarea class="form-control" id="guest_list" name="guest_list" rows="10" placeholder="One guest per line. Format: Name, Email, Phone"></textarea>
                                <div class="form-text">
                                    Enter one guest per line in the format: Name, Email, Phone<br>
                                    Email and Phone are optional. For example:<br>
                                    <code>John Doe, john@example.com, 555-1234</code><br>
                                    <code>Jane Smith, jane@example.com</code><br>
                                    <code>Bob Johnson</code>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="bulk_add_guests" class="btn btn-primary">Add All Guests</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($template === 'edit'): ?>
        <!-- Edit Guest Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Edit Guest</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="guest_id" value="<?php echo $guest['id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Guest Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($guest['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rsvp_status" class="form-label">RSVP Status</label>
                            <select class="form-select" id="rsvp_status" name="rsvp_status">
                                <option value="pending" <?php echo $guest['rsvp_status'] === RSVP_PENDING ? 'selected' : ''; ?>>Pending</option>
                                <option value="accepted" <?php echo $guest['rsvp_status'] === RSVP_ACCEPTED ? 'selected' : ''; ?>>Accepted</option>
                                <option value="declined" <?php echo $guest['rsvp_status'] === RSVP_DECLINED ? 'selected' : ''; ?>>Declined</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($guest['email']); ?>">
                            <div class="form-text">Email is optional, but needed for sending invitations.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($guest['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $guest['booking_id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="edit_guest" class="btn btn-primary">Update Guest</button>
                    </div>
                </form>
            </div>
        </div>
        
    <?php elseif ($template === 'delete'): ?>
        <!-- Delete Guest Confirmation -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to remove <strong><?php echo htmlspecialchars($guest['name']); ?></strong> from the guest list?</p>
                <p>This action cannot be undone.</p>
                
                <div class="alert alert-info">
                    <h6>Guest Details:</h6>
                    <ul class="mb-0">
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($guest['name']); ?></li>
                        <li><strong>Email:</strong> <?php echo !empty($guest['email']) ? htmlspecialchars($guest['email']) : 'Not provided'; ?></li>
                        <li><strong>Phone:</strong> <?php echo !empty($guest['phone']) ? htmlspecialchars($guest['phone']) : 'Not provided'; ?></li>
                        <li><strong>RSVP Status:</strong> <?php echo getRsvpStatusName($guest['rsvp_status']); ?></li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="guest_id" value="<?php echo $guest['id']; ?>">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/guests.php?booking_id=<?php echo $guest['booking_id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="delete_guest" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Delete Guest
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle RSVP status changes
        const rsvpSelects = document.querySelectorAll('.rsvp-status-select');
        if (rsvpSelects.length > 0) {
            rsvpSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    const guestId = this.getAttribute('data-guest-id');
                    const status = this.value;
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('action', 'update_rsvp');
                    formData.append('guest_id', guestId);
                    formData.append('status', status);
                    
                    // Send AJAX request
                    fetch('<?php echo APP_URL; ?>dashboard/guests.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Flash success message
                            showToast('RSVP status updated', 'success');
                        } else {
                            showToast('Error updating RSVP status', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error updating RSVP status', 'danger');
                    });
                });
            });
        }
    });
</script>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
