<!-- Sidebar -->
<aside class="left-sidebar" style="margin: 0; padding: 0; position: fixed; width: 250px; height: calc(100%); background-color: #ffffff; box-shadow: 2px 0 5px rgba(0,0,0,0.1); overflow-y: auto; z-index: 998;">
    <!-- Sidebar Toggle (mobile) -->
    <div class="d-md-none text-end p-3">
        <button id="sidebarToggle" class="btn btn-link text-white">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <!-- Brand Logo -->
    <div class="text-center py-4">
        <a href="<?= $adminBaseUrl ?>home" class="text-decoration-none">
            <h4 class="fw-bold mb-0"><?= $webName ?></h4>
        </a>
    </div>
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="list-unstyled">
                <?php if($_SESSION['role'] != 'client') { ?>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= $adminBaseUrl ?>services">
                            <i class="bi bi-gear"></i> <span>Services</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= $adminBaseUrl ?>packages">
                            <i class="bi bi-box-seam"></i> <span>Bundles</span>
                        </a>
                    </li>

                    <?php if($_SESSION['role'] == 'admin') { ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $adminBaseUrl ?>managers">
                                <i class="bi bi-people"></i> <span>Members</span>
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

                
                <!-- Common Menu Items -->
                <li class="sidebar-item mt-3">
                    <a class="sidebar-link" href="<?= $adminBaseUrl ?>logout">
                        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
