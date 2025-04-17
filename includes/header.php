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
