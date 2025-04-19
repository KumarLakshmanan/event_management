<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);

// Get page title
$pageTitle = APP_NAME;
switch ($currentPage) {
    case 'dashboard.php':
        $pageTitle .= ' - Dashboard';
        break;
    case 'packages.php':
        $pageTitle .= ' - Packages';
        break;
    case 'services.php':
        $pageTitle .= ' - Services';
        break;
    case 'bookings.php':
        $pageTitle .= ' - Bookings';
        break;
    case 'my-bookings.php':
        $pageTitle .= ' - My Bookings';
        break;
    case 'guests.php':
        $pageTitle .= ' - Guest Management';
        break;
    case 'user-management.php':
        $pageTitle .= ' - User Management';
        break;
    case 'login.php':
        $pageTitle .= ' - Login';
        break;
    case 'register.php':
        $pageTitle .= ' - Register';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <?php if ($isLoggedIn && $currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
        <div class="wrapper">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="content">
                <!-- Navbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle -->
                    <button id="sidebarToggle" class="btn btn-link">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Navbar Items -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Notifications -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <?php
                                $unreadCount = getUnreadNotificationsCount();
                                if ($unreadCount > 0):
                                ?>
                                    <span class="badge badge-danger badge-counter"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Notifications Center
                                </h6>

                                <?php
                                // Get recent notifications
                                $userId = $_SESSION['user_id'] ?? 0;
                                $userRole = $_SESSION['user_role'] ?? '';

                                if (isset($_SESSION['user_id'])) {
                                    $db = Database::getInstance();

                                    // For admin/manager, show all notifications
                                    // For client, show only their own notifications
                                    if ($userRole === 'admin' || $userRole === 'manager') {
                                        $notifications = $db->query("
                                        SELECT * FROM notifications 
                                        ORDER BY created_at DESC 
                                        LIMIT 5
                                    ");
                                    } else {
                                        $notifications = $db->query("
                                        SELECT * FROM notifications 
                                        WHERE user_id = ? OR user_id IS NULL
                                        ORDER BY created_at DESC 
                                        LIMIT 5
                                    ", [$userId]);
                                    }
                                }


                                if (empty($notifications)):
                                ?>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div>
                                            <span class="font-weight-bold">No new notifications</span>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notification):
                                        $icon = 'fa-bell';
                                        $bgClass = 'bg-primary';

                                        switch ($notification['type']) {
                                            case 'booking_created':
                                                $icon = 'fa-calendar-plus';
                                                $bgClass = 'bg-success';
                                                break;
                                            case 'booking_confirmed':
                                                $icon = 'fa-check-circle';
                                                $bgClass = 'bg-success';
                                                break;
                                            case 'booking_cancelled':
                                                $icon = 'fa-times-circle';
                                                $bgClass = 'bg-danger';
                                                break;
                                            case 'booking_completed':
                                                $icon = 'fa-calendar-check';
                                                $bgClass = 'bg-primary';
                                                break;
                                            case 'login':
                                                $icon = 'fa-sign-in-alt';
                                                $bgClass = 'bg-info';
                                                break;
                                            case 'register':
                                                $icon = 'fa-user-plus';
                                                $bgClass = 'bg-info';
                                                break;
                                            case 'guest_rsvp_accepted':
                                                $icon = 'fa-user-check';
                                                $bgClass = 'bg-success';
                                                break;
                                            case 'guest_rsvp_declined':
                                                $icon = 'fa-user-times';
                                                $bgClass = 'bg-danger';
                                                break;
                                            case 'profile_updated':
                                                $icon = 'fa-user-edit';
                                                $bgClass = 'bg-info';
                                                break;
                                            case 'password_changed':
                                                $icon = 'fa-key';
                                                $bgClass = 'bg-warning';
                                                break;
                                        }
                                    ?>
                                        <a class="dropdown-item d-flex align-items-center" href="<?php echo $notification['link'] ?? 'notifications.php'; ?>">
                                            <div class="mr-3">
                                                <div class="icon-circle <?php echo $bgClass; ?>">
                                                    <i class="fas <?php echo $icon; ?> text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500"><?php echo date('F d, Y', strtotime($notification['created_at'])); ?></div>
                                                <span class="font-weight-bold"><?php echo $notification['message']; ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <a class="dropdown-item text-center small text-gray-500" href="notifications.php">Show All Notifications</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
                                <i class="fas fa-user-circle fa-fw fa-2x text-gray-400"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    My Profile
                                </a>
                                <a class="dropdown-item" href="notifications.php">
                                    <i class="fas fa-bell fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Notifications
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../handlers/auth.php?action=logout">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Flash Message -->
                <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
                    <div id="flashMessage" class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <!-- Page Content -->
                <div class="container-fluid">
                <?php else: ?>
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>

                    <!-- Flash Message -->
                    <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
                        <div id="flashMessage" class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['flash_message']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>
                <?php endif; ?>