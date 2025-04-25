<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get page title from query parameter or use default
$title = isset($title) ? $title : '';
$pageTitle = !empty($title) ? $title . ' - ' . APP_NAME : APP_NAME;

// Set showSidebar default if not already set
$showSidebar = isset($showSidebar) ? $showSidebar : false;

// Get unread notification count for header
$unreadNotifications = 0;
$recentNotifications = [];
if (isLoggedIn()) {
    $notificationController = new NotificationController();
    $unreadNotifications = $notificationController->countUnread($_SESSION['user']['id']);
    
    // Get 5 most recent notifications
    if ($unreadNotifications > 0) {
        $result = $notificationController->getUserNotifications($_SESSION['user']['id'], 1, 5, true);
        $recentNotifications = $result['notifications'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    
    <style>
        /* Discount styles */
        .discount-applied {
            color: #198754;
            font-weight: bold;
            position: relative;
        }
        
        .discount-info {
            cursor: help;
            position: relative;
        }
        
        .discount-info:hover::after {
            content: attr(title);
            position: absolute;
            background: #198754;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 100;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php if (isset($showSidebar) && $showSidebar): ?>
            <div class="col-md-3 col-lg-2 p-0">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 py-3">
                <div class="container">
                    <?php echo getFlashMessages(); ?>
            <?php else: ?>
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                    <div class="container">
                        <a class="navbar-brand" href="<?php echo APP_URL; ?>"><?php echo APP_NAME; ?></a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/index.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/dashboard/packages.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>dashboard/packages.php">Packages</a>
                                </li>
                            </ul>
                            <ul class="navbar-nav">
                                <?php if (isLoggedIn()): ?>
                                <!-- Notification Dropdown -->
                                <li class="nav-item dropdown me-2">
                                    <a class="nav-link dropdown-toggle notification-counter" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-bell"></i>
                                        <?php if ($unreadNotifications > 0): ?>
                                            <span class="notification-badge bg-danger"><?php echo $unreadNotifications; ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
                                        <li>
                                            <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                                                <h6 class="mb-0">Notifications</h6>
                                                <a href="<?php echo APP_URL; ?>dashboard/notifications.php" class="text-decoration-none">View All</a>
                                            </div>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <?php if (empty($recentNotifications)): ?>
                                            <li><div class="px-3 py-2 text-muted">No new notifications</div></li>
                                        <?php else: ?>
                                            <?php foreach ($recentNotifications as $notification): ?>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="<?php echo APP_URL; ?>dashboard/notifications.php">
                                                        <div class="flex-shrink-0 me-2">
                                                            <div class="notification-icon bg-<?php echo $notificationController->getTypeColor($notification['type']); ?>" style="width: 30px; height: 30px;">
                                                                <i class="<?php echo $notificationController->getTypeIcon($notification['type']); ?> fa-sm"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-0 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                            <small class="text-muted"><?php echo timeAgo($notification['created_at']); ?></small>
                                                        </div>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                                <!-- User Dropdown -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/index.php">Dashboard</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>auth/logout.php">Logout</a></li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/auth/login.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>auth/login.php">Login</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/auth/register.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>auth/register.php">Register</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
                <div class="container mt-4">
                    <?php echo getFlashMessages(); ?>
            <?php endif; ?>
