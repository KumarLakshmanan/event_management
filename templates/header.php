<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get page title from query parameter or use default
$pageTitle = isset($title) ? $title . ' - ' . APP_NAME : APP_NAME;
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
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
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
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/dashboard/packages.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/dashboard/packages.php">Packages</a>
                                </li>
                            </ul>
                            <ul class="navbar-nav">
                                <?php if (isLoggedIn()): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/dashboard/index.php">Dashboard</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout.php">Logout</a></li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/auth/login.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/auth/login.php">Login</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/auth/register.php' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/auth/register.php">Register</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
                <div class="container mt-4">
                    <?php echo getFlashMessages(); ?>
            <?php endif; ?>
