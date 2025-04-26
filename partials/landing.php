<?php
ini_set('error_log', './php-error.log');
$pageId = "index";
if (isset($_GET["pageid"])) {
    $pageId = $_GET["pageid"];
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Management Dashboard</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/modern-dashboard.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/richtext.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/dataTables.bootstrap4.min.css">
    <!-- Core JS -->
    <script src="<?= $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/sweetalert.js"></script>
    <script src="<?= $adminBaseUrl ?>js/jquery.dataTables.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/dataTables.bootstrap4.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/bootstrap.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/jquery.richtext.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/custom.js"></script>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <?php include "./partials/sidebar.php"; ?>
        <div class="page-wrapper">
            <!-- Page Header -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4 px-4 pt-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <?php 
                    $pageTitle = "Dashboard";
                    switch($pageId) {
                        case "services": $pageTitle = "Services"; break;
                        case "packages": $pageTitle = "Bundles"; break;
                        case "managers": $pageTitle = "Members"; break;
                        case "clients": $pageTitle = "Guests"; break;
                        case "addcustompackage": $pageTitle = "Create Custom Bundle"; break;
                        case "booking": $pageTitle = "Bookings"; break;
                        case "clientbooking": $pageTitle = "Client Bookings"; break;
                        case "editallbooking": $pageTitle = "Edit Booking"; break;
                    }
                    echo $pageTitle;
                    ?>
                </h1>
                <div class="d-none d-sm-inline-block">
                    <span class="mr-2 d-none d-lg-inline text-gray-600">
                        Welcome, <?= $_SESSION['fullname'] ?>
                    </span>
                </div>
            </div>
            <div class="container-fluid px-4">
                <?php
                // Define page map
                $adminPages = [
                    "services" => "modules_view/services.php",
                    "editservice" => "modules_view/services/edit.php",
                    "addservice" => "modules_view/services/add.php",
                    "packages" => "modules_view/packages.php",
                    "editpackage" => "modules_view/packages/edit.php",
                    "addpackage" => "modules_view/packages/add.php",
                    "managers" => "modules_view/managers.php",
                    "editmanager" => "modules_view/member/edit.php",
                    "addmanager" => "modules_view/member/add.php",
                    "clients" => "modules_view/clients.php",
                    "editclient" => "modules_view/users/edit.php",
                    "addclient" => "modules_view/users/add.php",
                    "clientbooking" => "modules_view/clientbooking.php",
                    "editallbooking" => "modules_view/editallbooking.php"
                ];

                $clientPages = [
                    "addcustompackage" => "modules_view/createcustompackage.php",
                    "booking" => "modules_view/booking.php",
                    "editbooking" => "modules_view/editbooking.php",
                    "rsvp_attend" => "mail/mail_read.php",
                    "rsvp_notattend" => "mail/mail_notread.php"
                ];

                // Load correct view based on role and pageId
                if ($_SESSION['role'] != 'client') {
                    include $adminPages[$pageId] ?? "modules_view/services.php";
                } else {
                    include $clientPages[$pageId] ?? "modules_view/explore_bundles.php";
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Toggle Sidebar Script -->
    <script>
    $(document).ready(function() {
        // Add active class to current sidebar item
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/');
        const currentPage = pathParts[pathParts.length - 1];
        
        $('.sidebar-link').each(function() {
            const href = $(this).attr('href');
            if (href && href.includes(currentPage)) {
                $(this).addClass('active');
            }
        });
        
        // Mobile sidebar toggle
        $('#sidebarToggle').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-toggled');
        });
    });
    </script>
</body>

</html>
