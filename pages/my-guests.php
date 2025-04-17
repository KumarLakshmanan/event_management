<?php
require_once '../includes/header.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: dashboard.php");
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Get user bookings and guests data
if (USE_DATABASE) {
    $db = Database::getInstance();
    
    // Get user's bookings
    $bookings = $db->query(
        "SELECT * FROM bookings WHERE user_id = ? ORDER BY event_date DESC", 
        [$userId]
    );
    
    // Get booking IDs
    $bookingIds = array_column($bookings, 'id');
    
    if (!empty($bookingIds)) {
        // Convert array to string for SQL IN clause
        $bookingIdsStr = implode(',', $bookingIds);
        
        // Get all guests for these bookings
        $guests = $db->query(
            "SELECT * FROM guests WHERE booking_id IN ($bookingIdsStr) ORDER BY name"
        );
    } else {
        $guests = [];
    }
} else {
    // Fallback to mock data
    $allBookings = getMockData('bookings.json');
    $allGuests = getMockData('guests.json');
    
    // Filter bookings for current user
    $bookings = array_filter($allBookings, function($booking) use ($userId) {
        return $booking['user_id'] == $userId;
    });
    
    // Get booking IDs
    $bookingIds = array_column($bookings, 'id');
    
    // Filter guests for user's bookings
    $guests = array_filter($allGuests, function($guest) use ($bookingIds) {
        return in_array($guest['booking_id'], $bookingIds);
    });
}

// Filter guests for a specific booking if requested
$bookingId = isset($_GET['booking']) ? intval($_GET['booking']) : null;
$currentBooking = null;

if ($bookingId) {
    // Verify booking belongs to user
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            $currentBooking = $booking;
            break;
        }
    }
    
    if (!$currentBooking) {
        // Booking not found or doesn't belong to user
        header("Location: my-guests.php");
        exit;
    }
    
    // Filter guests for this booking
    $filteredGuests = array_filter($guests, function($guest) use ($bookingId) {
        return $guest['booking_id'] == $bookingId;
    });
} else {
    $filteredGuests = $guests;
}

