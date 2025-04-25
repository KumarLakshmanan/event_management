<?php
/**
 * Administrator dashboard for Event Planning Platform
 */

// Include configuration
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/includes/config.php';
    require_once INCLUDES_PATH . 'auth.php';
    require_once INCLUDES_PATH . 'functions.php';
}

// Require user to be logged in with administrator role
requireRole('administrator');

// Get admin's data
$db = getDBConnection();

// Get count of users
$stmt = $db->query("SELECT COUNT(*) as count FROM members");
$usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get count of services
$stmt = $db->query("SELECT COUNT(*) as count FROM products");
$servicesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get count of packages
$stmt = $db->query("SELECT COUNT(*) as count FROM bundles");
$packagesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get count of bookings
$stmt = $db->query("SELECT COUNT(*) as count FROM reservations");
$bookingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent users
$stmt = $db->prepare("
    SELECT *
    FROM members
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending bookings
$stmt = $db->prepare("
    SELECT r.*, b.name as bundle_name, m.name as client_name
    FROM reservations r
    JOIN bundles b ON r.bundle_id = b.id
    JOIN members m ON r.user_id = m.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute();
$pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = 'Admin Dashboard';

// Include header
include_once TEMPLATES_PATH . 'header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Administrator Dashboard</h1>
        <div>
            <a href="/dashboard/users.php?action=create" class="btn btn-outline-primary me-2">
                <i class="bi bi-person-plus me-2"></i>Add User
            </a>
            <a href="/dashboard/packages.php?action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create Package
            </a>
        </div>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-stat bg-primary">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <h3><?= $usersCount ?></h3>
                <p>Users</p>
                <a href="/dashboard/users.php" class="text-white">Manage Users <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-stat stat-services">
                <div class="stat-icon"><i class="bi bi-gear"></i></div>
                <h3><?= $servicesCount ?></h3>
                <p>Services</p>
                <a href="/dashboard/services.php" class="text-white">Manage Services <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-stat stat-packages">
                <div class="stat-icon"><i class="bi bi-box"></i></div>
                <h3><?= $packagesCount ?></h3>
                <p>Packages</p>
                <a href="/dashboard/packages.php" class="text-white">Manage Packages <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-stat stat-bookings">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <h3><?= $bookingsCount ?></h3>
                <p>Bookings</p>
                <a href="/dashboard/bookings.php" class="text-white">View Bookings <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Users -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Recent Users</h5>
                    <a href="/dashboard/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentUsers)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>There are no recent users to display.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><span class="badge bg-secondary"><?= ucfirst($user['role']) ?></span></td>
                                            <td>
                                                <a href="/dashboard/users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit User">
                                                    <i class="bi bi-pencil"></i>
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
        </div>
        
        <!-- Pending Bookings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Pending Bookings</h5>
                    <a href="/dashboard/bookings.php?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingBookings)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>There are no pending bookings to display.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Package</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingBookings as $booking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($booking['client_name']) ?></td>
                                            <td><?= htmlspecialchars($booking['bundle_name']) ?></td>
                                            <td><?= formatDate($booking['event_date'], 'M d, Y') ?></td>
                                            <td>
                                                <a href="/dashboard/bookings.php?action=view&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/dashboard/bookings.php?action=confirm&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Confirm Booking">
                                                    <i class="bi bi-check-circle"></i>
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
        </div>
    </div>
    
    <!-- Admin Tools -->
    <h2 class="mt-4 mb-3">Administrative Tools</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-gear me-2"></i>User Management</h5>
                    <p class="card-text">Add, edit, and manage user accounts and permissions.</p>
                    <a href="/dashboard/users.php" class="btn btn-outline-primary">Manage Users</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-gear me-2"></i>Service Management</h5>
                    <p class="card-text">Create and manage individual services offered in packages.</p>
                    <a href="/dashboard/services.php" class="btn btn-outline-primary">Manage Services</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box me-2"></i>Package Management</h5>
                    <p class="card-text">Create and customize event packages from available services.</p>
                    <a href="/dashboard/packages.php" class="btn btn-outline-primary">Manage Packages</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once TEMPLATES_PATH . 'footer.php';
?>