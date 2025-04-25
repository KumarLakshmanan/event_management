<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Get current user role
$userRole = isLoggedIn() ? $_SESSION['user']['role'] : null;

// Get unread notification count
$unreadNotifications = 0;
if (isLoggedIn()) {
    $notificationController = new NotificationController();
    $unreadNotifications = $notificationController->countUnread($_SESSION['user']['id']);
}

// Define sidebar items based on user role
$sidebarItems = [
    // Dashboard - available to all logged in users
    'dashboard' => [
        'icon' => 'fas fa-tachometer-alt',
        'title' => 'Dashboard',
        'link' => '/dashboard/index.php',
        'active' => $currentPage == 'index.php'
    ]
];

// Add role-specific menu items
if ($userRole) {
    // Packages - available to all roles
    $sidebarItems['packages'] = [
        'icon' => 'fas fa-box',
        'title' => 'Packages',
        'link' => '/dashboard/packages.php',
        'active' => $currentPage == 'packages.php'
    ];
    
    // Services - only for admin and manager
    if (in_array($userRole, [ROLE_ADMIN, ROLE_MANAGER])) {
        $sidebarItems['services'] = [
            'icon' => 'fas fa-concierge-bell',
            'title' => 'Services',
            'link' => '/dashboard/services.php',
            'active' => $currentPage == 'services.php'
        ];
    }
    
    // Bookings - available to all roles
    $sidebarItems['bookings'] = [
        'icon' => 'fas fa-calendar-check',
        'title' => 'Bookings',
        'link' => '/dashboard/bookings.php',
        'active' => $currentPage == 'bookings.php'
    ];
    
    // Notifications - available to all roles
    $sidebarItems['notifications'] = [
        'icon' => 'fas fa-bell',
        'title' => 'Notifications',
        'link' => '/dashboard/notifications.php',
        'active' => $currentPage == 'notifications.php'
    ];
    
    // User Management - only for admin
    if ($userRole == ROLE_ADMIN) {
        $sidebarItems['users'] = [
            'icon' => 'fas fa-user-cog',
            'title' => 'User Management',
            'link' => '/dashboard/users.php',
            'active' => $currentPage == 'users.php'
        ];
    }
}
?>

<div class="bg-dark sidebar pt-3">
    <div class="d-flex flex-column p-3 text-white">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4"><?php echo APP_NAME; ?></span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php foreach ($sidebarItems as $item): ?>
            <li class="nav-item">
                <a href="<?php echo APP_URL . $item['link']; ?>" class="nav-link <?php echo $item['active'] ? 'active' : 'text-white'; ?>">
                    <i class="<?php echo $item['icon']; ?> me-2"></i>
                    <?php echo $item['title']; ?>
                    <?php if ($item['title'] === 'Notifications' && $unreadNotifications > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-1"><?php echo $unreadNotifications; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <?php if (isLoggedIn()): ?>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2 fs-4"></i>
                <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>auth/logout.php">Sign out</a></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
