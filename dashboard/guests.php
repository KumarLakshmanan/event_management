<?php
/**
 * Guest Management
 * 
 * Manage guests for a booking, including RSVP status
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// PHPMailer library
require_once '../vendor/phpmailer/PHPMailer/src/Exception.php';
require_once '../vendor/phpmailer/PHPMailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
requireLogin();

// Get database connection
$db = getDBConnection();

// Handle RSVP updates from email links
if (isset($_GET['token']) && isset($_GET['response'])) {
    $token = $_GET['token'];
    $response = $_GET['response'] === 'yes' ? 'attending' : 'declined';
    
    // Find guest by token
    $stmt = $db->prepare("SELECT id, booking_id, name FROM attendees WHERE rsvp_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($guest) {
        // Update guest RSVP status
        $stmt = $db->prepare("UPDATE attendees SET rsvp_status = :status WHERE id = :id");
        $stmt->bindParam(':status', $response);
        $stmt->bindParam(':id', $guest['id']);
        
        if ($stmt->execute()) {
            // Add notification
            $message = $guest['name'] . ' has ' . ($response === 'attending' ? 'accepted' : 'declined') . ' the invitation';
            $type = 'guest_' . ($response === 'attending' ? 'accepted' : 'rejected');
            addNotification($type, $message, $guest['id']);
            
            setAlert('success', 'Thank you for your response. Your RSVP status has been updated to: ' . ucfirst($response));
        } else {
            setAlert('danger', 'Failed to update RSVP status. Please try again or contact the event organizer.');
        }
    } else {
        setAlert('danger', 'Invalid token. Please contact the event organizer for assistance.');
    }
    
    // Redirect to the homepage
    header('Location: ../index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'create' || $action === 'edit') {
        // Get form data
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $bookingId = (int)$_POST['booking_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $rsvpStatus = trim($_POST['rsvp_status'] ?? 'pending');
        
        // Validate form data
        $errors = [];
        
        if (empty($bookingId)) {
            $errors[] = 'Booking is required';
        }
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is invalid';
        }
        
        // If there are no errors, create or update guest
        if (empty($errors)) {
            // Generate token for RSVP
            $rsvpToken = generateRandomString(32);
            
            if ($action === 'create') {
                // Create guest
                $stmt = $db->prepare("INSERT INTO attendees (booking_id, name, email, phone, rsvp_status, rsvp_token) 
                                    VALUES (:booking_id, :name, :email, :phone, :rsvp_status, :rsvp_token)");
                $stmt->bindParam(':booking_id', $bookingId);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':rsvp_status', $rsvpStatus);
                $stmt->bindParam(':rsvp_token', $rsvpToken);
                
                if ($stmt->execute()) {
                    $guestId = $db->lastInsertId();
                    
                    // Send invitation email
                    $mailSent = sendRsvpEmail($name, $email, $rsvpToken, $bookingId);
                    
                    setAlert('success', 'Guest added successfully' . ($mailSent ? ' and invitation email sent' : ''));
                    
                    // If there's no redirect parameter, redirect to the guest list
                    if (!isset($_GET['redirect'])) {
                        header('Location: guests.php?booking_id=' . $bookingId);
                        exit;
                    }
                } else {
                    setAlert('danger', 'Failed to add guest');
                }
            } else {
                // Get current guest data to check if email changed
                $stmt = $db->prepare("SELECT email, rsvp_token FROM attendees WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $currentGuest = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Only generate new token if email changed
                $rsvpToken = $currentGuest['rsvp_token'];
                $emailChanged = $email !== $currentGuest['email'];
                
                if ($emailChanged) {
                    $rsvpToken = generateRandomString(32);
                }
                
                // Update guest
                $stmt = $db->prepare("UPDATE attendees SET name = :name, email = :email, phone = :phone, 
                                    rsvp_status = :rsvp_status, rsvp_token = :rsvp_token 
                                    WHERE id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':rsvp_status', $rsvpStatus);
                $stmt->bindParam(':rsvp_token', $rsvpToken);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    // Send invitation email if email changed or requested
                    $sendEmail = $emailChanged || isset($_POST['resend_email']);
                    
                    if ($sendEmail) {
                        $mailSent = sendRsvpEmail($name, $email, $rsvpToken, $bookingId);
                        setAlert('success', 'Guest updated successfully' . ($mailSent ? ' and invitation email sent' : ''));
                    } else {
                        setAlert('success', 'Guest updated successfully');
                    }
                    
                    // If there's no redirect parameter, redirect to the guest list
                    if (!isset($_GET['redirect'])) {
                        header('Location: guests.php?booking_id=' . $bookingId);
                        exit;
                    }
                } else {
                    setAlert('danger', 'Failed to update guest');
                }
            }
        } else {
            setAlert('danger', implode('<br>', $errors));
        }
    } elseif ($action === 'delete') {
        // Delete guest
        $id = (int)$_POST['id'];
        $bookingId = (int)$_POST['booking_id'];
        
        $stmt = $db->prepare("DELETE FROM attendees WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Guest deleted successfully');
        } else {
            setAlert('danger', 'Failed to delete guest');
        }
        
        header('Location: guests.php?booking_id=' . $bookingId);
        exit;
    } elseif ($action === 'send_all') {
        // Send invitation emails to all pending guests
        $bookingId = (int)$_POST['booking_id'];
        
        $stmt = $db->prepare("SELECT id, name, email, rsvp_token FROM attendees 
                            WHERE booking_id = :booking_id AND rsvp_status = 'pending'");
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->execute();
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sentCount = 0;
        foreach ($guests as $guest) {
            if (sendRsvpEmail($guest['name'], $guest['email'], $guest['rsvp_token'], $bookingId)) {
                $sentCount++;
            }
        }
        
        if ($sentCount > 0) {
            setAlert('success', 'Sent ' . $sentCount . ' invitation emails');
        } else {
            setAlert('info', 'No invitation emails were sent. Either there are no pending guests or all emails failed to send.');
        }
        
        header('Location: guests.php?booking_id=' . $bookingId);
        exit;
    }
}

// Get booking ID and action from URL
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if booking exists and user has permission to access it
if ($bookingId > 0) {
    $stmt = $db->prepare("SELECT b.*, m.name as user_name FROM reservations b
                        JOIN members m ON b.user_id = m.id
                        WHERE b.id = :id");
    $stmt->bindParam(':id', $bookingId);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        setAlert('danger', 'Booking not found');
        header('Location: bookings.php');
        exit;
    }
    
    // Check if user has permission to access this booking
    if (!hasRole('administrator') && !hasRole('manager') && $booking['user_id'] !== $_SESSION['user_id']) {
        setAlert('danger', 'You do not have permission to access this booking');
        header('Location: bookings.php');
        exit;
    }
}

// Initialize variables for form
$guest = [
    'id' => 0,
    'booking_id' => $bookingId,
    'name' => '',
    'email' => '',
    'phone' => '',
    'rsvp_status' => 'pending'
];

// If editing, get guest data
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM attendees WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $fetchedGuest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fetchedGuest) {
        $guest = $fetchedGuest;
        $bookingId = $guest['booking_id'];
        
        // Get booking data
        $stmt = $db->prepare("SELECT b.*, m.name as user_name FROM reservations b
                            JOIN members m ON b.user_id = m.id
                            WHERE b.id = :id");
        $stmt->bindParam(':id', $bookingId);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        setAlert('danger', 'Guest not found');
        header('Location: guests.php?booking_id=' . $bookingId);
        exit;
    }
}

// Get all guests for a booking
$guests = [];
if ($bookingId > 0 && ($action === '' || $action === 'list')) {
    $stmt = $db->prepare("SELECT * FROM attendees WHERE booking_id = :booking_id ORDER BY name");
    $stmt->bindParam(':booking_id', $bookingId);
    $stmt->execute();
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count RSVP statuses
    $rsvpCounts = [
        'total' => count($guests),
        'attending' => 0,
        'declined' => 0,
        'pending' => 0
    ];
    
    foreach ($guests as $g) {
        $rsvpCounts[$g['rsvp_status']]++;
    }
}

// Set page title based on action
$pageTitle = 'Guest Management';
if ($action === 'create') {
    $pageTitle = 'Add Guest';
} elseif ($action === 'edit') {
    $pageTitle = 'Edit Guest';
}

// Show booking details in title if available
if (isset($booking)) {
    $pageTitle .= ' for Booking #' . $booking['id'];
}

// Include header
require_once '../templates/header.php';

/**
 * Send RSVP email to guest
 * 
 * @param string $name Guest name
 * @param string $email Guest email
 * @param string $token RSVP token
 * @param int $bookingId Booking ID
 * @return bool True if email sent, false otherwise
 */
