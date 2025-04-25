<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Guest.php';
require_once __DIR__ . '/models/Booking.php';
require_once __DIR__ . '/controllers/NotificationController.php';

// Initialize Guest model
$guestModel = new Guest();
$bookingModel = new Booking();
$notificationController = new NotificationController();

// Set default values
$token = isset($_GET['token']) ? $_GET['token'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success = false;
$message = '';
$guestName = '';
$eventDate = '';
$eventLocation = '';

// Check if token and action are provided
if (!empty($token) && in_array($action, ['accept', 'decline'])) {
    // Find the guest by token
    $guest = $guestModel->getByToken($token);
    
    if ($guest) {
        $guestName = $guest['name'];
        
        // Get booking details
        $booking = $bookingModel->getById($guest['booking_id']);
        if ($booking) {
            $eventDate = formatDate($booking['event_date']);
            $eventLocation = $booking['event_location'];
            
            // Update guest RSVP status
            $status = $action === 'accept' ? RSVP_ACCEPTED : RSVP_DECLINED;
            $result = $guestModel->updateRsvpStatus($guest['id'], $status);
            
            if ($result) {
                $success = true;
                $message = $action === 'accept' 
                    ? 'Thank you for confirming your attendance!' 
                    : 'Thank you for letting us know you cannot attend.';
                
                // Create notification for booking owner
                $notificationMessage = $guest['name'] . ' has ' . ($action === 'accept' ? 'accepted' : 'declined') . ' the invitation.';
                $notificationController->create([
                    'user_id' => $booking['user_id'],
                    'type' => 'rsvp',
                    'message' => $notificationMessage,
                    'related_id' => $booking['id']
                ]);
            } else {
                $message = 'There was a problem updating your RSVP status. Please try again.';
            }
        } else {
            $message = 'Event details not found.';
        }
    } else {
        $message = 'Invalid invitation link. Please check your email and try again.';
    }
} else {
    $message = 'Invalid invitation link. Please check your email and try again.';
}

// Page title
$title = 'Event RSVP';
$showSidebar = false;

// Include header
include_once __DIR__ . '/templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center bg-<?php echo $success ? 'success' : 'danger'; ?> text-white">
                    <h4 class="mb-0"><?php echo $success ? 'RSVP Confirmation' : 'RSVP Error'; ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-<?php echo $action === 'accept' ? 'check-circle' : 'times-circle'; ?> fa-5x text-<?php echo $action === 'accept' ? 'success' : 'danger'; ?>"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($message); ?></h5>
                        </div>
                        
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-md-end fw-bold">Guest Name:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($guestName); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 text-md-end fw-bold">Event Date:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($eventDate); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 text-md-end fw-bold">Event Location:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($eventLocation); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4 text-md-end fw-bold">RSVP Status:</div>
                                    <div class="col-md-8">
                                        <span class="badge bg-<?php echo $action === 'accept' ? 'success' : 'danger'; ?>">
                                            <?php echo $action === 'accept' ? 'Attending' : 'Not Attending'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($action === 'accept'): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>What's Next?</strong> You'll receive an email with more details about the event as the date approaches.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($message); ?></h5>
                        </div>
                        <div class="alert alert-warning">
                            If you continue to experience issues, please contact the event organizer directly.
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/templates/footer.php'; ?>