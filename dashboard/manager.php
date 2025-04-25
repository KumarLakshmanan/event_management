<?php
/**
 * Manager dashboard for Event Planning Platform
 */

// Include configuration
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/includes/config.php';
}

// Require user to be logged in with manager role
requireRole(['manager', 'administrator']);

// Get manager's data
$db = getDBConnection();

// Get count of services
$stmt = $db->query("SELECT COUNT(*) as count FROM products");
$servicesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get count of packages
$stmt = $db->query("SELECT COUNT(*) as count FROM bundles");
$packagesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get count of pending bookings
$stmt = $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
$pendingBookingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent bookings
$stmt = $db->prepare("
    SELECT r.*, b.name as bundle_name, m.name as client_name
    FROM reservations r
    JOIN bundles b ON r.bundle_id = b.id
    JOIN members m ON r.user_id = m.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = 'Manager Dashboard';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manager Dashboard</h1>
        <div>
            <a href="/dashboard/services.php?action=create" class="btn btn-outline-primary me-2">
                <i class="bi bi-plus-circle me-2"></i>Add Service
            </a>
            <a href="/dashboard/packages.php?action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create Package
            </a>
        </div>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="dashboard-stat stat-services">
                <div class="stat-icon"><i class="bi bi-gear"></i></div>
                <h3><?= $servicesCount ?></h3>
                <p>Services Available</p>
                <a href="/dashboard/services.php" class="text-white">Manage Services <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="dashboard-stat stat-packages">
                <div class="stat-icon"><i class="bi bi-box"></i></div>
                <h3><?= $packagesCount ?></h3>
                <p>Event Packages</p>
                <a href="/dashboard/packages.php" class="text-white">Manage Packages <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="dashboard-stat stat-bookings">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <h3><?= $pendingBookingsCount ?></h3>
                <p>Pending Bookings</p>
                <a href="/dashboard/bookings.php?status=pending" class="text-white">Review Bookings <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <h2 class="mb-3">Recent Bookings</h2>
    
    <?php if (empty($recentBookings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>There are no recent bookings to display.
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['client_name']) ?></td>
                            <td><?= htmlspecialchars($booking['bundle_name']) ?></td>
                            <td><?= formatDate($booking['event_date']) ?></td>
                            <td><?= getStatusBadge($booking['status']) ?></td>
                            <td>
                                <a href="/dashboard/bookings.php?action=view&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="/dashboard/bookings.php?action=confirm&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Confirm Booking">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-3">
            <a href="/dashboard/bookings.php" class="btn btn-outline-primary">View All Bookings</a>
        </div>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <h2 class="mt-5 mb-3">Management Tools</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-gear me-2"></i>Service Management</h5>
                    <p class="card-text">Create, edit and manage available services for event packages.</p>
                    <a href="/dashboard/services.php" class="btn btn-outline-primary">Manage Services</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box me-2"></i>Package Management</h5>
                    <p class="card-text">Create and manage event packages by combining different services.</p>
                    <a href="/dashboard/packages.php" class="btn btn-outline-primary">Manage Packages</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar-check me-2"></i>Booking Management</h5>
                    <p class="card-text">Review, confirm, and manage client bookings and events.</p>
                    <a href="/dashboard/bookings.php" class="btn btn-outline-primary">Manage Bookings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>