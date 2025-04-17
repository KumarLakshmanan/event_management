<?php
// Get current page
$currentFile = basename($_SERVER['PHP_SELF']);

// Define sidebar items based on user role
$sidebarItems = [];

// Items for clients
$sidebarItems['client'] = [
    ['title' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'link' => 'dashboard.php'],
    ['title' => 'Packages', 'icon' => 'fa-box', 'link' => 'packages.php'],
    ['title' => 'My Bookings', 'icon' => 'fa-calendar-check', 'link' => 'my-bookings.php'],
    ['title' => 'My Guests', 'icon' => 'fa-users', 'link' => 'my-guests.php'],
];

// Items for managers and admins (without My Bookings and My Guests)
$sidebarItems['manager'] = [
    ['title' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'link' => 'dashboard.php'],
    ['title' => 'Packages', 'icon' => 'fa-box', 'link' => 'packages-admin.php'],
    ['title' => 'Services', 'icon' => 'fa-concierge-bell', 'link' => 'services.php'],
    ['title' => 'Bookings', 'icon' => 'fa-calendar', 'link' => 'bookings.php'],
];

// Additional items for admins only
$sidebarItems['admin'] = array_merge($sidebarItems['manager'], [
    ['title' => 'User Management', 'icon' => 'fa-user-cog', 'link' => 'user-management.php'],
]);

// Get user role from session
$userRole = $_SESSION['user_role'] ?? 'client';

// Determine which items to display based on role
$navItems = $sidebarItems[$userRole] ?? $sidebarItems['client'];
?>

<div class="sidebar">
    <!-- Sidebar - Brand -->
    <a class="brand-link" href="dashboard.php">
        <i class="fas fa-calendar-day mr-2"></i>
        <span>Event Manager</span>
    </a>
    
    <!-- Divider -->
    <hr class="sidebar-divider">
    
    <!-- Nav Items -->
    <ul class="nav flex-column">
        <?php foreach ($navItems as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentFile === $item['link']) ? 'active' : ''; ?>" href="<?php echo $item['link']; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['title']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        
        <!-- Divider -->
        <hr class="sidebar-divider">
        
        <!-- Logout -->
        <li class="nav-item">
            <a class="nav-link" href="../handlers/auth.php?action=logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
