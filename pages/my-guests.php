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


// Filter guests for a specific booking if requested

$filteredGuests = $guests;

?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        My Guests
    </h1>
</div>

<!-- Guests Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            All My Guests
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($filteredGuests)): ?>
            <div class="alert alert-info">
                <p>You haven't added any guests for this booking yet.</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addGuestModal">
                    <i class="fas fa-plus"></i> Add First Guest
                </button>
            </div>
        <?php elseif (empty($filteredGuests)): ?>
            <div class="alert alert-info">
                <p>You don't have any guests across all your bookings.</p>
                <p>Select a booking to add guests or create a new booking first.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Booking</th>
                            <th>Event Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>RSVP Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredGuests as $guest):
                        ?>
                            <tr>
                                <td><?php echo $guest['id']; ?></td>
                                <td>
                                    <a href="my-bookings.php?view=<?php echo $guest['booking_id']; ?>">
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
                                    <a href="my-bookings.php?view=<?php echo $guest['booking_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-users"></i> Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>