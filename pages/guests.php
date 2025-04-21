<?php
require_once '../includes/header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'client') {
    header("Location: dashboard.php");
    exit;
}

// Get services for lookup
$services = $db->query("SELECT * FROM services");
$bookings = $db->query("SELECT * FROM bookings");
$users = $db->query("SELECT * FROM users");
$guests = $db->query("SELECT * FROM guests");
$packages = $db->query("SELECT * FROM packages");
// Create lookup arrays
$userNames = [];
foreach ($users as $user) {
    $userNames[$user['id']] = $user['name'];
}

// Filter guests for a specific booking if requested
$bookingId = isset($_GET['booking']) ? intval($_GET['booking']) : null;
$currentBooking = null;

if ($bookingId) {
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            $currentBooking = $booking;
            break;
        }
    }
    
    // Filter guests for this booking
    $filteredGuests = array_filter($guests, function($guest) use ($bookingId) {
        return $guest['booking_id'] == $bookingId;
    });
} else {
    $filteredGuests = $guests;
}

?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <?php if ($currentBooking): ?>
            Guest Management for Booking #<?php echo $bookingId; ?>
        <?php else: ?>
            All Guests
        <?php endif; ?>
    </h1>
    
    <?php if ($currentBooking): ?>
    <div>
        <a href="bookings.php" class="btn btn-sm btn-secondary shadow-sm mr-2">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Bookings
        </a>
        <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addGuestModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Guest
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if ($currentBooking): ?>
<!-- Booking Info Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Booking Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Client:</strong> <?php echo $userNames[$currentBooking['user_id']] ?? 'Unknown'; ?></p>
                <p><strong>Event Date:</strong> <?php echo $currentBooking['event_date']; ?></p>
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
                <p><strong>Confirmed By:</strong> 
                    <?php 
                    if ($currentBooking['confirmed_by']) {
                        echo $userNames[$currentBooking['confirmed_by']] ?? 'Unknown';
                    } else {
                        echo 'Not confirmed yet';
                    }
                    ?>
                </p>
                <p><strong>Created At:</strong> <?php echo $currentBooking['created_at']; ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Guests Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php echo $currentBooking ? 'Guests List' : 'All Guests'; ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if (!$currentBooking): ?>
                        <th>Booking ID</th>
                        <th>Client</th>
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
                    <?php if (!empty($filteredGuests)): ?>
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
                            <td><?php echo $guest['id']; ?></td>
                            
                            <?php if (!$currentBooking): ?>
                            <td><?php echo $guest['booking_id']; ?></td>
                            <td>
                                <?php 
                                if (isset($guestBooking)) {
                                    echo $userNames[$guestBooking['user_id']] ?? 'Unknown';
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (isset($guestBooking)) {
                                    echo $guestBooking['event_date'];
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
                                <a href="../handlers/guests.php?action=delete&id=<?php echo $guest['id']; ?>&booking_id=<?php echo $bookingId; ?>" class="btn btn-danger btn-sm btn-delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <button class="btn btn-primary btn-sm send-invite" 
                                        data-id="<?php echo $guest['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($guest['name']); ?>"
                                        data-email="<?php echo htmlspecialchars($guest['email']); ?>">
                                    <i class="fas fa-envelope"></i>
                                </button>
                                <?php else: ?>
                                <a href="guests.php?booking=<?php echo $guest['booking_id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Booking
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $currentBooking ? '6' : '9'; ?>" class="text-center">No guests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                <form id="guestForm" class="api-form" action="../handlers/guests.php" method="POST" data-redirect="guests.php?booking=<?php echo $bookingId; ?>">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    
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


<script>
$(document).ready(function() {
    // Handle send invite button
    $('.send-invite').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');
        
        // Show loading spinner
        showSpinner();
        
        // Send AJAX request
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
    });
    
    // Function to show spinner
    function showSpinner() {
        $('body').append('<div class="spinner-overlay"><div class="spinner-border text-light" role="status"><span class="sr-only">Loading...</span></div></div>');
    }
    
    // Function to hide spinner
    function hideSpinner() {
        $('.spinner-overlay').remove();
    }
    
    // Function to show alert
    function showAlert(message, type) {
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
