<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get data from database or mock files
if (USE_DATABASE) {
    $db = Database::getInstance();
    
    // Get all data
    $users = $db->query("SELECT * FROM users");
    $packages = $db->query("SELECT * FROM packages");
    $services = $db->query("SELECT * FROM services");
    
    // Get bookings based on user role
    if ($_SESSION['user_role'] === 'client') {
        $bookings = $db->query("SELECT * FROM bookings WHERE user_id = ?", [$_SESSION['user_id']]);
        
        // Count booking statuses
        $pendingCount = $db->queryOne("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'pending'", [$_SESSION['user_id']]);
        $confirmedCount = $db->queryOne("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'confirmed'", [$_SESSION['user_id']]);
        
        $pendingBookings = $pendingCount['count'];
        $confirmedBookings = $confirmedCount['count'];
        
        // Get recent bookings
        $recentBookings = $db->query("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
    } else {
        $bookings = $db->query("SELECT * FROM bookings");
        
        // Count booking statuses
        $pendingCount = $db->queryOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
        $confirmedCount = $db->queryOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'");
        
        $pendingBookings = $pendingCount['count'];
        $confirmedBookings = $confirmedCount['count'];
        
        // Get recent bookings
        $recentBookings = $db->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
    }
    
    // Count totals
    $totalPackages = count($packages);
    $totalServices = count($services);
    $totalBookings = count($bookings);
    
} else {
    // Fallback to mock data
    $users = getMockData('users.json');
    $packages = getMockData('packages.json');
    $services = getMockData('services.json');
    $bookings = getMockData('bookings.json');
    
    // Count data
    $totalPackages = count($packages);
    $totalServices = count($services);
    $totalBookings = count($bookings);
    $pendingBookings = 0;
    $confirmedBookings = 0;
    
    // Count booking statuses
    foreach ($bookings as $booking) {
        if ($booking['status'] === 'pending') {
            $pendingBookings++;
        } else if ($booking['status'] === 'confirmed') {
            $confirmedBookings++;
        }
    }
    
    // Filter bookings for clients to show only their own
    if ($_SESSION['user_role'] === 'client') {
        $filteredBookings = array_filter($bookings, function($booking) {
            return $booking['user_id'] == $_SESSION['user_id'];
        });
        $totalBookings = count($filteredBookings);
        
        // Recount status
        $pendingBookings = 0;
        $confirmedBookings = 0;
        foreach ($filteredBookings as $booking) {
            if ($booking['status'] === 'pending') {
                $pendingBookings++;
            } else if ($booking['status'] === 'confirmed') {
                $confirmedBookings++;
            }
        }
    }
    
    // Get recent bookings
    $recentBookings = array_slice($bookings, 0, 5);
    
    // Filter for clients
    if ($_SESSION['user_role'] === 'client') {
        $recentBookings = array_filter($recentBookings, function($booking) {
            return $booking['user_id'] == $_SESSION['user_id'];
        });
        $recentBookings = array_slice($recentBookings, 0, 5);
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Total Packages Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Packages</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPackages; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Services Card -->
    <?php if ($_SESSION['user_role'] !== 'client'): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Services</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalServices; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-concierge-bell fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Total Bookings Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalBookings; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingBookings; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Bookings Overview</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Booking Status</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Confirmed
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Pending
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($_SESSION['user_role'] !== 'client'): ?>
                        <th>Client</th>
                        <?php endif; ?>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Event Place</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Get user names and package names
                    $userNames = [];
                    foreach ($users as $user) {
                        $userNames[$user['id']] = $user['name'];
                    }
                    
                    $packageNames = [];
                    foreach ($packages as $package) {
                        $packageNames[$package['id']] = $package['name'];
                    }
                    
                    foreach ($recentBookings as $booking): 
                    ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <?php if ($_SESSION['user_role'] !== 'client'): ?>
                        <td><?php echo $userNames[$booking['user_id']] ?? 'Unknown'; ?></td>
                        <?php endif; ?>
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
                            <?php if ($_SESSION['user_role'] === 'client'): ?>
                                <a href="my-bookings.php" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php else: ?>
                                <a href="bookings.php" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="<?php echo ($_SESSION['user_role'] !== 'client') ? '7' : '6'; ?>" class="text-center">No bookings found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
$(document).ready(function() {
    // Area Chart
    var ctx = document.getElementById("bookingsChart");
    var bookingsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
                label: "Bookings",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: [0, <?php echo $totalBookings; ?>, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        beginAtZero: true
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10
            }
        }
    });

    // Pie Chart
    var ctx2 = document.getElementById("bookingStatusChart");
    var bookingStatusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ["Confirmed", "Pending"],
            datasets: [{
                data: [<?php echo $confirmedBookings; ?>, <?php echo $pendingBookings; ?>],
                backgroundColor: ['#1cc88a', '#f6c23e'],
                hoverBackgroundColor: ['#17a673', '#f4b619'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
