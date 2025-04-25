<?php
/**
 * Client dashboard for Event Planning Platform
 */

// Include configuration
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/includes/config.php';
    require_once INCLUDES_PATH . 'auth.php';
    require_once INCLUDES_PATH . 'functions.php';
}

// Require user to be logged in with client role
requireRole('client');

// Get client's data
$db = getDBConnection();

// Get client's bookings count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM reservations WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$bookingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get client's guests count
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM attendees a
    JOIN reservations r ON a.booking_id = r.id
    WHERE r.user_id = :user_id
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$guestsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get upcoming events
$stmt = $db->prepare("
    SELECT r.*, b.name as bundle_name 
    FROM reservations r
    JOIN bundles b ON r.bundle_id = b.id
    WHERE r.user_id = :user_id AND r.event_date > CURRENT_TIMESTAMP
    ORDER BY r.event_date ASC
    LIMIT 5
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = 'Client Dashboard';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Welcome, <?= $_SESSION['user_name'] ?></h1>
        <a href="/dashboard/bookings.php?action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Book New Event
        </a>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-stat stat-bookings">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <h3><?= $bookingsCount ?></h3>
                <p>Events Booked</p>
                <a href="/dashboard/bookings.php" class="text-white">View All <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="dashboard-stat stat-packages">
                <div class="stat-icon"><i class="bi bi-box"></i></div>
                <h3>Explore</h3>
                <p>Event Packages</p>
                <a href="/dashboard/packages.php" class="text-white">Browse Packages <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="dashboard-stat stat-guests">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <h3><?= $guestsCount ?></h3>
                <p>Total Guests</p>
                <a href="/dashboard/guests.php" class="text-white">Manage Guests <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <h2 class="mb-3">Upcoming Events</h2>
    
    <?php if (empty($upcomingEvents)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>You have no upcoming events. 
            <a href="/dashboard/packages.php">Browse packages</a> to book a new event.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Event Package</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['bundle_name']) ?></td>
                            <td><?= formatDate($event['event_date']) ?></td>
                            <td><?= htmlspecialchars($event['event_place']) ?></td>
                            <td><?= getStatusBadge($event['status']) ?></td>
                            <td>
                                <a href="/dashboard/bookings.php?action=view&id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/dashboard/guests.php?booking_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Manage Guests">
                                    <i class="bi bi-people"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($upcomingEvents) >= 5): ?>
            <div class="text-center mt-3">
                <a href="/dashboard/bookings.php" class="btn btn-outline-primary">View All Events</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <h2 class="mt-5 mb-3">Quick Links</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box me-2"></i>Browse Packages</h5>
                    <p class="card-text">View all available event packages and find the perfect one for your needs.</p>
                    <a href="/dashboard/packages.php" class="btn btn-outline-primary">View Packages</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar-plus me-2"></i>Book an Event</h5>
                    <p class="card-text">Start planning your next event by booking a package or customizing your own.</p>
                    <a href="/dashboard/bookings.php?action=create" class="btn btn-outline-primary">Book Now</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-circle me-2"></i>Update Profile</h5>
                    <p class="card-text">Update your account information and preferences.</p>
                    <a href="/dashboard/profile.php" class="btn btn-outline-primary">View Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>