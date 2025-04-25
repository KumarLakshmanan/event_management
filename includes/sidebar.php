<?php
/**
 * Role-based sidebar navigation
 */

// Get current page for active highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar bg-dark text-white">
    <div class="sidebar-header p-3">
        <h3><?= APP_NAME ?></h3>
    </div>
    
    <ul class="nav flex-column">
        <?php if (isLoggedIn()): ?>
            <!-- All users see Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <!-- Package menu item - visible to all -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'packages.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/packages.php">
                    <i class="bi bi-box me-2"></i> Packages
                </a>
            </li>
            
            <?php if (hasRole(['administrator', 'manager'])): ?>
                <!-- Services - Admin and Manager only -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'services.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/services.php">
                        <i class="bi bi-gear me-2"></i> Services
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Bookings - visible to all -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'bookings.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/bookings.php">
                    <i class="bi bi-calendar-check me-2"></i> Bookings
                </a>
            </li>
            
            <?php if (hasRole('client')): ?>
                <!-- Guest management - Client only -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'guests.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/guests.php">
                        <i class="bi bi-people me-2"></i> Guests
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (hasRole('administrator')): ?>
                <!-- User management - Admin only -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/users.php">
                        <i class="bi bi-person-gear me-2"></i> Users
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- User profile - visible to all -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'profile.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard/profile.php">
                    <i class="bi bi-person-circle me-2"></i> My Profile
                </a>
            </li>
            
            <!-- Logout - visible to all -->
            <li class="nav-item mt-3">
                <a class="nav-link" href="<?= BASE_URL ?>auth/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        <?php else: ?>
            <!-- Links for non-logged in users -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
                    <i class="bi bi-house me-2"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'login.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>auth/login.php">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'register.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>auth/register.php">
                    <i class="bi bi-person-plus me-2"></i> Register
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>