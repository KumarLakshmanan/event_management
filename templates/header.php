<?php
/**
 * Header template for Event Planning Platform
 */

// Require necessary files if not already included
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/includes/config.php';
}
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar - only shown if user is logged in -->
            <?php if (isLoggedIn()): ?>
                <div class="col-md-3 col-lg-2 d-md-block sidebar-container p-0">
                    <?php include INCLUDES_PATH . 'sidebar.php'; ?>
                </div>
                
                <!-- Main content with sidebar -->
                <div class="col-md-9 col-lg-10 main-content">
            <?php else: ?>
                <!-- Main content without sidebar (full width) -->
                <div class="col-12 main-content">
            <?php endif; ?>
            
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                        <a class="navbar-brand d-md-none" href="<?= BASE_URL ?>"><?= APP_NAME ?></a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <?php if (!isLoggedIn()): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= BASE_URL ?>index.php">Home</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <ul class="navbar-nav">
                                <?php if (isLoggedIn()): ?>
                                    <!-- Notifications -->
                                    <li class="nav-item">
                                        <a class="nav-link position-relative" href="<?= BASE_URL ?>dashboard/notifications.php">
                                            <i class="bi bi-bell"></i>
                                            <?php 
                                            $notificationsCount = getUnreadNotificationsCount();
                                            if ($notificationsCount > 0): 
                                            ?>
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                    <?php echo $notificationsCount > 99 ? '99+' : $notificationsCount; ?>
                                                    <span class="visually-hidden">unread notifications</span>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    
                                    <!-- User dropdown -->
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-person-circle me-1"></i><?= $_SESSION['user_name'] ?>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                            <li><a class="dropdown-item" href="<?= BASE_URL ?>dashboard/profile.php">My Profile</a></li>
                                            <li><a class="dropdown-item" href="<?= BASE_URL ?>dashboard/notifications.php">Notifications</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php">Logout</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= BASE_URL ?>auth/login.php">Login</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= BASE_URL ?>auth/register.php">Register</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <!-- Page content container -->
                <div class="container-fluid mt-4 mb-5">
                    <?php if (isset($_SESSION['alert_message']) && isset($_SESSION['alert_type'])): ?>
                        <?= displayAlert($_SESSION['alert_message'], $_SESSION['alert_type']); ?>
                        <?php 
                        // Clear the alert after displaying it
                        unset($_SESSION['alert_message']);
                        unset($_SESSION['alert_type']);
                        ?>
                    <?php endif; ?>