<?php
/**
 * Bookings Management
 * 
 * Users can view, create, edit, and delete bookings
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get database connection
$db = getDBConnection();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'create' || $action === 'edit') {
        // Get form data
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $bundleId = (int)$_POST['bundle_id'];
        $eventPlace = trim($_POST['event_place']);
        $eventDate = trim($_POST['event_date']);
        $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $_SESSION['user_id'];
        $status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
        
        // Validate form data
        $errors = [];
        
        if (empty($bundleId)) {
            $errors[] = 'Package is required';
        }
        
        if (empty($eventPlace)) {
            $errors[] = 'Event place is required';
        }
        
        if (empty($eventDate)) {
            $errors[] = 'Event date is required';
        }
        
        // Check if user can give discount
        $currentUser = getCurrentUser();
        if ($discount > 0 && !$currentUser['can_give_discount'] && !hasRole('administrator')) {
            $errors[] = 'You do not have permission to give discount';
        }
        
        // If there are no errors, create or update booking
        if (empty($errors)) {
            if ($action === 'create') {
                // Create booking
                $stmt = $db->prepare("INSERT INTO reservations (user_id, bundle_id, event_place, event_date, discount, status) 
                                    VALUES (:user_id, :bundle_id, :event_place, :event_date, :discount, :status)");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':bundle_id', $bundleId);
                $stmt->bindParam(':event_place', $eventPlace);
                $stmt->bindParam(':event_date', $eventDate);
                $stmt->bindParam(':discount', $discount);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $bookingId = $db->lastInsertId();
                    setAlert('success', 'Booking created successfully');
                    
                    // Add notification for new booking
                    $currentUser = getCurrentUser();
                    $message = 'New booking created by ' . $currentUser['name'];
                    addNotification('new_booking', $message, $bookingId);
                    
                    header('Location: bookings.php');
                    exit;
                } else {
                    setAlert('danger', 'Failed to create booking');
                }
            } else {
                // Get current booking status
                $stmt = $db->prepare("SELECT status FROM reservations WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $oldStatus = $stmt->fetchColumn();
                
                // Update booking
                $confirmedBy = null;
                if ($status === 'confirmed' && $oldStatus !== 'confirmed') {
                    $confirmedBy = $_SESSION['user_id'];
                    
                    // Add notification for booking confirmation
                    $currentUser = getCurrentUser();
                    $message = 'Booking #' . $id . ' confirmed by ' . $currentUser['name'];
                    addNotification('booking_confirmed', $message, $id);
                } elseif ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                    // Add notification for booking cancellation
                    $currentUser = getCurrentUser();
                    $message = 'Booking #' . $id . ' cancelled by ' . $currentUser['name'];
                    addNotification('booking_cancelled', $message, $id);
                } elseif ($status === 'completed' && $oldStatus !== 'completed') {
                    // Add notification for booking completion
                    $currentUser = getCurrentUser();
                    $message = 'Booking #' . $id . ' marked as completed by ' . $currentUser['name'];
                    addNotification('booking_completed', $message, $id);
                }
                
                $stmt = $db->prepare("UPDATE reservations SET bundle_id = :bundle_id, event_place = :event_place, 
                                    event_date = :event_date, discount = :discount, status = :status, 
                                    confirmed_by = :confirmed_by
                                    WHERE id = :id");
                $stmt->bindParam(':bundle_id', $bundleId);
                $stmt->bindParam(':event_place', $eventPlace);
                $stmt->bindParam(':event_date', $eventDate);
                $stmt->bindParam(':discount', $discount);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':confirmed_by', $confirmedBy);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    setAlert('success', 'Booking updated successfully');
                    header('Location: bookings.php');
                    exit;
                } else {
                    setAlert('danger', 'Failed to update booking');
                }
            }
        } else {
            setAlert('danger', implode('<br>', $errors));
        }
    } elseif ($action === 'delete') {
        // Delete booking
        $id = (int)$_POST['id'];
        
        // Only allow admins, managers, or the booking owner to delete bookings
        $stmt = $db->prepare("SELECT user_id FROM reservations WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $userId = $stmt->fetchColumn();
        
        if (hasRole('administrator') || hasRole('manager') || $userId === $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                setAlert('success', 'Booking deleted successfully');
            } else {
                setAlert('danger', 'Failed to delete booking');
            }
        } else {
            setAlert('danger', 'You do not have permission to delete this booking');
        }
        
        header('Location: bookings.php');
        exit;
    }
}

// Get action and ID from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables for form
$booking = [
    'id' => 0,
    'user_id' => $_SESSION['user_id'],
    'bundle_id' => 0,
    'event_place' => '',
    'event_date' => date('Y-m-d\TH:i'),
    'discount' => 0,
    'status' => 'pending',
    'confirmed_by' => null,
    'created_at' => date('Y-m-d H:i:s')
];

// If editing, get booking data
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM reservations WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $fetchedBooking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fetchedBooking) {
        // Only allow admins, managers, or the booking owner to edit bookings
        if (hasRole('administrator') || hasRole('manager') || $fetchedBooking['user_id'] === $_SESSION['user_id']) {
            $booking = $fetchedBooking;
            // Format event date for datetime-local input
            $booking['event_date'] = date('Y-m-d\TH:i', strtotime($booking['event_date']));
        } else {
            setAlert('danger', 'You do not have permission to edit this booking');
            header('Location: bookings.php');
            exit;
        }
    } else {
        setAlert('danger', 'Booking not found');
        header('Location: bookings.php');
        exit;
    }
}

// Get all bookings for list view
$bookings = [];
if ($action === '' || $action === 'list') {
    // Different query based on user role
    if (hasRole('administrator') || hasRole('manager')) {
        // Admins and managers can see all bookings
        $stmt = $db->query("SELECT r.*, m.name as user_name, b.name as bundle_name, 
                          (SELECT name FROM members WHERE id = r.confirmed_by) as confirmed_by_name
                          FROM reservations r
                          JOIN members m ON r.user_id = m.id
                          JOIN bundles b ON r.bundle_id = b.id
                          ORDER BY r.created_at DESC");
    } else {
        // Clients can only see their own bookings
        $userId = $_SESSION['user_id'];
        $stmt = $db->prepare("SELECT r.*, m.name as user_name, b.name as bundle_name, 
                            (SELECT name FROM members WHERE id = r.confirmed_by) as confirmed_by_name
                            FROM reservations r
                            JOIN members m ON r.user_id = m.id
                            JOIN bundles b ON r.bundle_id = b.id
                            WHERE r.user_id = :user_id
                            ORDER BY r.created_at DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all packages for dropdown
$packages = [];
$stmt = $db->query("SELECT id, name FROM bundles ORDER BY name");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all clients for dropdown (admin/manager only)
$clients = [];
if (hasRole('administrator') || hasRole('manager')) {
    $stmt = $db->query("SELECT id, name, email FROM members WHERE role = 'client' ORDER BY name");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Set page title based on action
$pageTitle = 'Booking Management';
if ($action === 'create') {
    $pageTitle = 'Create Booking';
} elseif ($action === 'edit') {
    $pageTitle = 'Edit Booking';
}

// Include header
require_once '../templates/header.php';
?>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
            <?php if ($action === '' || $action === 'list'): ?>
                <a href="bookings.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Booking
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <!-- Create/Edit Form -->
            <div class="col-12">
                <form method="post" action="bookings.php?action=<?php echo $action; ?>" class="row g-3">
                    <?php if ($id > 0): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <?php if (hasRole('administrator') || hasRole('manager')): ?>
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Client</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>" <?php echo $booking['user_id'] == $client['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($client['name'] . ' (' . $client['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <label for="bundle_id" class="form-label">Package</label>
                        <select class="form-select" id="bundle_id" name="bundle_id" required>
                            <option value="">Select Package</option>
                            <?php foreach ($packages as $package): ?>
                                <option value="<?php echo $package['id']; ?>" <?php echo $booking['bundle_id'] == $package['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($package['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="event_place" class="form-label">Event Place</label>
                        <input type="text" class="form-control" id="event_place" name="event_place" value="<?php echo htmlspecialchars($booking['event_place']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="event_date" class="form-label">Event Date & Time</label>
                        <input type="datetime-local" class="form-control" id="event_date" name="event_date" value="<?php echo $booking['event_date']; ?>" required>
                    </div>
                    
                    <?php if (hasRole('administrator') || (hasRole('manager') && getCurrentUser()['can_give_discount'])): ?>
                        <div class="col-md-6">
                            <label for="discount" class="form-label">Discount (£)</label>
                            <input type="number" class="form-control" id="discount" name="discount" value="<?php echo $booking['discount']; ?>" step="0.01" min="0">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('administrator') || hasRole('manager')): ?>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="bookings.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Booking List -->
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <?php if (hasRole('administrator') || hasRole('manager')): ?>
                                    <th>Client</th>
                                <?php endif; ?>
                                <th>Package</th>
                                <th>Event Place</th>
                                <th>Event Date</th>
                                <th>Status</th>
                                <th>Discount</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="<?php echo hasRole('administrator') || hasRole('manager') ? 9 : 8; ?>" class="text-center">No bookings found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <?php if (hasRole('administrator') || hasRole('manager')): ?>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($booking['bundle_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['event_place']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($booking['event_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['status'] === 'confirmed' ? 'success' : 
                                                    ($booking['status'] === 'pending' ? 'warning' : 
                                                    ($booking['status'] === 'completed' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency($booking['discount']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($booking['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="bookings.php?action=edit&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="guests.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                
                                                <!-- Delete Button -->
                                                <?php if (hasRole('administrator') || hasRole('manager') || $booking['user_id'] === $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $booking['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Delete Modal -->
                                            <?php if (hasRole('administrator') || hasRole('manager') || $booking['user_id'] === $_SESSION['user_id']): ?>
                                                <div class="modal fade" id="deleteModal<?php echo $booking['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $booking['id']; ?>">Delete Booking</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete booking #<?php echo $booking['id']; ?>?</p>
                                                                <p>This action cannot be undone and will remove all guests associated with this booking.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post" action="bookings.php?action=delete">
                                                                    <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>