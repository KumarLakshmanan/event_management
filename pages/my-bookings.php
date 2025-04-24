<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get services for lookup
$services = $db->query("SELECT * FROM services");
$bookings = $db->query("SELECT * FROM bookings");
$users = $db->query("SELECT * FROM users");
$guests = $db->query("SELECT * FROM guests");
$packages = $db->query("SELECT * FROM packages");

// Filter bookings for current user
$userBookings = array_filter($bookings, function ($booking) {
    if ($_SESSION['user_role'] === 'client') {
        return $booking['user_id'] == $_SESSION['user_id'];
    } else {
        return true; // Admin/Manager can see all bookings
    }
});

// Create lookup array for packages
$packageNames = [];
foreach ($packages as $package) {
    $packageNames[$package['id']] = $package;
}

// Get booking details if specified
$viewBooking = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    foreach ($userBookings as $booking) {
        if ($booking['id'] == $_GET['view']) {
            $viewBooking = $booking;
            break;
        }
    }
}

// Get booking guests if viewing a booking
$bookingGuests = [];
if ($viewBooking) {
    $bookingGuests = array_filter($guests, function ($guest) use ($viewBooking) {
        return $guest['booking_id'] == $viewBooking['id'];
    });
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Bookings</h1>
    <?php if ($_SESSION['user_role'] === 'client'): ?>
        <a href="packages.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Book New Event
        </a>
    <?php endif; ?>
</div>

<?php if ($viewBooking): ?>
    <!-- Booking Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Booking Details</h6>
            <?php if ($_SESSION['user_role'] === 'client'): ?>
                <a href="my-bookings.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to All Bookings
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Event Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>Booking ID:</th>
                            <td>#<?php echo $viewBooking['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Package:</th>
                            <td><?php echo $packageNames[$viewBooking['package_id']]['name'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <th>Event Date:</th>
                            <td><?php echo $viewBooking['event_date']; ?></td>
                        </tr>
                        <tr>
                            <th>Event Place:</th>
                            <td><?php echo $viewBooking['event_place']; ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($viewBooking['status'] === 'confirmed'): ?>
                                    <span class="badge badge-success">Confirmed</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>
                                <?php
                                if ($viewBooking['discount']) {
                                    echo '$' . number_format($viewBooking['discount'], 2);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Booking Date:</th>
                            <td><?php echo $viewBooking['created_at']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Package Details</h5>
                    <?php if (isset($packageNames[$viewBooking['package_id']])):
                        $package = $packageNames[$viewBooking['package_id']];
                    ?>
                        <table class="table table-borderless">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo $package['name']; ?></td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td>£<?php echo number_format($package['price'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Final Price:</th>
                                <td>
                                    <?php
                                    $finalPrice = $package['price'] - ($viewBooking['discount'] ?? 0);
                                    echo '$' . number_format($finalPrice, 2);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td><?php echo $package['description']; ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">Package details not available.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Guest Management Section -->
            <?php if ($viewBooking['status'] === 'confirmed'): ?>
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Guest Management</h5>
                        <?php if ($_SESSION['user_role'] === 'client'): ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addGuestModal">
                                <i class="fas fa-user-plus"></i> Add Guest
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>RSVP Status</th>
                                    <?php if ($_SESSION['user_role'] === 'client'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bookingGuests)): ?>
                                    <?php foreach ($bookingGuests as $guest): ?>
                                        <tr>
                                            <td><?php echo $guest['id']; ?></td>
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
                                            <?php if ($_SESSION['user_role'] === 'client'): ?>
                                                <td>
                                                    <a href="../handlers/guests.php?action=delete&id=<?php echo $guest['id']; ?>&booking_id=<?php echo $viewBooking['id']; ?>" class="btn btn-sm btn-danger btn-delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-primary send-invite"
                                                        data-id="<?php echo $guest['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($guest['name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($guest['email']); ?>">
                                                        <i class="fas fa-envelope"></i> Send Invite
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No guests added yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
                    <form id="guestForm" class="api-form" action="../handlers/guests.php" method="POST" data-redirect="my-bookings.php?view=<?php echo $viewBooking['id']; ?>">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="booking_id" value="<?php echo $viewBooking['id']; ?>">

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
                        booking_id: <?php echo $viewBooking['id']; ?>
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

<?php else: ?>
    <!-- Bookings List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">My Bookings</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package</th>
                            <th>Event Date</th>
                            <th>Event Place</th>
                            <th>Status</th>
                            <th>Discount</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userBookings)): ?>
                            <?php foreach ($userBookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo $packageNames[$booking['package_id']]['name'] ?? 'Unknown'; ?></td>
                                    <td><?php echo $booking['event_date']; ?></td>
                                    <td><?php echo $booking['event_place']; ?></td>
                                    <td>
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <span class="badge badge-success">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($booking['discount']) {
                                            echo '$' . number_format($booking['discount'], 2);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $booking['created_at']; ?></td>
                                    <td>
                                        <a href="?view=<?php echo $booking['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No bookings found. <a href="packages.php">Book an event now</a>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>