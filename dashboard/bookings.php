<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Package.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Guest.php';

// Require login for all actions
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$bookingController = new BookingController();

// Get the current user
$user = getCurrentUser();

// Check for booking ID in URL
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Check for package ID in URL (for creating a booking)
$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : null;

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get status filter from URL
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Initialize variables
$errors = [];
$success = false;
$booking = [];
$package = [];
$services = [];
$guests = [];
$rsvpStats = [];

// Process booking actions (create/update/cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle create booking form submission
    if (isset($_POST['create_booking'])) {
        // Validate form data
        $bookingData = [
            'user_id' => $user['id'],
            'package_id' => isset($_POST['package_id']) ? (int)$_POST['package_id'] : null,
            'event_date' => sanitizeInput($_POST['event_date']),
            'event_location' => sanitizeInput($_POST['event_location']),
            'total_price' => isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0,
            'services' => isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : []
        ];
        
        // Create booking
        $newBookingId = $bookingController->createBooking($bookingData);
        
        if ($newBookingId) {
            setFlashMessage('Booking created successfully!', 'success');
            header('Location: ' . APP_URL . '/dashboard/bookings.php?id=' . $newBookingId);
            exit;
        } else {
            $errors[] = 'Failed to create booking. Please check your input and try again.';
        }
    }
    
    // Handle update booking status
    if (isset($_POST['update_status'])) {
        // Check if user has permission
        if (!hasPermission('manage_packages') && $user['id'] != $_POST['user_id']) {
            setFlashMessage('You do not have permission to update this booking.', 'danger');
            header('Location: ' . APP_URL . '/dashboard/bookings.php');
            exit;
        }
        
        $updateData = [
            'booking_id' => (int)$_POST['booking_id'],
            'status' => sanitizeInput($_POST['status'])
        ];
        
        $result = $bookingController->updateStatus($updateData);
        
        if ($result) {
            setFlashMessage('Booking status updated successfully!', 'success');
            header('Location: ' . APP_URL . '/dashboard/bookings.php?id=' . $updateData['booking_id']);
            exit;
        } else {
            $errors[] = 'Failed to update booking status.';
        }
    }
    
    // Handle apply discount
    if (isset($_POST['apply_discount']) && hasPermission('apply_discount')) {
        $discountData = [
            'booking_id' => (int)$_POST['booking_id'],
            'discount_amount' => (float)$_POST['discount_amount']
        ];
        
        $result = $bookingController->applyDiscount($discountData);
        
        if ($result) {
            setFlashMessage('Discount applied successfully!', 'success');
            header('Location: ' . APP_URL . '/dashboard/bookings.php?id=' . $discountData['booking_id']);
            exit;
        } else {
            $errors[] = 'Failed to apply discount.';
        }
    }
    
    // Handle cancel booking
    if (isset($_POST['cancel_booking'])) {
        // Check if user has permission
        if (!hasPermission('manage_packages') && $user['id'] != $_POST['user_id']) {
            setFlashMessage('You do not have permission to cancel this booking.', 'danger');
            header('Location: ' . APP_URL . '/dashboard/bookings.php');
            exit;
        }
        
        $result = $bookingController->cancelBooking((int)$_POST['booking_id']);
        
        if ($result) {
            setFlashMessage('Booking cancelled successfully!', 'success');
            header('Location: ' . APP_URL . '/dashboard/bookings.php');
            exit;
        } else {
            $errors[] = 'Failed to cancel booking.';
        }
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_status' && isset($_POST['booking_id']) && isset($_POST['status'])) {
            // Check permission
            if (!hasPermission('manage_packages')) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            
            $updateData = [
                'booking_id' => (int)$_POST['booking_id'],
                'status' => sanitizeInput($_POST['status'])
            ];
            
            $result = $bookingController->updateStatus($updateData);
            
            echo json_encode(['success' => $result]);
            exit;
        }
        
        if ($_POST['action'] === 'apply_discount' && isset($_POST['booking_id']) && isset($_POST['discount_amount'])) {
            // Check permission
            if (!hasPermission('apply_discount')) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                exit;
            }
            
            $discountData = [
                'booking_id' => (int)$_POST['booking_id'],
                'discount_amount' => (float)$_POST['discount_amount']
            ];
            
            $result = $bookingController->applyDiscount($discountData);
            $booking = $bookingController->getBookingDetails($discountData['booking_id']);
            
            echo json_encode([
                'success' => $result,
                'discount' => $booking['discount_applied'],
                'new_total' => $booking['total_price'] - $booking['discount_applied']
            ]);
            exit;
        }
    }
    
    // Return error for invalid requests
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle different page actions
if ($action === 'create') {
    // Create new booking
    $pageTitle = 'Book an Event';
    $template = 'create';
    
    // Get package if specified
    if ($packageId) {
        $package = $bookingController->getPackageDetails($packageId);
        if (!$package) {
            setFlashMessage('Package not found.', 'danger');
            header('Location: ' . APP_URL . '/dashboard/packages.php');
            exit;
        }
    }
    
    // Get all packages and services for selecting
    $packages = $bookingController->getAllPackages();
    $services = $bookingController->getAllServices();
} elseif ($bookingId) {
    // View booking details
    $booking = $bookingController->getBookingDetails($bookingId);
    
    // Check if booking exists
    if (!$booking) {
        setFlashMessage('Booking not found.', 'danger');
        header('Location: ' . APP_URL . '/dashboard/bookings.php');
        exit;
    }
    
    // Check if user has permission to view this booking
    if (!hasPermission('manage_packages') && $booking['user_id'] != $user['id']) {
        setFlashMessage('You do not have permission to view this booking.', 'danger');
        header('Location: ' . APP_URL . '/dashboard/bookings.php');
        exit;
    }
    
    // Get package and booking services
    $package = $bookingController->getPackageDetails($booking['package_id']);
    $services = $bookingController->getBookingServices($bookingId);
    
    // Get guests and RSVP stats
    $guests = $bookingController->getBookingGuests($bookingId);
    $rsvpStats = $bookingController->getRsvpStats($bookingId);
    
    $pageTitle = 'Booking Details #' . $bookingId;
    $template = 'view';
} else {
    // List bookings
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Decide if we should show all bookings or only user's bookings
    if (hasPermission('manage_packages')) {
        // Admins and managers can see all bookings, with optional status filter
        if (!empty($statusFilter)) {
            $result = $bookingController->getBookingsByStatus($statusFilter, $page);
        } else {
            $result = $bookingController->getAllBookings($page);
        }
    } else {
        // Clients can only see their own bookings
        $result = $bookingController->getUserBookings($user['id'], $page);
    }
    
    $bookings = $result['bookings'];
    $pagination = $result['pagination'];
    $pageTitle = 'Bookings';
    $template = 'list';
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
        
        <?php if ($template === 'list' && hasRole(ROLE_CLIENT)): ?>
            <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?action=create" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Book New Event
            </a>
        <?php elseif ($template === 'view'): ?>
            <a href="<?php echo APP_URL; ?>/dashboard/bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Bookings
            </a>
        <?php endif; ?>
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
    
    <?php if ($template === 'list'): ?>
        <!-- Booking List View -->
        <?php if (hasPermission('manage_packages')): ?>
            <!-- Status filter for admins/managers -->
            <div class="mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-md-0">Filter by Status</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="btn-group w-100">
                                    <a href="<?php echo APP_URL; ?>/dashboard/bookings.php" class="btn <?php echo $statusFilter === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                    <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                                    <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?status=confirmed" class="btn <?php echo $statusFilter === 'confirmed' ? 'btn-primary' : 'btn-outline-primary'; ?>">Confirmed</a>
                                    <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?status=cancelled" class="btn <?php echo $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-outline-primary'; ?>">Cancelled</a>
                                    <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?status=completed" class="btn <?php echo $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline-primary'; ?>">Completed</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <?php if (hasPermission('manage_packages')): ?>
                        <?php if (!empty($statusFilter)): ?>
                            <?php echo getStatusName($statusFilter); ?> Bookings
                        <?php else: ?>
                            All Bookings
                        <?php endif; ?>
                    <?php else: ?>
                        Your Bookings
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($bookings)): ?>
                    <div class="p-4 text-center">
                        <p>No bookings found.</p>
                        <?php if (hasRole(ROLE_CLIENT)): ?>
                            <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?action=create" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>Book New Event
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <?php if (hasPermission('manage_packages')): ?>
                                        <th>Client</th>
                                    <?php endif; ?>
                                    <th>Package</th>
                                    <th>Event Date</th>
                                    <th>Location</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <?php if (hasPermission('manage_packages')): ?>
                                            <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                        <td><?php echo formatDate($booking['event_date']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($booking['event_location'], 0, 20) . (strlen($booking['event_location']) > 20 ? '...' : '')); ?></td>
                                        <td><?php echo formatPrice($booking['total_price'] - $booking['discount_applied']); ?></td>
                                        <td>
                                            <span class="booking-status-<?php echo $booking['id']; ?>">
                                                <?php echo getStatusBadge($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($pagination['total'] > 1): ?>
                <div class="card-footer">
                    <?php 
                    $pageUrl = '/dashboard/bookings.php';
                    if (!empty($statusFilter)) {
                        $pageUrl .= '?status=' . $statusFilter . '&page=';
                    } else {
                        $pageUrl .= '?page=';
                    }
                    echo getPagination($pagination['current'], $pagination['total'], $pageUrl); 
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php elseif ($template === 'view'): ?>
        <!-- Booking Detail View -->
        <div class="row">
            <!-- Booking Details -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Booking Information</h5>
                        <span class="booking-status-<?php echo $booking['id']; ?>">
                            <?php echo getStatusBadge($booking['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Event Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Date:</strong> <?php echo formatDate($booking['event_date']); ?></li>
                                    <li><strong>Location:</strong> <?php echo htmlspecialchars($booking['event_location']); ?></li>
                                    <li><strong>Status:</strong> <?php echo getStatusName($booking['status']); ?></li>
                                    <li><strong>Created:</strong> <?php echo formatDate($booking['created_at']); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Package</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> <?php echo htmlspecialchars($package['name']); ?></li>
                                    <li><strong>Base Price:</strong> <?php echo formatPrice($package['price']); ?></li>
                                    <li><strong>Discount:</strong> <span id="booking-discount"><?php echo formatPrice($booking['discount_applied']); ?></span></li>
                                    <li><strong>Total Price:</strong> <span id="booking-total-price"><?php echo formatPrice($booking['total_price'] - $booking['discount_applied']); ?></span></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Services Included -->
                        <h6 class="mb-3">Services Included</h6>
                        <?php if (empty($services)): ?>
                            <p class="text-muted">No additional services.</p>
                        <?php else: ?>
                            <div class="row mb-4">
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="service-item p-2 border rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($service['name']); ?></span>
                                                <span class="badge bg-primary"><?php echo formatPrice($service['price']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Update (Admin/Manager Only) -->
                        <?php if (hasPermission('manage_packages') && $booking['status'] !== STATUS_CANCELLED): ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">Update Booking Status</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                        
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-6">
                                                <select class="form-select booking-status-select" name="status" data-booking-id="<?php echo $booking['id']; ?>">
                                                    <option value="pending" <?php echo $booking['status'] === STATUS_PENDING ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $booking['status'] === STATUS_CONFIRMED ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="completed" <?php echo $booking['status'] === STATUS_COMPLETED ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Apply Discount (Admin/Manager Only) -->
                            <?php if (hasPermission('apply_discount')): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">Apply Discount</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="" id="discount-form">
                                            <input type="hidden" name="booking_id" id="booking-id" value="<?php echo $booking['id']; ?>">
                                            
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="discount-amount" name="discount_amount" step="0.01" min="0" max="<?php echo $booking['total_price']; ?>" placeholder="Amount" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" name="apply_discount" class="btn btn-success">Apply Discount</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Cancel Booking -->
                        <?php if ($booking['status'] === STATUS_PENDING || $booking['status'] === STATUS_CONFIRMED): ?>
                            <div class="mt-4">
                                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $booking['user_id']; ?>">
                                    
                                    <button type="submit" name="cancel_booking" class="btn btn-danger">
                                        <i class="fas fa-times-circle me-1"></i>Cancel Booking
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Guest List and RSVP Stats -->
            <div class="col-md-4 mb-4">
                <?php if (hasPermission('apply_discount') && $booking['status'] !== STATUS_CANCELLED): ?>
                <!-- Discount Management -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Apply Discount</h5>
                    </div>
                    <div class="card-body">
                        <form id="discount-form" class="mb-3">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <div class="mb-3">
                                <label for="discount_amount" class="form-label">Discount Amount (£)</label>
                                <input type="number" class="form-control" id="discount_amount" name="discount_amount" 
                                       min="0" max="<?php echo $booking['total_price']; ?>" step="0.01" 
                                       value="<?php echo $booking['discount_applied']; ?>" required>
                                <div class="form-text">Maximum discount: <?php echo formatPrice($booking['total_price']); ?></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Apply Discount</button>
                        </form>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Discounts can be applied to any booking that hasn't been cancelled.
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Guest List</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($guests)): ?>
                            <p class="text-muted">No guests added yet.</p>
                        <?php else: ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Guests: <?php echo $rsvpStats['total']; ?></span>
                                    <a href="<?php echo APP_URL; ?>/dashboard/guests.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-users me-1"></i>Manage Guests
                                    </a>
                                </div>
                                <div class="progress">
                                    <?php if ($rsvpStats['total'] > 0): ?>
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo ($rsvpStats['accepted'] / $rsvpStats['total']) * 100; ?>%" 
                                             aria-valuenow="<?php echo $rsvpStats['accepted']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $rsvpStats['total']; ?>">
                                            <?php echo $rsvpStats['accepted']; ?> Accepted
                                        </div>
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: <?php echo ($rsvpStats['declined'] / $rsvpStats['total']) * 100; ?>%" 
                                             aria-valuenow="<?php echo $rsvpStats['declined']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $rsvpStats['total']; ?>">
                                            <?php echo $rsvpStats['declined']; ?> Declined
                                        </div>
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo ($rsvpStats['pending'] / $rsvpStats['total']) * 100; ?>%" 
                                             aria-valuenow="<?php echo $rsvpStats['pending']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $rsvpStats['total']; ?>">
                                            <?php echo $rsvpStats['pending']; ?> Pending
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="list-group guest-list">
                                <?php foreach (array_slice($guests, 0, 5) as $guest): ?>
                                    <div class="guest-list-item p-2 ps-3 mb-2 <?php echo $guest['rsvp_status']; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($guest['name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php if (!empty($guest['email'])): ?>
                                                        <?php echo htmlspecialchars($guest['email']); ?>
                                                    <?php elseif (!empty($guest['phone'])): ?>
                                                        <?php echo htmlspecialchars($guest['phone']); ?>
                                                    <?php else: ?>
                                                        No contact info
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <span class="guest-status-<?php echo $guest['id']; ?>">
                                                <?php echo getStatusBadge($guest['rsvp_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($guests) > 5): ?>
                                    <div class="text-center mt-2">
                                        <a href="<?php echo APP_URL; ?>/dashboard/guests.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            View All <?php echo count($guests); ?> Guests
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] !== STATUS_CANCELLED): ?>
                            <div class="mt-3">
                                <a href="<?php echo APP_URL; ?>/dashboard/guests.php?booking_id=<?php echo $booking['id']; ?>&action=add" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-1"></i>Add Guests
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Client Details (Admin/Manager Only) -->
                <?php if (hasPermission('manage_packages')): ?>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Client Information</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><strong>Name:</strong> <?php echo htmlspecialchars($booking['client_name']); ?></li>
                                <li><strong>Email:</strong> <?php echo htmlspecialchars($booking['client_email']); ?></li>
                                <li><strong>Client ID:</strong> #<?php echo $booking['user_id']; ?></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php elseif ($template === 'create'): ?>
        <!-- Create Booking Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Book an Event</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- Step 1: Select Package -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">Step 1: Select Package</h5>
                        
                        <?php if ($packageId && $package): ?>
                            <!-- Show selected package -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Selected Package: <?php echo htmlspecialchars($package['name']); ?></h6>
                                        <p><?php echo htmlspecialchars($package['description']); ?></p>
                                        <div class="d-flex justify-content-between">
                                            <strong>Price: <?php echo formatPrice($package['price']); ?></strong>
                                            <a href="<?php echo APP_URL; ?>/dashboard/bookings.php?action=create" class="btn btn-sm btn-outline-secondary">
                                                Change Package
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="package_id" id="selected-package-id" value="<?php echo $package['id']; ?>">
                            <input type="hidden" name="total_price" value="<?php echo $package['price']; ?>">
                        <?php else: ?>
                            <!-- Show package selection -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Choose a Package</label>
                                    <div class="row row-cols-1 row-cols-md-2 g-4 mb-3">
                                        <?php foreach ($packages as $pkg): ?>
                                            <div class="col">
                                                <div class="card h-100 package-select-card border-hover" data-package-id="<?php echo $pkg['id']; ?>">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($pkg['name']); ?></h5>
                                                        <p class="card-text"><?php echo htmlspecialchars(substr($pkg['description'], 0, 100) . (strlen($pkg['description']) > 100 ? '...' : '')); ?></p>
                                                        <div class="text-primary fs-5 fw-bold"><?php echo formatPrice($pkg['price']); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="package_id" id="selected-package-id" value="">
                                </div>
                            </div>
                            
                            <!-- Or create custom package -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="custom-package" name="custom_package">
                                        <label class="form-check-label" for="custom-package">
                                            Create Custom Package (Select individual services)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="custom-package-services" class="d-none">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Select Services</label>
                                        <div class="row row-cols-1 row-cols-md-2 g-4 mb-3">
                                            <?php foreach ($services as $svc): ?>
                                                <div class="col">
                                                    <div class="card h-100">
                                                        <div class="card-body">
                                                            <div class="form-check">
                                                                <input class="form-check-input custom-service-checkbox" type="checkbox" name="services[]" 
                                                                       value="<?php echo $svc['id']; ?>" id="service_<?php echo $svc['id']; ?>" 
                                                                       data-service-price="<?php echo $svc['price']; ?>">
                                                                <label class="form-check-label w-100" for="service_<?php echo $svc['id']; ?>">
                                                                    <h6 class="card-title"><?php echo htmlspecialchars($svc['name']); ?></h6>
                                                                    <p class="card-text"><?php echo htmlspecialchars($svc['description']); ?></p>
                                                                    <div class="text-primary fs-5 fw-bold"><?php echo formatPrice($svc['price']); ?></div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="alert alert-info">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-0">Total Price:</h6>
                                                <div id="custom-package-total" class="fw-bold">$0.00</div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="total_price" id="custom-package-price" value="0">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Step 2: Event Details -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">Step 2: Event Details</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">Event Date</label>
                                <input type="date" class="form-control datepicker" id="event_date" name="event_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="event_location" class="form-label">Event Location</label>
                                <input type="text" class="form-control" id="event_location" name="event_location" 
                                       placeholder="Enter event address or venue name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/dashboard/bookings.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="create_booking" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i>Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle custom package toggle
                const customPackageCheckbox = document.getElementById('custom-package');
                const customPackageServices = document.getElementById('custom-package-services');
                const selectedPackageId = document.getElementById('selected-package-id');
                
                if (customPackageCheckbox) {
                    customPackageCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            customPackageServices.classList.remove('d-none');
                            selectedPackageId.value = '';
                            
                            // Remove selection from package cards
                            document.querySelectorAll('.package-select-card').forEach(function(card) {
                                card.classList.remove('border-primary');
                            });
                        } else {
                            customPackageServices.classList.add('d-none');
                        }
                    });
                }
                
                // Handle package card selection
                document.querySelectorAll('.package-select-card').forEach(function(card) {
                    card.addEventListener('click', function() {
                        const packageId = this.getAttribute('data-package-id');
                        selectedPackageId.value = packageId;
                        
                        // Remove selection from all cards
                        document.querySelectorAll('.package-select-card').forEach(function(c) {
                            c.classList.remove('border-primary');
                        });
                        
                        // Add selection to clicked card
                        this.classList.add('border-primary');
                        
                        // Uncheck custom package
                        if (customPackageCheckbox) {
                            customPackageCheckbox.checked = false;
                            customPackageServices.classList.add('d-none');
                        }
                    });
                });
            });
        </script>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>

<?php if ($template === 'view'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Discount form handling
        const discountForm = document.getElementById('discount-form');
        if (discountForm) {
            discountForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const bookingId = discountForm.querySelector('input[name="booking_id"]').value;
                const discountAmount = parseFloat(discountForm.querySelector('input[name="discount_amount"]').value);
                
                // Validate discount amount
                if (isNaN(discountAmount) || discountAmount < 0) {
                    alert('Please enter a valid discount amount.');
                    return;
                }
                
                // Submit discount via AJAX
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=apply_discount&booking_id=${bookingId}&discount_amount=${discountAmount}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update discount and total price display
                        document.getElementById('booking-discount').textContent = '£' + data.discount.toFixed(2);
                        document.getElementById('booking-total-price').textContent = '£' + data.new_total.toFixed(2);
                        
                        // Show success message
                        alert('Discount applied successfully!');
                    } else {
                        alert(data.message || 'Failed to apply discount. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        }
    });
</script>
<?php endif; ?>
