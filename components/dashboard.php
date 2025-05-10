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
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/richtext.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/dashboard.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/dataTables.bootstrap4.min.css">
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
        <?php include "./components/sidebar.php"; ?>
        <div class="page-wrapper mt-5">
            <div class="container-fluid">
                <?php
                if ($_SESSION['role'] != 'client') {
                    if ($pageId == "services") {
                        include "dashboard/services.php";
                    } elseif ($pageId == "editservice") {
                        include "dashboard/services/edit.php";
                    } elseif ($pageId == "addservice") {
                        include "dashboard/services/add.php";
                    } elseif ($pageId == "packages") {
                        include "dashboard/packages.php";
                    } elseif ($pageId == "editpackage") {
                        include "dashboard/packages/edit.php";
                    } elseif ($pageId == "addpackage") {
                        include "dashboard/packages/add.php";
                    } elseif ($pageId == "managers") {
                        include "dashboard/managers.php";
                    } elseif ($pageId == "editmanager") {
                        include "dashboard/manager/edit.php";
                    } elseif ($pageId == "addmanager") {
                        include "dashboard/manager/add.php";
                    } elseif ($pageId == "clients") {
                        include "dashboard/clients.php";
                    } elseif ($pageId == "editclient") {
                        include "dashboard/client/edit.php";
                    } elseif ($pageId == "addclient") {
                        include "dashboard/client/add.php";
                    } elseif ($pageId == "clientbooking") {
                        include "dashboard/clientbooking.php";
                    } elseif ($pageId == "editallbooking") {
                        include "dashboard/editallbooking.php";
                    } elseif ($pageId == "contactus") {
                        include "dashboard/contactus.php";
                    } else {
                        include "dashboard/services.php";
                    }
                } else {
                    if ($pageId == "addcustompackage") {
                        include "dashboard/createcustompackage.php";
                    } elseif ($pageId == "booking") {
                        include "dashboard/booking.php";
                    } elseif ($pageId == "editbooking") {
                        include "dashboard/editbooking.php";
                    } elseif ($pageId == "rsvp_attend") {
                        include "rsvp_attend.php";
                    } elseif ($pageId == "rsvp_notattend") {
                        include "rsvp_notattend.php";
                    } elseif ($pageId == "email_verification") {
                        include "email_verification.php";
                    } else {
                        include "dashboard/pick_a_package.php";
                    }
                }

                ?>
            </div>
            <footer class="footer text-center"> Xpert Event</footer>
        </div>
    </div>
</body>

</html>