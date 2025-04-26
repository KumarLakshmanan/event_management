<!-- Topbar/Header -->
<header class="topbar" style="position: fixed; width: 100%; background-color: #0d6efd; z-index: 999;">
    <nav class="navbar navbar-expand-md navbar-dark">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- <a class="navbar-brand d-flex align-items-center" href="<?= $adminBaseUrl ?>" target="_blank">
                <img src="<?= $adminBaseUrl ?>img/justdial.jpeg" alt="Logo" style="height: 30px; width: 150px; object-fit: contain;">
            </a> -->
            <div class="d-flex align-items-center">
                <div class="profile-pic dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none" href="#" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $adminBaseUrl ?>img/profile.png" alt="Profile" class="rounded-circle" width="36" height="36">
                        <span class="ms-2 fw-semibold">
                            <?= isset($_SESSION['fullname']) ? $_SESSION['fullname'] : "Guest"; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Sidebar -->
<aside class="left-sidebar" style="margin: 0; padding: 0; position: fixed; top: 60px; width: 250px; height: calc(100% - 60px); background-color: #ffffff; box-shadow: 2px 0 5px rgba(0,0,0,0.1); overflow-y: auto; z-index: 998;">
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="list-unstyled">
                <?php if($_SESSION['role'] != 'client') { ?>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>services">
                            <i class="bi bi-gear me-2"></i> <span>Services</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>packages">
                            <i class="bi bi-box-seam me-2"></i> <span>Bundles</span>
                        </a>
                    </li>

                    <?php if($_SESSION['role'] == 'admin') { ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>managers">
                                <i class="bi bi-person-badge me-2"></i> <span>Members</span>
                            </a>
                        </li>
                    <?php } ?>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>clients">
                            <i class="bi bi-people me-2"></i> <span>Users</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>clientbooking">
                            <i class="bi bi-calendar-check me-2"></i> <span>Orders List</span>
                        </a>
                    </li>

                <?php } else { ?>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>explore_bundles">
                            <i class="bi bi-box2-heart me-2"></i> <span> Explore Bundles</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link d-flex align-items-center p-3 text-dark" href="<?= $adminBaseUrl ?>booking">
                            <i class="bi bi-calendar-event me-2"></i> <span>My Orders</span>
                        </a>
                    </li>

                <?php } ?>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>logout" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 256 256">
                            <path fill="currentColor" d="m224.5 136.5l-42 42a12 12 0 0 1-8.5 3.5a12.2 12.2 0 0 1-8.5-3.5a12 12 0 0 1 0-17L187 140h-83a12 12 0 0 1 0-24h83l-21.5-21.5a12 12 0 0 1 17-17l42 42a12 12 0 0 1 0 17ZM104 204H52V52h52a12 12 0 0 0 0-24H48a20.1 20.1 0 0 0-20 20v160a20.1 20.1 0 0 0 20 20h56a12 12 0 0 0 0-24Z" />
                        </svg>
                        <span class="hide-menu px-2">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<!-- Add Bootstrap 5 CSS if not already -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Add Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