// Get guest for edit if specified
$editGuest = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    foreach ($guests as $guest) {
        if ($guest['id'] == $_GET['edit']) {
            $editGuest = $guest;
            break;
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <?php if ($currentBooking): ?>
            Guest Management for Booking #<?php echo $bookingId; ?>
        <?php else: ?>
            My Guests
        <?php endif; ?>
    </h1>
    
    <?php if ($currentBooking): ?>
    <div>
        <a href="my-guests.php" class="btn btn-sm btn-secondary shadow-sm mr-2">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to All Guests
        </a>
        <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addGuestModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Guest
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (!$currentBooking): ?>
<!-- Bookings Selection Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Select a Booking to Manage Guests</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($bookings)): ?>
        <div class="row">
            <?php foreach ($bookings as $booking): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 booking-card">
                    <div class="card-body">
                        <h5 class="card-title">Event on <?php echo date('M d, Y', strtotime($booking['event_date'])); ?></h5>
                        <p class="card-text">
                            <strong>Location:</strong> <?php echo $booking['event_place']; ?><br>
                            <strong>Status:</strong> 
                            <?php if ($booking['status'] === 'confirmed'): ?>
                                <span class="badge badge-success">Confirmed</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="my-guests.php?booking=<?php echo $booking['id']; ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-users"></i> Manage Guests
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <p>You don't have any bookings yet. You need to book a package before you can manage guests.</p>
            <a href="packages.php" class="btn btn-primary">Browse Packages</a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($currentBooking): ?>
<!-- Booking Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Booking Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Event Date:</strong> <?php echo date('F d, Y', strtotime($currentBooking['event_date'])); ?></p>
                <p><strong>Event Place:</strong> <?php echo $currentBooking['event_place']; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> 
                    <?php if ($currentBooking['status'] === 'confirmed'): ?>
                        <span class="badge badge-success">Confirmed</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Pending</span>
                    <?php endif; ?>
                </p>
                <p><strong>Created At:</strong> <?php echo date('F d, Y', strtotime($currentBooking['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Guests Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php echo $currentBooking ? 'Guests List' : 'All My Guests'; ?>
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($filteredGuests) && $currentBooking): ?>
        <div class="alert alert-info">
            <p>You haven't added any guests for this booking yet.</p>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addGuestModal">
                <i class="fas fa-plus"></i> Add First Guest
            </button>
        </div>
        <?php elseif (empty($filteredGuests) && !$currentBooking): ?>
        <div class="alert alert-info">
            <p>You don't have any guests across all your bookings.</p>
            <p>Select a booking to add guests or create a new booking first.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <?php if (!$currentBooking): ?>
                        <th>Booking</th>
                        <th>Event Date</th>
                        <?php endif; ?>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>RSVP Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredGuests as $guest): 
                        // Get booking info if not filtering by booking
                        if (!$currentBooking) {
                            $guestBooking = null;
                            foreach ($bookings as $booking) {
                                if ($booking['id'] == $guest['booking_id']) {
                                    $guestBooking = $booking;
                                    break;
                                }
                            }
                        }
                    ?>
                    <tr>
                        <?php if (!$currentBooking): ?>
                        <td>
                            <a href="my-guests.php?booking=<?php echo $guest['booking_id']; ?>">
                                Booking #<?php echo $guest['booking_id']; ?>
                            </a>
                        </td>
                        <td>
                            <?php 
                            if (isset($guestBooking)) {
                                echo date('M d, Y', strtotime($guestBooking['event_date']));
                            } else {
                                echo 'Unknown';
                            }
                            ?>
                        </td>
                        <?php endif; ?>
                        
                        <td><?php echo $guest['name']; ?></td>
                        <td><?php echo $guest['email']; ?></td>
                        <td><?php echo $guest['phone']; ?></td>
                        <td>
                            <?php if ($guest['rsvp_status'] === 'yes'): ?>
                                <span class="badge badge-success">Attending</span>
                            <?php elseif ($guest['rsvp_status'] === 'no'): ?>
                                <span class="badge badge-danger">Not Attending</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($currentBooking): ?>
                            <a href="?booking=<?php echo $bookingId; ?>&edit=<?php echo $guest['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../handlers/guests.php?action=delete&id=<?php echo $guest['id']; ?>&booking_id=<?php echo $bookingId; ?>&client=1" class="btn btn-danger btn-sm btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <button class="btn btn-primary btn-sm send-invite" 
                                    data-id="<?php echo $guest['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($guest['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($guest['email']); ?>">
                                <i class="fas fa-envelope"></i>
                            </button>
                            <?php else: ?>
                            <a href="my-guests.php?booking=<?php echo $guest['booking_id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-users"></i> Manage
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($currentBooking): ?>
<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1" role="dialog" aria-labelledby="addGuestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGuestModalLabel">Add Guest</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="guestForm" class="api-form" action="../handlers/guests.php" method="POST" data-redirect="my-guests.php?booking=<?php echo $bookingId; ?>">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    <input type="hidden" name="client" value="1">
                    
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div id="nameFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                        <div id="phoneFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Guest</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Guest Modal -->
<?php if ($editGuest): ?>
<div class="modal fade" id="editGuestModal" tabindex="-1" role="dialog" aria-labelledby="editGuestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGuestModalLabel">Edit Guest</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editGuestForm" class="api-form" action="../handlers/guests.php" method="POST" data-redirect="my-guests.php?booking=<?php echo $bookingId; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $editGuest['id']; ?>">
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    <input type="hidden" name="client" value="1">
                    
                    <div class="form-group">
                        <label for="edit_name">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo $editGuest['name']; ?>" required>
                        <div id="edit_nameFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo $editGuest['email']; ?>" required>
                        <div id="edit_emailFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" value="<?php echo $editGuest['phone']; ?>">
                        <div id="edit_phoneFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_rsvp_status">RSVP Status</label>
                        <select class="form-control" id="edit_rsvp_status" name="rsvp_status">
                            <option value="pending" <?php echo $editGuest['rsvp_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="yes" <?php echo $editGuest['rsvp_status'] === 'yes' ? 'selected' : ''; ?>>Attending</option>
                            <option value="no" <?php echo $editGuest['rsvp_status'] === 'no' ? 'selected' : ''; ?>>Not Attending</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Guest</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editGuestModal').modal('show');
});
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Handle send invite button
    $('.send-invite').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');
        
        // Show loading spinner
        showSpinner();
        
        // Simulate sending (for demo)
        setTimeout(function() {
            hideSpinner();
            showAlert('Invitation sent to ' + name + ' (' + email + ')', 'success');
        }, 1000);
        
        // In a real application, we would send AJAX request here
        /*
        $.ajax({
            url: '../handlers/guests.php',
            type: 'POST',
            data: {
                action: 'send_invite',
                id: id,
                booking_id: <?php echo $bookingId; ?>
            },
            success: function(response) {
                hideSpinner();
                showAlert('Invitation sent to ' + name + ' (' + email + ')', 'success');
            },
            error: function(xhr, status, error) {
                hideSpinner();
                showAlert('Error sending invitation. Please try again.', 'danger');
                console.error('Error:', error);
            }
        });
        */
    });
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>