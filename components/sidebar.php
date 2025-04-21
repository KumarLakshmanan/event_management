
<header class="topbar" data-navbarbg="skin5" style="position:fixed;width: 100%">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header" data-logobg="skin6">
            <a class="navbar-brand" href="<?= $baseUrl ?>" target="_blank">
                <!-- <b class="logo-icon">
                    <img src="<?= $adminBaseUrl ?>img/icon-black.png" alt="homepage" style="height: 40px;">
                </b> -->
                <span class="logo-text">
                    <img src="<?= $adminBaseUrl ?>img/justdial.jpeg" alt="homepage" style="height: 30px;width: 150px;">
                </span>
            </a>
            <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
        </div>
        <ul class="navbar-nav ms-auto d-flex align-items-center px-2">
            <li>
                <a class="profile-pic" href="#">
                    <img src="<?= $adminBaseUrl ?>img/varun.png" alt="user-img" width="36" class="img-circle"><span class="text-white font-medium">
                        <?php
                        if (isset($_SESSION['fullname'])) {
                            echo $_SESSION['fullname'];
                        } else {
                            echo "Guest";
                        }
                        ?>
                    </span></a>
            </li>
        </ul>
    </nav>
</header>
<aside class="left-sidebar" style="position:fixed" data-sidebarbg="skin6">
    <div class="scroll-sidebar" style="overflow-y: auto">
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link"></a>
                    <a class="sidebar-link waves-effect waves-dark sidebar-link"></a>
                </li>
                <?php if($_SESSION['role']!='client'){ ?>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>services" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path fill="currentColor" d="m9.25 22l-.4-3.2q-.325-.125-.612-.3t-.563-.375L4.7 19.375l-2.75-4.75l2.575-1.95Q4.5 12.5 4.5 12.338v-.675q0-.163.025-.338L1.95 9.375l2.75-4.75l2.975 1.25q.275-.2.575-.375t.6-.3l.4-3.2h5.5l.4 3.2q.325.125.613.3t.562.375l2.975-1.25l2.75 4.75l-2.575 1.95q.025.175.025.338v.674q0 .163-.05.338l2.575 1.95l-2.75 4.75l-2.95-1.25q-.275.2-.575.375t-.6.3l-.4 3.2zm2.8-6.5q1.45 0 2.475-1.025T15.55 12q0-1.45-1.025-2.475T12.05 8.5q-1.475 0-2.488 1.025T8.55 12q0 1.45 1.013 2.475T12.05 15.5" />
                        </svg>
                        <span class="hide-menu px-2">Services</span>
                    </a>
                </li>   
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>packages" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path fill="currentColor" d="m9.25 22l-.4-3.2q-.325-.125-.612-.3t-.563-.375L4.7 19.375l-2.75-4.75l2.575-1.95Q4.5 12.5 4.5 12.338v-.675q0-.163.025-.338L1.95 9.375l2.75-4.75l2.975 1.25q.275-.2.575-.375t.6-.3l.4-3.2h5.5l.4 3.2q.325.125.613.3t.562.375l2.975-1.25l2.75 4.75l-2.575 1.95q.025.175.025.338v.674q0 .163-.05.338l2.575 1.95l-2.75 4.75l-2.95-1.25q-.275.2-.575.375t-.6.3l-.4 3.2zm2.8-6.5q1.45 0 2.475-1.025T15.55 12q0-1.45-1.025-2.475T12.05 8.5q-1.475 0-2.488 1.025T8.55 12q0 1.45 1.013 2.475T12.05 15.5" />
                        </svg>
                        <span class="hide-menu px-2">Packages</span>
                    </a>
                </li>      
                <?php if($_SESSION['role']=='admin'){ ?>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>managers" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 15 15">
                            <path fill="currentColor" fill-rule="evenodd" d="M11 2H4V0H3v2H1.5A1.5 1.5 0 0 0 0 3.5v8A1.5 1.5 0 0 0 1.5 13h12a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 13.5 2H12V0h-1zM3 6a2 2 0 1 1 4 0a2 2 0 0 1-4 0m-.618 4.618a2.927 2.927 0 0 1 5.236 0l.33.658A.5.5 0 0 1 7.5 12h-5a.5.5 0 0 1-.447-.724zM9 6h3V5H9zm0 3h3V8H9z" clip-rule="evenodd" />
                            <path fill="currentColor" d="M15 14v1H0v-1z" />
                        </svg>
                        <span class="hide-menu px-2">Manager</span>
                    </a>
                </li>       
                <?php } ?>  
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>clients" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 15 15">
                            <path fill="currentColor" fill-rule="evenodd" d="M11 2H4V0H3v2H1.5A1.5 1.5 0 0 0 0 3.5v8A1.5 1.5 0 0 0 1.5 13h12a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 13.5 2H12V0h-1zM3 6a2 2 0 1 1 4 0a2 2 0 0 1-4 0m-.618 4.618a2.927 2.927 0 0 1 5.236 0l.33.658A.5.5 0 0 1 7.5 12h-5a.5.5 0 0 1-.447-.724zM9 6h3V5H9zm0 3h3V8H9z" clip-rule="evenodd" />
                            <path fill="currentColor" d="M15 14v1H0v-1z" />
                        </svg>
                        <span class="hide-menu px-2">Clients</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>clientbooking" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 28 28">
                            <path fill="currentColor" d="M11 3a9 9 0 0 0-8.048 13.032l-.908 3.389a1.25 1.25 0 0 0 1.53 1.53l3.387-.906A9 9 0 1 0 11 3m0 14.5a1 1 0 1 1 0-2a1 1 0 0 1 0 2M9.5 9.256A.75.75 0 0 1 8 9.25v-.019a1.611 1.611 0 0 1 .007-.127a3.015 3.015 0 0 1 .37-1.222C8.789 7.152 9.598 6.5 11 6.5c1.403 0 2.212.652 2.622 1.382A3.015 3.015 0 0 1 14 9.2l.001.05v.001c0 1.124-.692 1.88-1.193 2.428l-.125.137c-.546.606-.932 1.11-.932 1.935a.75.75 0 0 1-1.5 0c0-1.425.739-2.296 1.318-2.939l.018-.02c.609-.677.912-1.013.914-1.535v-.003a1.532 1.532 0 0 0-.185-.635C12.163 8.348 11.847 8 11 8s-1.163.348-1.315.618a1.516 1.516 0 0 0-.185.638M17 25a8.978 8.978 0 0 1-6.732-3.026a10.077 10.077 0 0 0 2.109-.068A7.468 7.468 0 0 0 17 23.5a7.463 7.463 0 0 0 3.59-.914a.75.75 0 0 1 .555-.066l3.25.87l-.872-3.252a.75.75 0 0 1 .066-.553A7.467 7.467 0 0 0 24.5 16a7.498 7.498 0 0 0-3.825-6.54a9.926 9.926 0 0 0-.75-1.974a9.004 9.004 0 0 1 5.123 12.547l.908 3.388a1.25 1.25 0 0 1-1.531 1.53l-3.386-.906A8.965 8.965 0 0 1 17 25M12.5 9.256v.002v-.005" />
                        </svg>
                        <span class="hide-menu px-2">Booking List</span>
                    </a>
                </li> 
                <?php } else { ?>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>pick_a_package" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 15 15">
                            <path fill="currentColor" fill-rule="evenodd" d="M11 2H4V0H3v2H1.5A1.5 1.5 0 0 0 0 3.5v8A1.5 1.5 0 0 0 1.5 13h12a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 13.5 2H12V0h-1zM3 6a2 2 0 1 1 4 0a2 2 0 0 1-4 0m-.618 4.618a2.927 2.927 0 0 1 5.236 0l.33.658A.5.5 0 0 1 7.5 12h-5a.5.5 0 0 1-.447-.724zM9 6h3V5H9zm0 3h3V8H9z" clip-rule="evenodd" />
                            <path fill="currentColor" d="M15 14v1H0v-1z" />
                        </svg>
                        <span class="hide-menu px-2">Pick A Package</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?= $adminBaseUrl ?>booking" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 28 28">
                            <path fill="currentColor" d="M11 3a9 9 0 0 0-8.048 13.032l-.908 3.389a1.25 1.25 0 0 0 1.53 1.53l3.387-.906A9 9 0 1 0 11 3m0 14.5a1 1 0 1 1 0-2a1 1 0 0 1 0 2M9.5 9.256A.75.75 0 0 1 8 9.25v-.019a1.611 1.611 0 0 1 .007-.127a3.015 3.015 0 0 1 .37-1.222C8.789 7.152 9.598 6.5 11 6.5c1.403 0 2.212.652 2.622 1.382A3.015 3.015 0 0 1 14 9.2l.001.05v.001c0 1.124-.692 1.88-1.193 2.428l-.125.137c-.546.606-.932 1.11-.932 1.935a.75.75 0 0 1-1.5 0c0-1.425.739-2.296 1.318-2.939l.018-.02c.609-.677.912-1.013.914-1.535v-.003a1.532 1.532 0 0 0-.185-.635C12.163 8.348 11.847 8 11 8s-1.163.348-1.315.618a1.516 1.516 0 0 0-.185.638M17 25a8.978 8.978 0 0 1-6.732-3.026a10.077 10.077 0 0 0 2.109-.068A7.468 7.468 0 0 0 17 23.5a7.463 7.463 0 0 0 3.59-.914a.75.75 0 0 1 .555-.066l3.25.87l-.872-3.252a.75.75 0 0 1 .066-.553A7.467 7.467 0 0 0 24.5 16a7.498 7.498 0 0 0-3.825-6.54a9.926 9.926 0 0 0-.75-1.974a9.004 9.004 0 0 1 5.123 12.547l.908 3.388a1.25 1.25 0 0 1-1.531 1.53l-3.386-.906A8.965 8.965 0 0 1 17 25M12.5 9.256v.002v-.005" />
                        </svg>
                        <span class="hide-menu px-2">Bookings</span>
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