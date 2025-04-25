<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Package.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/User.php';

// Require the user to be logged in
requireLogin();

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current user
$user = getCurrentUser();

// Initialize models
$bookingModel = new Booking();
$packageModel = new Package();
$serviceModel = new Service();
$userModel = new User();

// Get statistics based on user role
$stats = [];

// Common stat: user's upcoming bookings (for all roles)
$userBookings = $bookingModel->getByUserId($user['id'], 5, 0, 'event_date', 'ASC');
$upcomingBookingsCount = count(array_filter($userBookings, function($booking) {
    return $booking['status'] === STATUS_CONFIRMED && strtotime($booking['event_date']) >= time();
}));

// Role-specific dashboard data
if ($user['role'] === ROLE_ADMIN || $user['role'] === ROLE_MANAGER) {
    // Admin and Manager see booking stats
    $stats = $bookingModel->getStatistics();
    
    // Recent bookings for review
    $recentBookings = $bookingModel->getAll(5);
    
    // Get packages and services count
    $packagesCount = $packageModel->countAll();
    $servicesCount = $serviceModel->countAll();
    
    // For admin only - get user counts
    if ($user['role'] === ROLE_ADMIN) {
        $clientsCount = count($userModel->getByRole(ROLE_CLIENT));
        $managersCount = count($userModel->getByRole(ROLE_MANAGER));
        $adminsCount = count($userModel->getByRole(ROLE_ADMIN));
    }
} else {
    // Client-specific stats
    $stats['total'] = $bookingModel->countByUserId($user['id']);
    $stats['upcoming'] = $upcomingBookingsCount;
    
    // Get client's bookings
    $clientBookings = $bookingModel->getByUserId($user['id'], 5);
}

// Set page title and sidebar flag for the template
$title = 'Dashboard';
$showSidebar = true;

// Include header
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Dashboard</h1>
        <span class="badge bg-primary"><?php echo getRoleName($user['role']); ?></span>
    </div>
    
    <!-- Welcome Message -->
    <div class="alert alert-info">
        <h4>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h4>
        <p>This is your personalized dashboard where you can manage your event planning activities.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php if ($user['role'] === ROLE_CLIENT): ?>
            <!-- Client Stats -->
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Total Bookings</h6>
                            <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Upcoming Events</h6>
                            <h2 class="mb-0"><?php echo $stats['upcoming']; ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card info">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Available Packages</h6>
                            <h2 class="mb-0"><?php echo $packageModel->countAll(); ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card warning">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">Quick Actions</h6>
                        <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-sm btn-primary mb-2">
                            <i class="fas fa-box me-1"></i> Browse Packages
                        </a>
                        <a href="<?php echo APP_URL; ?>dashboard/bookings.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-1"></i> View My Bookings
                        </a>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Admin/Manager Stats -->
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Total Bookings</h6>
                            <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Upcoming Events</h6>
                            <h2 class="mb-0"><?php echo $stats['upcoming']; ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card info">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Total Revenue</h6>
                            <h2 class="mb-0"><?php echo formatPrice($stats['total_revenue']); ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card stats-card warning">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted">Pending Bookings</h6>
                            <h2 class="mb-0"><?php echo $stats['pending']; ?></h2>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8 mb-4">
            <?php if ($user['role'] === ROLE_CLIENT): ?>
                <!-- Client's Recent Bookings -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Your Recent Bookings</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($clientBookings)): ?>
                            <div class="p-4 text-center">
                                <p>You haven't made any bookings yet.</p>
                                <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-primary">Browse Packages</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Event Date</th>
                                            <th>Location</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clientBookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                                <td><?php echo formatDate($booking['event_date']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['event_location']); ?></td>
                                                <td><?php echo formatPrice($booking['total_price'] - $booking['discount_applied']); ?></td>
                                                <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                <td>
                                                    <a href="<?php echo APP_URL; ?>dashboard/bookings.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
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
                    <div class="card-footer bg-light">
                        <a href="<?php echo APP_URL; ?>dashboard/bookings.php" class="btn btn-sm btn-outline-secondary">View All Bookings</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Admin/Manager Recent Bookings -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Recent Bookings</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentBookings)): ?>
                            <div class="p-4 text-center">
                                <p>No bookings have been made yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client</th>
                                            <th>Package</th>
                                            <th>Event Date</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td>#<?php echo $booking['id']; ?></td>
                                                <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                                <td><?php echo formatDate($booking['event_date']); ?></td>
                                                <td><?php echo formatPrice($booking['total_price'] - $booking['discount_applied']); ?></td>
                                                <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                <td>
                                                    <a href="<?php echo APP_URL; ?>dashboard/bookings.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
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
                    <div class="card-footer bg-light">
                        <a href="<?php echo APP_URL; ?>dashboard/bookings.php" class="btn btn-sm btn-outline-secondary">View All Bookings</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4 mb-4">
            <?php if ($user['role'] === ROLE_ADMIN): ?>
                <!-- System Overview for Admin -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">System Overview</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Packages
                                <span class="badge bg-primary rounded-pill"><?php echo $packagesCount; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Services
                                <span class="badge bg-primary rounded-pill"><?php echo $servicesCount; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Clients
                                <span class="badge bg-primary rounded-pill"><?php echo $clientsCount; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Managers
                                <span class="badge bg-primary rounded-pill"><?php echo $managersCount; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Administrators
                                <span class="badge bg-primary rounded-pill"><?php echo $adminsCount; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php elseif ($user['role'] === ROLE_MANAGER): ?>
                <!-- Manager Quick Links -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>dashboard/packages.php?action=create" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Create New Package
                            </a>
                            <a href="<?php echo APP_URL; ?>dashboard/services.php?action=create" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle me-1"></i> Add New Service
                            </a>
                            <a href="<?php echo APP_URL; ?>dashboard/bookings.php?status=pending" class="btn btn-outline-warning">
                                <i class="fas fa-clock me-1"></i> Review Pending Bookings
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Package & Service Stats -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Inventory</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Packages
                                <span class="badge bg-primary rounded-pill"><?php echo $packagesCount; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Services
                                <span class="badge bg-primary rounded-pill"><?php echo $servicesCount; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <!-- Client Quick Links -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>dashboard/packages.php" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Browse Event Packages
                            </a>
                            <a href="<?php echo APP_URL; ?>dashboard/bookings.php?action=create" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-plus me-1"></i> Book New Event
                            </a>
                            <a href="<?php echo APP_URL; ?>dashboard/guests.php" class="btn btn-outline-secondary">
                                <i class="fas fa-users me-1"></i> Manage Guest Lists
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Events Card -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Upcoming Events</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $upcomingBookings = array_filter($userBookings, function($booking) {
                            return $booking['status'] === STATUS_CONFIRMED && strtotime($booking['event_date']) >= time();
                        });
                        
                        if (empty($upcomingBookings)):
                        ?>
                            <p class="text-muted">No upcoming events. Ready to plan your next celebration?</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($upcomingBookings as $booking): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($booking['package_name']); ?></h6>
                                            <small><?php echo getStatusBadge($booking['status']); ?></small>
                                        </div>
                                        <p class="mb-1">
                                            <i class="fas fa-calendar-day me-1"></i> 
                                            <?php echo formatDate($booking['event_date']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i> 
                                            <?php echo htmlspecialchars($booking['event_location']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <a href="<?php echo APP_URL; ?>dashboard/bookings.php?id=<?php echo $booking['id']; ?>">
                                                View Details
                                            </a>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>