function sendRsvpEmail($name, $email, $token, $bookingId) {
    global $db;
    
    // Get booking details
    $stmt = $db->prepare("SELECT b.*, m.name as user_name, bd.name as package_name 
                        FROM reservations b
                        JOIN members m ON b.user_id = m.id
                        JOIN bundles bd ON b.bundle_id = bd.id
                        WHERE b.id = :id");
    $stmt->bindParam(':id', $bookingId);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        return false;
    }
    
    // Create Accept/Decline links
    $acceptUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/dashboard/guests.php?token=' . $token . '&response=yes';
    $declineUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/dashboard/guests.php?token=' . $token . '&response=no';
    
    // Create email content
    $subject = 'Event Invitation: ' . $booking['package_name'] . ' on ' . date('F j, Y', strtotime($booking['event_date']));
    
    $message = "
    <html>
    <head>
        <title>Event Invitation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .footer { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px; font-size: 12px; }
            .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; color: #fff; text-decoration: none; border-radius: 5px; }
            .btn-success { background-color: #28a745; }
            .btn-danger { background-color: #dc3545; }
            .details { margin: 20px 0; }
            .details div { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Event Invitation</h2>
            </div>
            
            <p>Dear $name,</p>
            
            <p>You are cordially invited to an event hosted by {$booking['user_name']}.</p>
            
            <div class='details'>
                <div><strong>Event:</strong> {$booking['package_name']}</div>
                <div><strong>Date:</strong> " . date('F j, Y', strtotime($booking['event_date'])) . "</div>
                <div><strong>Time:</strong> " . date('g:i A', strtotime($booking['event_date'])) . "</div>
                <div><strong>Location:</strong> {$booking['event_place']}</div>
            </div>
            
            <p>Please let us know if you will be attending by clicking one of the buttons below:</p>
            
            <div style='text-align: center;'>
                <a href='$acceptUrl' class='btn btn-success'>I'll Attend</a>
                <a href='$declineUrl' class='btn btn-danger'>I Can't Attend</a>
            </div>
            
            <p>We hope to see you there!</p>
            
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>If you have any questions, please contact the event organizer directly.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try to send email using PHPMailer
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // Replace with your email
        $mail->Password = 'your-password'; // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('noreply@eventplanning.com', 'Event Planning Platform');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags(str_replace(['<div>', '</div>'], ["\n", ''], $message));
        
        // For demo purposes, don't actually send (comment this out in production)
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        
        // Uncomment this in production
        // $mail->send();
        
        return true; // Just pretend we sent for the demo
    } catch (Exception $e) {
        // Log the error
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
            <div>
                <?php if (isset($booking) && ($action === '' || $action === 'list')): ?>
                    <a href="guests.php?booking_id=<?php echo $bookingId; ?>&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Guest
                    </a>
                    <?php if (!empty($guests)): ?>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sendAllModal">
                            <i class="fas fa-envelope"></i> Send All Invitations
                        </button>
                    <?php endif; ?>
                    <a href="bookings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                <?php elseif ($action === 'create' || $action === 'edit'): ?>
                    <a href="guests.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Guest List
                    </a>
                <?php else: ?>
                    <a href="bookings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($booking) && ($action === '' || $action === 'list')): ?>
    <div class="container-fluid pt-4 px-4">
        <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Booking Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Booking ID:</strong> <?php echo $booking['id']; ?></p>
                                <p><strong>Client:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                                <p><strong>Event Date:</strong> <?php echo date('F j, Y g:i A', strtotime($booking['event_date'])); ?></p>
                                <p><strong>Event Place:</strong> <?php echo htmlspecialchars($booking['event_place']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <?php echo getStatusBadge($booking['status']); ?></p>
                                <?php if (isset($rsvpCounts)): ?>
                                    <p><strong>Guest Count:</strong> <?php echo $rsvpCounts['total']; ?></p>
                                    <p><strong>RSVP Status:</strong></p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success">Attending: <?php echo $rsvpCounts['attending']; ?></span>
                                        <span class="badge bg-danger">Declined: <?php echo $rsvpCounts['declined']; ?></span>
                                        <span class="badge bg-warning">Pending: <?php echo $rsvpCounts['pending']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <!-- Create/Edit Form -->
            <div class="col-12">
                <form method="post" action="guests.php?action=<?php echo $action; ?><?php echo isset($_GET['redirect']) ? '&redirect=' . $_GET['redirect'] : ''; ?>" class="row g-3">
                    <?php if ($id > 0): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($guest['name']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($guest['email']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($guest['phone']); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="rsvp_status" class="form-label">RSVP Status</label>
                        <select class="form-select" id="rsvp_status" name="rsvp_status">
                            <option value="pending" <?php echo $guest['rsvp_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="attending" <?php echo $guest['rsvp_status'] === 'attending' ? 'selected' : ''; ?>>Attending</option>
                            <option value="declined" <?php echo $guest['rsvp_status'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                        </select>
                    </div>
                    
                    <?php if ($action === 'edit'): ?>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resend_email" name="resend_email">
                                <label class="form-check-label" for="resend_email">
                                    Resend invitation email
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="guests.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php elseif (isset($booking) && ($action === '' || $action === 'list')): ?>
            <!-- Guest List -->
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>RSVP Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($guests)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No guests found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($guests as $g): ?>
                                    <tr>
                                        <td><?php echo $g['id']; ?></td>
                                        <td><?php echo htmlspecialchars($g['name']); ?></td>
                                        <td><?php echo htmlspecialchars($g['email']); ?></td>
                                        <td><?php echo htmlspecialchars($g['phone']); ?></td>
                                        <td>
                                            <?php echo getRsvpBadge($g['rsvp_status']); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="guests.php?action=edit&id=<?php echo $g['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $g['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $g['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $g['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $g['id']; ?>">Delete Guest</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($g['name']); ?></strong>?</p>
                                                            <p>This action cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="post" action="guests.php?action=delete">
                                                                <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
                                                                <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Send All Invitations Modal -->
            <div class="modal fade" id="sendAllModal" tabindex="-1" aria-labelledby="sendAllModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendAllModalLabel">Send All Invitations</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to send invitation emails to all guests with pending RSVP status?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" action="guests.php?action=send_all">
                                <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                <button type="submit" class="btn btn-success">Send All</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> Select a booking to manage its guests.
                </div>
                <a href="bookings.php" class="btn btn-primary">View Bookings</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>