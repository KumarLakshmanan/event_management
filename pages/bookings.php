<?php
require_once '../includes/header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'client') {
    header("Location: dashboard.php");
    exit;
}


$db = Database::getInstance();

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

$packageNames = [];
foreach ($packages as $package) {
    $packageNames[$package['id']] = $package['name'];
}

// Get single booking for confirmation if specified
$confirmBooking = null;
if (isset($_GET['confirm']) && is_numeric($_GET['confirm'])) {
    foreach ($bookings as $booking) {
        if ($booking['id'] == $_GET['confirm']) {
            $confirmBooking = $booking;
            break;
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Booking Management</h1>
</div>

<!-- Bookings Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Bookings</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Event Place</th>
                        <th>Status</th>
                        <th>Confirmed By</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo $userNames[$booking['user_id']] ?? 'Unknown'; ?></td>
                        <td><?php echo $packageNames[$booking['package_id']] ?? 'Unknown'; ?></td>
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
                            if ($booking['confirmed_by']) {
                                echo $userNames[$booking['confirmed_by']] ?? 'Unknown';
                            } else {
                                echo '-';
                            }
                            ?>
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
                        <td>
                            <?php if ($booking['status'] === 'pending'): ?>
                            <a href="?confirm=<?php echo $booking['id']; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Confirm
                            </a>
                            <?php endif; ?>
                            
                            <a href="my-bookings.php?view=<?php echo $booking['id']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No bookings found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<?php if ($confirmBooking): ?>
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="confirmForm" class="api-form" action="../handlers/bookings.php" method="POST" data-redirect="bookings.php">
                    <input type="hidden" name="action" value="confirm">
                    <input type="hidden" name="id" value="<?php echo $confirmBooking['id']; ?>">
                    
                    <div class="alert alert-info">
                        <p><strong>Client:</strong> <?php echo $userNames[$confirmBooking['user_id']] ?? 'Unknown'; ?></p>
                        <p><strong>Package:</strong> <?php echo $packageNames[$confirmBooking['package_id']] ?? 'Unknown'; ?></p>
                        <p><strong>Event Date:</strong> <?php echo $confirmBooking['event_date']; ?></p>
                        <p><strong>Event Place:</strong> <?php echo $confirmBooking['event_place']; ?></p>
                    </div>
                    
                    <?php if (canGiveDiscount()): ?>
                    <div class="form-group">
                        <label for="discount">Apply Discount (Optional)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">£</span>
                            </div>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0">
                        </div>
                        <small class="form-text text-muted">Leave empty for no discount.</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#confirmModal').modal('show');
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
